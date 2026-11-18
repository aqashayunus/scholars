# olympia-scholars — CI/CD

## 1. Current State (2026-11-18)

- **CI** (`.github/workflows/ci.yml`): lints every PHP file, runs static analysis (Larastan level 7, baselined), runs the full test suite, builds frontend assets. Runs on push to **any branch** (path-scoped to the files that actually matter) plus PRs targeting `main`/`khai-QA-refactor`, mirroring ICIPC's widened-trigger lesson (docs/CICD.md §1 on that repo) rather than olympia-crm's original fixed-branch design.
- **CD** (`.github/workflows/cd.yml`): deploys to `scholars.olympia-education.com` — the **test** environment. Automatic on every CI-green push to `main`/`khai-QA-refactor`, plus manual `workflow_dispatch`. This is the first of two intended environments (see §6 — production is not yet provisioned).
- **GitHub Secrets**: all 6 `CPANEL_*` secrets are set as of 2026-11-18 — cPanel SSH access for `scholars.olympia-education.com` was granted the same day, via a dedicated ed25519 deploy keypair (`scholars-cd-deploy`) generated fresh for this pipeline (never a reused personal key), public half appended to `~/.ssh/authorized_keys`, private half piped directly into `gh secret set` and deleted locally — same custody discipline `olympia-crm`'s and `ICIPC`'s deploy keys went through. Connection validated independently before any secret was trusted (`ssh -i scholars-cd-deploy ... whoami` → `serimala`).
- **Shared host confirmed**: same physical host as `olympia-crm` and `ICIPC` — `server.serimalaysia.my`, account `serimala`, SSH port `55000`. Confirmed directly from `~/.ssh/authorized_keys`, which already held `icipc-cd-deploy` and `github-actions-olympia-crm-deploy` keys before this one was added — not assumed from prior sessions' notes.
- **FK-orphan-audit tooling**: does not exist on Scholars (no `db:audit-fk-orphans`-equivalent Artisan command). `olympia-crm`'s deploy sequence runs one before every `migrate --force`; Scholars' does not yet. Not a silent gap — just not yet built. Revisit if/when a migration lands that could orphan foreign keys on live data.

## 2. CI — what it actually checks

| Step | What it proves |
|---|---|
| `composer validate --strict` | `composer.json`/`composer.lock` are internally consistent |
| PHP syntax lint (`php -l`) over `app bootstrap config database routes tests` | No PHP file fails to parse |
| `php artisan route:list` | The route table itself loads without error |
| `./vendor/bin/phpstan analyse --memory-limit=1G` (Larastan level 7, baselined) | No *new* static-analysis errors beyond the 2,152 grandfathered ones baselined 2026-11-18 when the level was raised from 5 |
| `php artisan test` (full suite, `DB_CONNECTION=sqlite`, `:memory:`) | Every Feature/Unit test passes |
| `npm run build` (separate job) | The Vite frontend build succeeds |
| Production cache integrity gate (`config:cache` / `route:cache` / `view:cache`, run **last**, in that order) | Every cached artifact the live deploy will build on actually builds — catches a syntax error in an unrendered Blade partial, or a duplicate route name, before `cd.yml` ever runs `php artisan optimize` on the live host after `migrate --force` has already applied |

**Why level 7, not 5**: Scholars' CI previously ran Larastan at level 5 with no baseline. Raised to level 7 (the team standard, matching `olympia-crm`) on 2026-11-18, with a baseline generated the same day (`phpstan-baseline.neon`, 2,152 pre-existing errors grandfathered) — CI now gates only on *new* errors going forward, never the backlog.

**Step ordering, and why it's fixed**:
- `config:cache` must run **after** the test suite, never before — it bakes `.env`'s MySQL settings into `bootstrap/cache/config.php`, which would override the test step's `DB_CONNECTION=sqlite` override and break the suite.
- The route audit (if one is added later, matching `olympia-crm`'s `audit-routes.php`) must run **before** `route:cache` — caching auto-generates synthetic `generated::*` names for unnamed routes, making a duplicate-name audit meaningless against the cached table.
- Final cleanup uses targeted clears (`config:clear`, `route:clear`, `view:clear`), never `optimize:clear` — Scholars' `CACHE_STORE` setting may route through a store that needs a live database `cache:clear` can't reach on a CI runner.

## 3. CD — pipeline flow

```
push to main/khai-QA-refactor, CI green  -->  build  -->  deploy-test  (scholars.olympia-education.com)
workflow_dispatch (manual)               -->  build  -->  deploy-test, and deploy-prod if requested
```

`build` both builds **and ships** the code — a direct `rsync` from the runner's own workspace straight to the cPanel host, no GitHub Actions artifact hand-off. `olympia-crm`'s own pipeline hit a permanent "Artifact storage quota" wall using the artifact-based approach (its docs/CICD.md §21) and never got it to recover; this pipeline is built artifact-free from day one rather than risk the same dead end.

`deploy-test`/`deploy-prod` are pure "activate what's already been rsynced" jobs: backup the live DB, run `migrate --force`, rebuild caches, restart the queue, health-check.

## 4. The 2 stateful paths (excluded from `rsync --delete`)

Confirmed via a direct grep across `app/` for every `Storage::disk(...)`/`->store()`/`->storeAs()` call site — not assumed from either sibling project's own (different) list. Scholars uses only Laravel's two stock disks, no custom root paths:

| Path | Disk | What lives there |
|---|---|---|
| `.env` | — | Live secrets; `build` never touches it |
| `/storage/app/private/` | `local` | Chat attachments (`MessagingService::store`) |
| `/storage/app/public/` | `public` | Everything else: assignments, submissions, certificates, materials, class recordings + thumbnails, payment proofs, teacher-application documents (resumes/certificates/transcripts), avatars, diagnostic images, banners |

Only 3 excludes total (`.env` plus the two disk roots) — simpler than `olympia-crm`'s 5-path list because Scholars has no root-level upload directories outside the standard Laravel storage tree. If a future feature adds a new upload path outside these two disks, grep for the new `Storage::disk(...)` call site and add it here and to `cd.yml`'s `--exclude` list in the same PR.

## 5. GitHub Secrets

| Secret | Purpose | Status |
|---|---|---|
| `CPANEL_SSH_HOST` | rsync + SSH target host | **Set** — `server.serimalaysia.my` (same host as `olympia-crm`/`ICIPC`) |
| `CPANEL_SSH_PORT` | rsync + SSH port | **Set** — `55000` |
| `CPANEL_SSH_USER` | rsync + SSH user | **Set** — `serimala` |
| `CPANEL_SSH_PRIVATE_KEY` | Private half of a dedicated deploy keypair (ed25519, `scholars-cd-deploy`, generated 2026-11-18 — never a reused personal key) | **Set** |
| `CPANEL_DEPLOY_PATH` | Document root for the test target | **Set** — `/home/serimala/scholars.olympia-education.com` |
| `CPANEL_HEALTH_CHECK_URL` | `/up` endpoint, test target | **Set** — `https://scholars.olympia-education.com/up` |
| `CPANEL_DEPLOY_PATH_PROD` | Document root for production | **NOT SET** — production subdomain not yet provisioned, see §6 |
| `CPANEL_HEALTH_CHECK_URL_PROD` | `/up` endpoint, production | **NOT SET** — see §6 |

Set via `gh secret set <NAME> --repo MEDIA-Olympia-Education/Scholars`. The deploy path and health-check URL are POSIX-path-shaped values typed from a Windows/Git-Bash shell — both were set with `MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL="*"` prefixed onto the `gh secret set` call, pre-empting the exact bug ICIPC's own first live run hit (§7 below).

**Host facts, confirmed 2026-11-18** (via a direct SSH connection using the new deploy key, before any secret was trusted): `/home/serimala/scholars.olympia-education.com` held leftover artifacts from a prior manual deploy attempt (`vendor/`, `node_modules/`, two SQL dumps, a zip, scratch files) — not a fresh cPanel-provisioned empty docroot. Backed up to `~/backups/scholars_predeploy/` where anything looked non-trivial, then cleared to just `.` and `..` ahead of the first real CD run. No remnant database was treated as load-bearing — the Wanderer's call was fresh-DB-later, not reuse.

## 6. Gap Analysis

| Item | Status | Notes |
|---|---|---|
| cPanel SSH access for `scholars.olympia-education.com` | **GRANTED 2026-11-18** | Dedicated deploy keypair authorized on the host |
| The 6 test-target `CPANEL_*` secrets | **SET 2026-11-18** | Confirmed via `gh secret list` |
| First live deploy run | **NOT YET RUN** | Budget for at least one new bug — every sibling pipeline's first real run has found one |
| Production environment (`CPANEL_DEPLOY_PATH_PROD`, `CPANEL_HEALTH_CHECK_URL_PROD`) | **NOT PROVISIONED** | Two-environment pattern (test auto-deploy + prod manual-gated) was the explicit design choice from day one, matching `olympia-crm`'s `crmtest`/`ocrm` split — `cd.yml`'s `deploy-prod` job is written and wired to these secret names now, but will simply never fire (`workflow_dispatch` with `deploy_production: true` required) until a production subdomain exists and the two secrets are set. This section is the first thing to update once that happens. |
| FK-orphan-audit pre-migrate gate | **MISSING** | `olympia-crm` has one; Scholars doesn't. No specific plan yet — revisit if a migration risks orphaning FKs on live data. |
| Double-trigger when `main`/`khai-QA-refactor` are pushed together | **Observed, not fixed** | `cd.yml`'s `build` job matches CI completion on either branch — pushing the same commit to both (as happened during the 2026-11-18 `main` reset) fires two independent `build`→`deploy-test` sequences back to back. Harmless (idempotent, serialized by the `cpanel-deploy-test-scholars` concurrency group) but wasteful. Not worth fixing until it's established whether `main` will keep being pushed in lockstep with `khai-QA-refactor` going forward or just periodically fast-forwarded. |

## 7. Gotchas pre-empted from sibling pipelines, plus 2 new ones found here

Every one of these was a real bug on `olympia-crm` or `ICIPC`'s first live run. Built into Scholars' `cd.yml` from the start rather than rediscovered:

1. **Unrecognized `with:` keys in GitHub Actions are silently ignored, not rejected.** `burnett01/rsync-deployments@7.0.1` has no `exclude:` input — exclude patterns go in `switches:`, a real input, never a `exclude:` key that looks plausible but does nothing.
2. **`set -e` does not catch failures inside a shell pipeline.** `mysqldump | gzip > file` needs `set -o pipefail` explicitly, or a failing `mysqldump` (e.g. missing `PROCESS` privilege on shared hosting) passes silently because only `gzip`'s exit code counts.
3. **`mysqldump` needs `--no-tablespaces`** on shared hosting — the global `PROCESS` privilege it would otherwise require isn't granted to database-scoped shared-hosting users.
4. **Never `source .env`** to extract DB credentials — a generated password containing shell metacharacters gets executed as code. Extract via `grep '^KEY=' .env | cut -d '=' -f2-` instead.
5. **The CRLF trap.** If `.env` is ever re-uploaded from a Windows editor, `grep | cut` extraction carries a trailing `\r` into the variable — invisible in printed output but breaks `mysqldump`/`mysql` connections with cryptic host-resolution errors. Scholars' `cd.yml` appends `tr -d '\r"'` to every credential extraction from the start — the one hardening ICIPC's own doc flagged as *still missing* on its pipeline.
6. **`storage/framework/{cache,sessions,testing,views}` have no real files to rsync**, only `.gitignore` placeholders — `rsync --delete` can wipe the whole subtree if it was never present in a build output, breaking `view:clear`/`view:cache` with "View path not found." A defensive `mkdir -p` runs at the top of every deploy SSH script, independent of what rsync did or didn't create.
7. **GitHub Actions artifact storage can get permanently stuck** at a quota wall with no way to debug it via the UI or API. This pipeline never uses artifacts for the deploy path (§3) — rsync runs directly from the build job's own workspace.
8. **A non-22 SSH port** (`55000` here, confirmed §1) manifests a wrong-port mistake as an indistinguishable-from-firewall timeout. Confirmed directly via a live connection test before trusting it in secrets.
9. **The `/up` health check proves PHP-FPM/bootstrap integrity, not DB reachability** — it returns 200 even with wrong DB credentials. Actual DB connectivity is proven by the `mysqldump` backup step that runs *before* `migrate --force`, not by `/up` afterward.
10. **A multi-line paste into an interactive SSH/terminal session can appear to truncate mid-paste on screen** (the authorized_keys heredoc during this setup rendered the closing `EOF` glued onto the key's own line in the terminal echo) — don't trust the visual rendering; verify with `cat -A` (shows end-of-line markers) before relying on it. In this case the file landed correctly; the display was just collapsed.
11. **`read -s` does not reliably suppress echo when the input arrives via a multi-line paste into a web-based terminal.** Hit live during this setup (§8) — a DB password typed as part of a pasted multi-line block appeared in plaintext on screen (and in the chat transcript used to drive the terminal) despite the `-s` flag. **Never pipe a real secret through an interactive `read` in a pasted script again.** Edit `.env` directly with `nano` instead — type secrets straight into the file, never through a command whose echo behavior depends on the terminal client.
12. **A fresh cPanel docroot has no `.env` and no database — "clear the folder" is not "ready to deploy."** The first activation run failed on `grep: .env: No such file or directory`, because nothing in `build`'s rsync ever creates one (it's gitignored, and deliberately excluded from `--delete` so a *future* redeploy never touches a live one — but that same exclusion means a *first* deploy must have one created manually before `cd.yml` can succeed). The database itself was deferred by deliberate choice ("db can be done later") until this point. **Checklist for any new environment, before the first CD run**: create the DB + user in cPanel, `cp .env.example .env` on the host, fill in `APP_URL`/`APP_ENV`/`APP_DEBUG`/`DB_*` by hand, `php artisan key:generate --force`, then sanity-check connectivity with `php artisan migrate:status` ("Migration table not found" is success — it means the connection itself worked) before ever dispatching `cd.yml`.

## 8. First Live Run — 2026-11-18

Two failures before the first real success, neither in the pipeline logic itself:

1. **Transient SSH timeout** on the very first `deploy-test` attempt (`dial tcp ...: i/o timeout`) — `build`'s rsync had connected to the same host/port moments earlier from a different runner IP (each job in a workflow gets its own fresh GitHub Actions runner) and succeeded, so this wasn't a credentials or firewall-allowlist problem, just a one-off blip. A `gh run rerun --failed` resolved it immediately — worth trying once before investigating further if this recurs.
2. **No `.env`, no database** on the second attempt (`grep: .env: No such file or directory`) — see gotcha §7.12 above. Resolved by creating the database (`serimala_scholarstest`) and a matching user via the cPanel UI, then hand-editing `.env` on the host.

A real security incident happened during the fix for #2, worth recording plainly rather than glossing over: a DB password was exposed in plaintext in the session transcript because an interactive `read -s` prompt, fed via a pasted multi-line script into cPanel's web terminal, didn't suppress echo (gotcha §7.11). The exposed password was rotated immediately and never used. A second, unrelated mix-up during the same fix — the Wanderer initially created a DB user named `serimala_scholarstest_app`, then separately created `serimala_scholarstest` (matching the database name exactly, no suffix) and that's the one actually wired into `.env` — worth noting only because it means the `_app`-suffixed user may still exist unused in cPanel's MySQL Users list and can be deleted if it's confirmed to have nothing attached to it.

**Final successful run** (`gh run 44233316747`, manually dispatched after `.env`/DB were fixed): `build` (42s) — composer/npm install, Vite build, rsync — then `deploy-test` (34s) — backup, `migrate --force`, `storage:link`, cache rebuild, `queue:restart`, health check — both green. Verified directly: `https://scholars.olympia-education.com/up` → 200, `https://scholars.olympia-education.com/` → 200.
