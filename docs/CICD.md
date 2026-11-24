# olympia-scholars — CI/CD

## 1. Current State (2026-11-24)

- **CI** (`.github/workflows/ci.yml`): lints PHP, runs static analysis (Larastan level 7, baselined), runs the full test suite, builds frontend assets, and proves the production caches build. Runs on push to **any branch** (path-scoped to the files that actually matter) plus PRs targeting `main`/`khai-QA-refactor`, mirroring ICIPC's widened-trigger lesson (docs/CICD.md §1 on that repo) rather than olympia-crm's original fixed-branch design.
- **CD** (`.github/workflows/cd.yml`): **two live environments.**
  - **Test**: `scholars.olympia-education.com`. Automatic on every CI-green push to `main`/`khai-QA-refactor`, plus manual `workflow_dispatch`.
  - **Production**: `olympianscholars.com`. **Manual only**: `workflow_dispatch` with `deploy_production` ticked. Live on CD since **2026-11-23 18:42 MYT** (§9). Before that it ran a hand-deployed copy of the retired pre-2026-11-18 `main` branch.
- **`olympianscholars.org`** permanently redirects every path to the same path on `.com` (§10). It is not a deploy target.
- **Branches**: `main` (default), `khai-QA-refactor` (source of truth) and `aidill-refactor`, normally kept at the same commit and pushed together as fast-forwards. The old diverged `main` was reset to `khai-QA-refactor` on 2026-11-23.
- **GitHub Secrets**: all 8 `CPANEL_*` secrets are set (§5). cPanel SSH access uses a dedicated ed25519 deploy keypair (`scholars-cd-deploy`), generated fresh for this pipeline and never a reused personal key. Its public half is appended to `~/.ssh/authorized_keys` and its private half was piped directly into `gh secret set` and deleted locally, the same custody discipline `olympia-crm`'s and `ICIPC`'s deploy keys went through. The connection was validated independently before any secret was trusted (`ssh -i scholars-cd-deploy ... whoami` → `serimala`).
- **Shared host confirmed**: test and production, plus `olympia-crm` and `ICIPC`, all live on `server.serimalaysia.my`, account `serimala`, SSH port `55000`. Confirmed directly from `~/.ssh/authorized_keys`, which already held `icipc-cd-deploy` and `github-actions-olympia-crm-deploy` keys before this one was added, not assumed from prior sessions' notes.
- **FK-orphan-audit tooling**: does not exist on Scholars (no `db:audit-fk-orphans`-equivalent Artisan command). `olympia-crm`'s deploy sequence runs one before every `migrate --force`; Scholars' does not yet. This now matters more: production's legacy schema has **49 FK constraints where a fresh migrate creates 121** (§9.3), so orphaned rows may already exist there.

## 2. CI — what it actually checks

| Step | What it proves |
|---|---|
| `composer validate --strict` | `composer.json`/`composer.lock` are internally consistent |
| PHP syntax lint (`php -l`) over `app bootstrap config database routes tests` | No PHP file fails to parse. Each file is linted in its own `php -l` call (4 in parallel via `xargs -n1 -P4`); only failures are printed, every broken file is listed, then the step fails. **Fixed 2026-11-24**: the step used to run `find ... -exec php -l {} +`, but `php -l` only lints the *first* file it is given, so just one file per batch was ever checked. That's how a stray newline before `<?php` in `StudentAttendanceSeeder.php` went unnoticed until 2026-11-23. |
| `php artisan route:list` | The route table itself loads without error |
| `./vendor/bin/phpstan analyse --memory-limit=1G` (Larastan level 7, baselined) | No *new* static-analysis errors beyond the grandfathered ones. The baseline started at 2,152 entries on 2026-11-18 and stood at 2,154 on 2026-11-24 after the production work (see the policy below) |
| `php artisan test` (full suite, `DB_CONNECTION=sqlite`, `:memory:`) | Every Feature/Unit test passes (420 on 2026-11-24) |
| `npm run build` | The Vite frontend build succeeds |
| Production cache integrity gate (`config:cache` / `route:cache` / `view:cache`, run **last**, in that order) | Every cached artifact the live deploy will build on actually builds. This catches a syntax error in an unrendered Blade partial, or a duplicate route name, before `cd.yml` ever runs `php artisan optimize` on the live host after `migrate --force` has already applied |

**Why level 7, not 5**: Scholars' CI previously ran Larastan at level 5 with no baseline. It was raised to level 7 (the team standard, matching `olympia-crm`) on 2026-11-18, with a baseline generated the same day (`phpstan-baseline.neon`). CI now gates only on *new* errors going forward, never the backlog.

**Baseline policy**: fix new errors in the code; never baseline your own code's type errors. The only acceptable additions are "relation X is not found" or undefined-relation-property errors that come from the **untyped legacy models** (`LiveClass`, `Teacher`, whose relations have no return types), where new code uses those relations the same way existing code already does. When regenerating, review the diff line by line. In normal work the baseline should only shrink. Typing those models is a dedicated cleanup, because it touches the whole codebase.

**Run Larastan locally before pushing.** It gates CI, and a push that fails it simply never deploys (this happened on 2026-11-23). A local `vendor/` installed before Larastan was added will lack it, so run `php composer.phar install` first.

**Step ordering, and why it's fixed**:
- `config:cache` must run **after** the test suite, never before. It bakes `.env`'s MySQL settings into `bootstrap/cache/config.php`, which would override the test step's `DB_CONNECTION=sqlite` override and break the suite.
- The route audit (if one is added later, matching `olympia-crm`'s `audit-routes.php`) must run **before** `route:cache`. Caching auto-generates synthetic `generated::*` names for unnamed routes, making a duplicate-name audit meaningless against the cached table.
- Final cleanup uses targeted clears (`config:clear`, `route:clear`, `view:clear`), never `optimize:clear`. Scholars' `CACHE_STORE` setting may route through a store that needs a live database, which `cache:clear` can't reach on a CI runner.

**CI's path filter does not include `cd.yml`.** A push that only changes `cd.yml` (or docs) runs no CI, and therefore triggers no automatic test deploy. To exercise a workflow-only change, dispatch CD manually.

## 3. CD — pipeline flow

```
push to main/khai-QA-refactor, CI green  -->  build  -->  deploy-test  (scholars.olympia-education.com)
workflow_dispatch (manual)               -->  build  -->  deploy-test, and deploy-prod if deploy_production is ticked  (olympianscholars.com)
```

`build` both builds **and ships** the code: a direct `rsync` from the runner's own workspace straight to the cPanel host, with no GitHub Actions artifact hand-off. `olympia-crm`'s own pipeline hit a permanent "Artifact storage quota" wall using the artifact-based approach (its docs/CICD.md §21) and never got it to recover, so this pipeline is artifact-free by design. A production dispatch therefore also re-ships and re-activates test, which is harmless.

`deploy-test` and `deploy-prod` are pure "activate what's already been rsynced" jobs:
1. back up the live DB (`mysqldump`)
2. `migrate --force`
3. `storage:link`
4. clear, then rebuild, the caches (`optimize`)
5. `queue:restart`
6. health-check `/up`, **retried up to 6 times, 10 s apart**, printing the HTTP code on every miss (§7.16)

**How to deploy production**: Actions → **CD** → **Run workflow** → branch `main`, tick **deploy_production** → Run. If only the activation job fails on an SSH timeout, use "Re-run failed jobs" once. For anything else, read the step log before re-running: the database part is idempotent, but repeating a failure teaches nothing.

## 4. Stateful paths (excluded from `rsync --delete`)

| Path | What lives there |
|---|---|
| `.env` | Live secrets; `build` never touches it |
| `/storage/app/private/` | `local` disk: chat attachments (`MessagingService::store`) |
| `/storage/app/public/` | `public` disk: assignments, submissions, certificates, materials, class recordings + thumbnails, payment proofs, teacher-application documents (resumes/certificates/transcripts), avatars, diagnostic images, banners |
| `/public/uploads/` | Written directly with `->move(public_path('uploads/...'))`: blog thumbnails and popup images (`AdminContentController`), quiz question images (`AssignmentQuizService`) |
| `/storage/logs/` | Application logs, kept across deploys |

**How this list is maintained**: the original list (2026-11-18) came from grepping only `Storage::disk(...)`/`->store()`/`->storeAs()` call sites, and it missed `public/uploads/`, which is written via `public_path()`. Every deploy would have deleted admin-uploaded images; this was fixed before production went live (2026-11-23). When adding any upload path, grep for **both** patterns (`Storage::` / `->store(` **and** `public_path(` / `->move(`), and add the path here and to **both** rsync `--exclude` lists in `cd.yml` in the same PR.

`public/storage` is deliberately *not* excluded. `storage:link` recreates it as a symlink on every activation (§7.18 covers hosts where it used to be a real directory).

## 5. GitHub Secrets

| Secret | Purpose | Status |
|---|---|---|
| `CPANEL_SSH_HOST` | rsync + SSH target host (both environments) | **Set**: `server.serimalaysia.my` |
| `CPANEL_SSH_PORT` | rsync + SSH port | **Set**: `55000` |
| `CPANEL_SSH_USER` | rsync + SSH user | **Set**: `serimala` |
| `CPANEL_SSH_PRIVATE_KEY` | Private half of the dedicated deploy keypair (ed25519, `scholars-cd-deploy`, generated 2026-11-18, never a reused personal key) | **Set** |
| `CPANEL_DEPLOY_PATH` | Project root for the test target | **Set**: `/home/serimala/scholars.olympia-education.com` |
| `CPANEL_HEALTH_CHECK_URL` | `/up` endpoint, test target | **Set**: `https://scholars.olympia-education.com/up` |
| `CPANEL_DEPLOY_PATH_PROD` | Project root for production (the docroot is its `/public`) | **Set 2026-11-23**: `/home/serimala/olympianscholars.com` |
| `CPANEL_HEALTH_CHECK_URL_PROD` | `/up` endpoint, production | **Set 2026-11-23**: `https://olympianscholars.com/up` |

The first six were set with `gh secret set <NAME> --repo MEDIA-Olympia-Education/Scholars`. The POSIX-path-shaped values were typed from Windows/Git-Bash with `MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL="*"` prefixed, pre-empting the path-mangling bug ICIPC's first live run hit. The two `_PROD` secrets were entered in the GitHub web UI (Settings → Secrets and variables → Actions), which avoids that problem entirely. GitHub never displays secret values, so a suspected typo is fixed by re-entering the value, not by inspecting it. Note the two deploy paths are the project root, **not** `/public`, and have no trailing slash.

**Host facts, confirmed 2026-11-18** (via a direct SSH connection using the new deploy key, before any secret was trusted): `/home/serimala/scholars.olympia-education.com` held leftover artifacts from a prior manual deploy attempt (`vendor/`, `node_modules/`, two SQL dumps, a zip, scratch files), not a fresh cPanel-provisioned empty docroot. Anything non-trivial was backed up to `~/backups/scholars_predeploy/`, then the folder was cleared to just `.` and `..` ahead of the first real CD run. No remnant database was treated as load-bearing: the test target started on a fresh DB.

## 6. Gap Analysis

| Item | Status | Notes |
|---|---|---|
| Test environment | **LIVE since 2026-11-18** | §8 |
| Production environment | **LIVE since 2026-11-23** | Adopted an existing hand-deployed site with real data, see §9 |
| `.org` → `.com` | **DONE 2026-11-24** | Full-path 301, §10 |
| Health-check false failures | **FIXED 2026-11-23** | Retry loop, §7.16 |
| Test deploy pruning prod backups | **FIXED 2026-11-23** | §7.17 |
| FK-orphan-audit pre-migrate gate | **MISSING** | `olympia-crm` has one; Scholars doesn't |
| Missing FK constraints on production | **FIX READY, not yet on production** | Migration `2026_11_24_000001_add_missing_foreign_keys_to_legacy_schema` (§9.3). Rehearse on a fresh production dump right before the production deploy |
| CI `php -l` lints one file per batch | **FIXED 2026-11-24** | Now one file per call, all failures reported, §2 |
| Billplz X-Signature key | **NOT YET CONFIGURED** (account pending) | Verification now fails closed, so online payments are rejected until the key is set. See §7.20 before taking any payment |
| Double-trigger when `main`/`khai-QA-refactor` are pushed together | **Observed, not fixed** | `cd.yml`'s `build` job matches CI completion on either branch, so pushing the same commit to both fires two independent `build`→`deploy-test` sequences back to back. Harmless (idempotent, serialized by the `cpanel-deploy-test-scholars` concurrency group) but wasteful. Branches are now routinely pushed together, so this happens on every release; filtering `cd.yml` to one branch would halve the work |
| Leftover old copies on the host | **Kept on purpose** | The `.org` folder (redirected, §10) still holds the pre-CD app; back it up before ever removing it |

## 7. Gotchas

Items 1–12 were real bugs on `olympia-crm`'s or `ICIPC`'s first live runs, built into Scholars' `cd.yml` from the start rather than rediscovered. Items 13 onwards were found while taking Scholars to production (2026-11-23/24).

1. **Unrecognized `with:` keys in GitHub Actions are silently ignored, not rejected.** `burnett01/rsync-deployments@7.0.1` has no `exclude:` input. Exclude patterns go in `switches:`, a real input, never in an `exclude:` key that looks plausible but does nothing.
2. **`set -e` does not catch failures inside a shell pipeline.** `mysqldump | gzip > file` needs `set -o pipefail` explicitly, or a failing `mysqldump` (e.g. missing `PROCESS` privilege on shared hosting) passes silently because only `gzip`'s exit code counts.
3. **`mysqldump` needs `--no-tablespaces`** on shared hosting. The global `PROCESS` privilege it would otherwise require isn't granted to database-scoped shared-hosting users.
4. **Never `source .env`** to extract DB credentials. A generated password containing shell metacharacters gets executed as code. Extract via `grep '^KEY=' .env | cut -d '=' -f2-` instead.
5. **The CRLF trap.** If `.env` is ever re-uploaded from a Windows editor, `grep | cut` extraction carries a trailing `\r` into the variable. It's invisible in printed output but breaks `mysqldump`/`mysql` connections with cryptic host-resolution errors. Scholars' `cd.yml` appends `tr -d '\r"'` to every credential extraction from the start, the one hardening ICIPC's own doc flagged as *still missing* on its pipeline.
6. **`storage/framework/{cache,sessions,testing,views}` have no real files to rsync**, only `.gitignore` placeholders, so `rsync --delete` can wipe the whole subtree if it was never present in a build output. That breaks `view:clear`/`view:cache` with "View path not found." A defensive `mkdir -p` runs at the top of every deploy SSH script, independent of what rsync did or didn't create.
7. **GitHub Actions artifact storage can get permanently stuck** at a quota wall with no way to debug it via the UI or API. This pipeline never uses artifacts for the deploy path (§3); rsync runs directly from the build job's own workspace.
8. **A non-22 SSH port** (`55000` here, confirmed §1) makes a wrong-port mistake look exactly like a firewall timeout. It was confirmed directly via a live connection test before being trusted in secrets.
9. **The `/up` health check proves PHP-FPM/bootstrap integrity, not DB reachability.** It returns 200 even with wrong DB credentials. Actual DB connectivity is proven by the `mysqldump` backup step that runs *before* `migrate --force`, not by `/up` afterward.
10. **A multi-line paste into an interactive SSH/terminal session can appear to truncate mid-paste on screen.** The authorized_keys heredoc during this setup rendered the closing `EOF` glued onto the key's own line in the terminal echo. Don't trust the visual rendering; verify with `cat -A` (shows end-of-line markers) before relying on it. In that case the file landed correctly and only the display was collapsed. Related: cPanel's web terminal sometimes **splits one long pasted line into two**, or wraps a paste in `^[[200~ … ~` bracketed-paste markers. Paste one short command at a time, and press Ctrl+C if the markers appear.
11. **`read -s` does not reliably suppress echo when the input arrives via a multi-line paste into a web-based terminal.** This was hit live during the test setup (§8): a DB password typed as part of a pasted multi-line block appeared in plaintext on screen (and in the chat transcript used to drive the terminal) despite the `-s` flag. **Never pipe a real secret through an interactive `read` in a pasted script again.** Edit `.env` directly with `nano` instead, typing secrets straight into the file, never through a command whose echo behavior depends on the terminal client.
12. **A fresh cPanel docroot has no `.env` and no database: "clear the folder" is not "ready to deploy."** The test target's first activation failed on `grep: .env: No such file or directory`. Nothing in `build`'s rsync ever creates `.env`: it's gitignored, and deliberately excluded from `--delete` so a *future* redeploy never touches a live one, but that same exclusion means a *first* deploy needs one created by hand. Use the checklist in §11.
13. **`public/uploads/` was missing from the excludes** because the original stateful-path survey only grepped `Storage::` call sites (§4). Production already held real admin uploads there; adding the exclude before its first CD deploy prevented their deletion.
14. **`.env.example` ships `MAIL_MAILER=log`, and the old new-host checklist never mentioned mail.** The test host ran with it, so every "sent" email, including teacher **welcome emails containing temporary passwords**, was written into `storage/logs/laravel.log` and reported as a success, so the admin saw no warning. Always set real `MAIL_*` values (§11), send a test mail, and if `log` was ever active, empty the log: `: > storage/logs/laravel.log`.
15. **After any `.env` edit on a deployed host, rebuild the config cache.** Every activation runs `optimize`, so the host serves a *cached* config and `.env` changes are ignored until `php artisan config:clear && php artisan config:cache`.
16. **The health check can hit a few seconds of HTTP 500 right after the swap.** Production's first CD run went red on "Health check FAILED" although every step had succeeded and `/up` answered 200 moments later: right after the rsync plus cache rebuild, LiteSpeed's PHP workers briefly error (seen on the test host too). Both jobs now retry up to 6 times, 10 s apart, and print the HTTP code on each miss.
17. **The test job's backup pruning deleted production backups.** `~/backups/scholars_db/` is shared by both environments, and `deploy-test` pruned `*.sql.gz` down to the newest 5. That silently removed a hand-made production dump on go-live day; an offline copy survived. Test now prunes only its own `pre_deploy_[0-9]*` files (production's pattern, `pre_deploy_prod_*`, was already scoped). **Keep hand-made backups outside `scholars_db/`**, e.g. directly in `~/backups/`.
18. **A legacy host may have `public/storage` as a real directory, not a symlink.** The old code mirrored uploads into it (`ensurePublicStorageMirror`), and both `.com` and `.org` had real directories. `rsync --delete` removes it (it isn't in the build) and `storage:link` then creates the symlink, so any file that exists *only* there would be lost. Before the first CD deploy to such a host, check with `ls -ld public/storage`. If it's a directory, copy anything missing into `storage/app/public/` (`rsync -a --ignore-existing public/storage/ storage/app/public/`; the dry run is `--dry-run --stats`).
19. **A database built outside the migration history makes `migrate --force` fail mid-deploy.** Production's schema came partly from a dump and partly from the old `main`, so 30 migrations were "Pending" although their tables and columns already existed, and some (`create_fee_structure_*`, `create_attendance_events`, …) would crash with "already exists" after the new code was already live. See §9 for the baseline + reconcile approach. **Never run `2026_09_11_000001_split_subjects_per_individual_year` on a populated database**: it deactivates kindergarten subjects and overwrites admin edits to subject fees and descriptions.
20. **Billplz payments need the X-Signature key before the first payment.** Since 2026-11-24 verification follows Billplz's documented algorithm and **fails closed**: with no key configured, every callback is rejected (HTTP 400) and logged as critical, and redirects change nothing. Rejected callbacks are retried by Billplz up to 5 times, and each failure lowers the account's standing. Set the API key, Collection ID and X-Signature key in Admin → Finance → Billplz (Billplz dashboard: Settings → Keys & Integration), enable X-Signature in Billplz, and test in sandbox before the first live payment.
21. **Never seed a populated production database.** `DatabaseSeeder` and `CourseCatalogSeeder` create demo accounts with the password `password`. Only `SettingsSeeder`, `EmailTemplateSeeder`, `SubjectTopicsSeeder` and `AcademicCalendarSeeder` are safe (they use `firstOrCreate`/`updateOrCreate` and create no users).

## 8. First Live Run (test) — 2026-11-18

There were two failures before the first real success, neither in the pipeline logic itself:

1. **Transient SSH timeout** on the very first `deploy-test` attempt (`dial tcp ...: i/o timeout`). `build`'s rsync had connected to the same host/port moments earlier from a different runner IP (each job in a workflow gets its own fresh GitHub Actions runner) and succeeded, so this wasn't a credentials or firewall-allowlist problem, just a one-off blip. A `gh run rerun --failed` resolved it immediately; worth trying once before investigating further if this recurs.
2. **No `.env`, no database** on the second attempt (`grep: .env: No such file or directory`), see gotcha §7.12. Resolved by creating the database (`serimala_scholarstest`) and a matching user via the cPanel UI, then hand-editing `.env` on the host.

A real security incident happened during the fix for #2, recorded plainly rather than glossed over: a DB password was exposed in plaintext in the session transcript, because an interactive `read -s` prompt fed via a pasted multi-line script into cPanel's web terminal didn't suppress echo (gotcha §7.11). The exposed password was rotated immediately and never used. A second, unrelated mix-up during the same fix: the Wanderer initially created a DB user named `serimala_scholarstest_app`, then separately created `serimala_scholarstest` (matching the database name exactly, no suffix), and that's the one wired into `.env`. The `_app`-suffixed user may still exist unused in cPanel's MySQL Users list and can be deleted once it's confirmed to have nothing attached to it.

**Final successful run** (`gh run 44233316747`, manually dispatched after `.env`/DB were fixed): `build` (42 s: composer/npm install, Vite build, rsync), then `deploy-test` (34 s: backup, `migrate --force`, `storage:link`, cache rebuild, `queue:restart`, health check), both green. Verified directly: `https://scholars.olympia-education.com/up` → 200, `https://scholars.olympia-education.com/` → 200.

## 9. Production Go-Live — 2026-11-23

Production was **adopted, not provisioned**. `olympianscholars.com` already ran a hand-deployed copy of the old `main` branch with real users, sharing its database (`serimala_scholars`) with an older copy at `olympianscholars.org`. Its `.env` (including `APP_KEY`, so sessions and encrypted data keep working), database and uploads were kept.

### 9.1 What made it safe
1. **The schema was rehearsed on a copy first.** A full production dump was loaded into a local scratch database, and khai's migrations were run against it. That reproduced the "already exists" failures (§7.19) before they could happen live.
2. **The schema was diffed** against a reference database built by a fresh `migrate` of the same commit: tables, columns, enum values, and indexes.
3. **Two fixes came out of the diff:**
   - a one-time **baseline** SQL that inserted the 30 already-applied migrations into `migrations` (batch 38), including `split_subjects_per_individual_year`, which must not run;
   - an idempotent **reconcile** migration, `2026_11_23_000001_reconcile_legacy_production_schema`, which adds the 3 missing enum value sets and 22 missing indexes (5 of them unique; checked for duplicates first). It is a no-op on test, CI and fresh databases.
4. **The full sequence was rehearsed** (baseline, migrate, a second migrate that must say "Nothing to migrate", a schema re-diff, then a smoke test of the real pages against the migrated copy) before anything touched production.

### 9.2 Go-live order (as run)
1. `.env` on the host: `APP_DEBUG=false`, `LOG_LEVEL=error`. Mail already used real SMTP via the site's existing mailbox, confirmed with a test send.
2. Uploads consolidated: `.org`'s 2 unique files were copied into `.com`'s `storage/app/public/` (§7.18).
3. The baseline SQL was run in phpMyAdmin on `serimala_scholars`: 30 rows inserted.
4. Backups: `~/backups/olympianscholars_com_2026-11-23_1832.tar.gz` (whole folder, 482 MB) and `~/backups/scholars_db/prod_com_before_golive_2026-11-23_1836.sql.gz`.
5. The two `_PROD` secrets were added (§5).
6. CD was dispatched with `deploy_production`. Activation: DB backup (`pre_deploy_prod_20261123_184310.sql.gz`), 10 migrations (8 new + reconcile + IC number), `storage:link` (`public/storage` became a symlink), caches, `queue:restart`. The **health check failed falsely** (§7.16) while the site was healthy; a second run after the retry fix was fully green.
7. Verified from outside: all pages 200, new routes present, `/.env` 403, logs/dumps 404, no debug traces on error.

### 9.3 Known differences that remain (deliberately out of scope)
- **Foreign keys**: production has 49 FK constraints, a fresh migrate has 121, so deleting a parent row does not cascade there and orphans accumulate. All 72 missing keys have compatible column types. The fix is migration `2026_11_24_000001_add_missing_foreign_keys_to_legacy_schema`:
  - Orphans are resolved first, following each key's intent. A nullable reference is cleared and the row kept; otherwise the orphan row is deleted, since its parent is already gone. This repeats until a pass changes nothing.
  - **Every** deleted or changed row is first copied in full (JSON) into the permanent table `legacy_fk_cleanup_backup` (`source_table`, `action`, `reason`, `row_data`). That includes rows that the keys already present cascade-delete or null alongside it.
  - Signed off 2026-11-24, including removing teacher #24 (its user account was already deleted) together with its 3 courses and their 2 enrolments.
  - Rehearsed on the 2026-11-23 production copy: 121/121 keys matching the reference, 359 rows backed up (mostly notifications, subtopic progress and teacher-subject links of deleted users, students and teachers), per-table before/after counts equal to the backup, and a second run that does nothing. On a database that already has all keys (test, CI, fresh) it is a no-op and does not even create the backup table.
  - To restore a row: `SELECT row_data FROM legacy_fk_cleanup_backup WHERE source_table = '...'`.
- **4 harmless column differences**: production has `longtext` where `text` is expected on 3 columns, and `assignments.section_b_questions` defaults to NULL instead of 0.
- **`teacher_applications.ic_number`** already existed from the old `main` with real values. `2026_11_23_000002` adds it only where it is missing, so it was a no-op on production.

### 9.4 Rollback
- **Code**: dispatch CD with `branch` set to the previous good commit SHA (it feeds `actions/checkout`'s `ref`) and `deploy_production` ticked.
- **Database**: restore the newest `~/backups/scholars_db/pre_deploy_prod_*.sql.gz` with `gunzip -c <file> | mysql ...`, using credentials extracted as in §7.4.

## 10. `olympianscholars.org`

`.org` still holds its own pre-CD copy of the app, which used the same database as `.com`. It is **not deployed to**. Every request redirects with a 301 to the same path on `.com`, in one hop, so the old code can no longer be reached or write to the shared, now-migrated database:

- cPanel → Domains → Redirects: permanent 301 from `olympianscholars.org` (with or without `www`) to `https://olympianscholars.com/`. **This alone covered only the root path**, because the folder's Laravel `.htaccess` rules served inner paths first.
- So a catch-all was prepended to `/home/serimala/olympianscholars.org/public/.htaccess` (the original is kept as `.htaccess.bak-2026-11-23`):
  ```
  RewriteEngine On
  RewriteCond %{HTTP_HOST} ^(www\.)?olympianscholars\.org$ [NC]
  RewriteRule ^(.*)$ https://olympianscholars.com/$1 [R=301,L]
  ```
- Verified 2026-11-24: http/https/www, deep paths and query strings all land on the matching `.com` URL. Keep AutoSSL active for `.org`, so `https://olympianscholars.org` can still redirect without a certificate warning.

A full backup of the `.org` folder is at `~/backups/olympianscholars_org_2026-11-23.tar.gz`.

## 11. New-environment checklist (before the first CD run)

1. **Database**: create the DB and a user with the same name (cPanel → Manage My Databases), grant ALL PRIVILEGES. Use a different password from every other environment.
2. **Domain**: point the docroot at `<project root>/public`, set PHP 8.2 (MultiPHP Manager), and run AutoSSL.
3. **`.env`**: `cp .env.example .env` in the project root, then edit with `nano` (§7.11):
   - `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`, `APP_URL=https://<domain>`
   - `DB_*`
   - **real `MAIL_*`** (§7.14): `MAIL_MAILER=smtp`, `MAIL_SCHEME=smtps`, `MAIL_HOST=mail.<domain>`, `MAIL_PORT=465`, a real mailbox as `MAIL_USERNAME`/`MAIL_FROM_ADDRESS`, and the password single-quoted
   - Billplz keys if payments are enabled (§7.20)
   - `chmod 600 .env`
4. **App key**: `php artisan key:generate --force` (only once the code is on the host; or generate locally with `php artisan key:generate --show` and paste it in).
5. **Connectivity**: `php artisan migrate:status` ("Migration table not found" is success: the connection works).
6. **Mail**: after the first deploy, send a test mail. Then remember §7.15 for any later `.env` change.
7. **Cron** (cPanel → Cron Jobs), using the path from `which php`:
   ```
   * * * * * cd <project root> && php artisan schedule:run >> /dev/null 2>&1
   * * * * * cd <project root> && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
   ```
8. **Secrets**: add the deploy path (project root, no `/public`, no trailing slash) and the `/up` URL (§5).
9. **Adopting an existing site instead?** Follow §9 (rehearse on a dump, diff the schema, baseline + reconcile) and check §7.18 before deploying. Never seed it (§7.21).
