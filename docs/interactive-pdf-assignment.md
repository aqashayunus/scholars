# Interactive PDF Assignment System — Architecture & Developer Documentation

## 1. Architecture Overview

The **Interactive PDF Assignment Answering System** in Olympia Scholars upgrades the student assignment workflow from an external "Download → Open in 3rd-party app → Annotate → Save → Re-upload" cycle to a native, in-browser digital worksheet experience.

Students open their assignments directly inside the Olympia Scholars platform, review the original assignment PDF, write/draw using an iPad Apple Pencil, stylus, or desktop pointer on a synchronized transparent overlay canvas, have their annotations autosaved continuously (with offline resilience via IndexedDB), and submit with one tap.

### High-Level Architectural Flow

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              STUDENT BROWSER / IPAD                              │
│                                                                                 │
│  ┌─────────────────────────┐           ┌─────────────────────────────────────┐  │
│  │     PDF.js Viewer       │           │      Transparent Canvas Layer       │  │
│  │ (Renders source pages)  │ ◄───────► │ (High-DPI Coordinate Normalization) │  │
│  └───────────┬─────────────┘           └──────────────────┬──────────────────┘  │
│              │                                            │                     │
│              ▼                                            ▼                     │
│    Original Source PDF                        Drawing Engine (PointerEvents)    │
│    (Read-Only & Immutable)                    - Apple Pencil palm rejection     │
│                                               - Vector strokes (JSON)           │
│                                                           │                     │
│                                                           ▼                     │
│                                                Dual-Tier Storage Manager        │
│                                                - Tier 1: Local IndexedDB        │
│                                                - Tier 2: Debounced Server Sync  │
│                                                           │                     │
│                                                           ▼                     │
│                                                Client-Side Flattener            │
│                                                (pdf-lib renders vector / PNGs   │
│                                                 onto original PDF binary)       │
└───────────────────────────────────────────────────────────┼─────────────────────┘
                                                            │
                            POST /submit (Multipart: final_pdf + metadata)
                            PATCH /page (JSON: annotation_json)
                                                            │
                                                            ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          LARAVEL 11 BACKEND (PHP 8.2)                            │
│                                                                                 │
│  ┌───────────────────────────────────────────────────────────────────────────┐  │
│  │                      InteractiveAssignmentController                      │  │
│  │                                                                           │  │
│  │  - Enforces StudentAssignmentSubmissionPolicy (Course scoping & IDOR)     │  │
│  │  - Atomic Transactions & Optimistic Concurrency Control                   │  │
│  │  - Rate Limiting (60 saves/min throttle)                                  │  │
│  │  - Multi-location PDF storage resolution (Storage / symlinks / local paths)│  │
│  └─────────────────────────────────────┬─────────────────────────────────────┘  │
│                                        │                                        │
│          ┌─────────────────────────────┴─────────────────────────────┐          │
│          ▼                                                           ▼          │
│  ┌───────────────────────────────┐                   ┌───────────────────────┐  │
│  │        Database Layer         │                   │    Storage Layer      │  │
│  │ - student_assignment_subm...  │                   │ - source_pdfs/        │  │
│  │ - submission_pages            │                   │ - submissions/final/  │  │
│  │ - student_assignments         │                   │                       │  │
│  └───────────────────────────────┘                   └───────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Core Design Principles
1. **Source Immutability:** The teacher's original assignment PDF is strictly read-only and never modified or overwritten.
2. **Normalized Coordinates:** All strokes are stored in normalized points relative to the original PDF page dimensions ($[0, \text{width}]$ and $[0, \text{height}]$), making them invariant to device scale, orientation, viewport dimensions, and pinch-zoom levels.
3. **Dual-Tier Persistence:** Instant write-through to client IndexedDB ensures students never lose work even if Wi-Fi drops, followed by debounced server background synchronization.
4. **Zero Server Binaries (cPanel Compatible):** PDF flattening is performed on the client using `pdf-lib`, eliminating the need for Node.js daemons, Puppeteer, Chrome headless, or ImageMagick on shared hosting.

---

## 2. Database Structure

The interactive system introduces a clean, decoupled relational schema that links to existing `assignments` and `student_assignments` records without breaking legacy workflows.

### Table Schema & Relationships

```
┌─────────────────────────┐
│       assignments       │
├─────────────────────────┤
│ id                      │
│ file_path               │
│ is_interactive_pdf [NEW]│ ◄─── (boolean, default false, indexed)
└───────────┬─────────────┘
            │ 1
            │
            │ N
┌───────────▼─────────────────────────┐
│     student_assignments             │
├─────────────────────────────────────┤
│ id                                  │
│ student_id                          │
│ assignment_id                       │
│ status ('pending','submitted',...)  │
│ file_path (submitted PDF)           │
│ marks / grade / feedback            │
└───────────┬─────────────────────────┘
            │ 1
            │
            │ 1
┌───────────▼─────────────────────────┐
│   student_assignment_submissions    │
├─────────────────────────────────────┤
│ id (PK, unsignedBigInteger)         │
│ student_assignment_id (FK, unique)  │
│ assignment_id (FK, index)           │
│ student_id (FK, index)              │
│ status ('draft','submitted','lock') │
│ current_page (unsignedSmallInteger) │
│ zoom_level (decimal 4,2)            │
│ total_pages (unsignedSmallInteger)  │
│ version (unsignedInteger)           │
│ final_pdf_path (nullable string)    │
│ submitted_at (nullable timestamp)   │
│ created_at / updated_at             │
└───────────┬─────────────────────────┘
            │ 1
            │
            │ N
┌───────────▼─────────────────────────┐
│         submission_pages            │
├─────────────────────────────────────┤
│ id (PK, unsignedBigInteger)         │
│ submission_id (FK, cascade)         │
│ page_number (unsignedSmallInteger)  │
│ annotation_json (longText)          │
│ stroke_count (unsignedInteger)      │
│ page_version (unsignedInteger)      │
│ created_at / updated_at             │
│ UNIQUE(submission_id, page_number)  │
└─────────────────────────────────────┘
```

### Key Schema Features
- **`assignments.is_interactive_pdf`**: Distinguishes legacy file-upload assignments from in-browser interactive PDF assignments.
- **`student_assignment_submissions`**: Tracks the interactive session, current page, zoom preferences, submission state, optimistic concurrency version, and path to the compiled final PDF.
- **`submission_pages`**: Stores each page's annotation strokes independently. Unique composite index on `(submission_id, page_number)` guarantees integrity.
- **Casting & Helpers**:
  - `StudentAssignmentSubmission`: `isDraft()`, `isSubmitted()`, `isLocked()`.
  - `SubmissionPage`: `getStrokesAttribute()` automatically decodes `annotation_json` into PHP arrays.

---

## 3. PDF Rendering Engine

### PDF.js Integration
The interactive viewer utilizes the pre-bundled PDF.js library (`/vendor/pdfjs/pdf.min.mjs` and `/vendor/pdfjs/pdf.worker.min.mjs`).

1. **Streaming PDF Loader:**
   - PDF binary is streamed securely through the authenticated route `GET /student/assignments/{assignment}/pdf-stream`.
   - The controller's `resolvePdfPath()` detects files stored in `Storage::disk('public')`, raw paths, or symlinked public storage paths.
2. **Page Viewport & HiDPI Backing Canvas:**
   - For each page, a container element `<div class="page-container" data-page-number="N">` is created.
   - The underlying PDF is rendered onto a background `<canvas class="pdf-canvas">` using `window.devicePixelRatio` (typically $2\times$ or $3\times$ on Retina iPad displays) to guarantee crisp text rendering.
   - A transparent overlay `<canvas class="drawing-canvas">` is mounted directly over the PDF canvas with identical CSS width/height and backing resolution.

---

## 4. Annotation & Drawing System

The annotation engine is implemented in modular vanilla ES6 scripts located in `resources/js/pdf/`:

| Module | Responsibility |
|---|---|
| `coordinate-normalizer.js` | Transforms client pointer coordinates $(X_{client}, Y_{client})$ into PDF-native points $(X_{pdf}, Y_{pdf})$ invariant to zoom and screen DPI. |
| `drawing-engine.js` | Handles pointer events (`pointerdown`, `pointermove`, `pointerup`), pen pressure, stroke rendering, and canvas clearing. |
| `eraser-engine.js` | Vector stroke proximity eraser that tests line-segment distance against eraser radius and deletes matching strokes. |
| `history-manager.js` | Per-page Undo and Redo transaction stacks. |
| `storage-manager.js` | IndexedDB local storage and debounced server sync queue. |
| `pdf-flattener.js` | Merges annotations with original PDF using `pdf-lib` into a standard, self-contained PDF. |
| `pdf-viewer.js` | Coordinates PDF.js document loading, zoom scaling, and page switching. |
| `interactive-app.js` | Master orchestrator binding UI controls, shortcut keys, autosave timers, and network state. |

### Tool Set
- **Pen Tool:** Fine-tip ballpoint pen (1px to 8px) with full color selection (#000000, #1E40AF, #DC2626, #047857) and pressure sensitivity.
- **Highlighter Tool:** Semi-transparent marker with blend mode (`rgba(...)` or canvas `globalAlpha = 0.35`) and broader width (12px to 32px).
- **Stroke Eraser:** Erases complete vector strokes upon proximity contact.
- **Clear Page:** Wipes all annotations from the current active page (with undo capability).
- **Undo / Redo:** Full step-by-step history tracking per page.

---

## 5. Apple Pencil & Touch Handling

To deliver a native iPad note-taking feel, the canvas uses standard W3C Pointer Events:

1. **Hardware Differentiation:**
   - `e.pointerType === 'pen'`: Handled strictly as drawing input. Pressure (`e.pressure`) dynamically scales the stroke line width:
     $$\text{strokeWidth} = \text{baseWidth} \times (0.5 + 0.5 \times \text{pressure})$$
   - `e.pointerType === 'touch'`: If drawing mode is active and Apple Pencil is detected, touch events are ignored for drawing to provide **hardware palm rejection**. Two-finger touch gestures trigger smooth pan and zoom.
   - `e.pointerType === 'mouse'`: Standard desktop cursor drawing.
2. **Preventing Viewport Interference:**
   - The drawing canvas applies CSS `touch-action: none` while drawing is engaged, preventing iOS Safari from triggering rubber-band scrolling, pull-to-refresh, or accidental back-navigation gestures.
   - Single-finger touches on toolbars or the document outer scroll area scroll naturally without leaving phantom marks.

---

## 6. Autosave Architecture

To prevent data loss:

1. **Debounced Sync:**
   - As the student draws, strokes accumulate in memory and are written immediately to IndexedDB.
   - A 1,500ms debounce timer triggers an asynchronous `PATCH /student/assignments/{submission}/page` call with the current page's payload.
2. **Page Navigation Trigger:**
   - Navigating between pages (e.g. Page 1 to Page 2) instantly flushes any pending debounced save for the active page before switching.
3. **Status Indicator:**
   - The header displays real-time sync status:
     - `● All changes saved`: Up to date with backend.
     - `○ Saving...`: Network sync in flight.
     - `⚠ Saved offline`: Backend unreachable, stored locally in IndexedDB.
4. **Optimistic Concurrency Control:**
   - Every page save transmits `page_version`. If a concurrent tab or session has bumped the version, the backend gracefully handles conflict resolution.
5. **Rate Limiting:**
   - The page save route is throttled at 60 requests per minute per IP/user (`throttle:60,1`) to protect server resources under active drawing conditions.

---

## 7. Offline & Local Recovery

The system incorporates an IndexedDB storage layer powered by the `StorageManager` class:

- **Database Name:** `OlympiaScholar_InteractivePDF`
- **Store Name:** `annotations`
- **Key Pattern:** `sub_{submissionId}_p_{pageNumber}`
- **Recovery Workflow:**
  1. When opening the interactive workspace, `StorageManager` checks if local IndexedDB contains newer unsaved strokes than the server database.
  2. If the user loses internet connection during an assignment, strokes continue saving to IndexedDB with a persistent visual notification (`Saved locally (offline)`).
  3. When network connectivity restores (`window.addEventListener('online')`), the queue automatically synchronizes pending page strokes to the Laravel backend.

---

## 8. Submission Workflow

```
[ Student Clicks "Submit Assignment" ]
                  │
                  ▼
[ Client Validation: Check Unsaved Changes ]
                  │
                  ▼
[ Confirmation Modal: "Submit Assignment? Once submitted, your work will be locked for grading." ]
                  │
                  ▼
[ Render Flattened PDF in Browser (pdf-lib) ]
  - Converts vector strokes into high-DPI raster layers
  - Embeds each page overlay directly onto source PDF pages
                  │
                  ▼
[ POST /student/assignments/{submission}/submit ]
  - Payload: multipart/form-data with final_pdf file
                  │
                  ▼
[ Backend Transaction (DB::transaction) ]
  - Verify submission status is 'draft' (reject if already submitted/locked)
  - Store flattened PDF in storage/app/public/assignments/submissions/{id}/
  - Update student_assignment_submissions.status = 'submitted'
  - Update student_assignment_submissions.submitted_at = now()
  - Update parent student_assignments.status = 'submitted'
  - Update parent student_assignments.file_path = final_pdf_path
  - Update parent student_assignments.submitted_at = now()
                  │
                  ▼
[ Lock UI & Redirect to Student Tasks View with Success Toast ]
```

---

## 9. Final PDF Generation (Zero Server Binaries)

### Rationale
Shared cPanel hosting environments frequently disable `exec()`, `shell_exec()`, and do not permit installing headless Chromium, Puppeteer, or Node.js services.

### Client-Side Flattening Implementation
Using `pdf-lib` in `resources/js/pdf/pdf-flattener.js`:
1. Fetches the clean source PDF binary directly into an `ArrayBuffer`.
2. Loads the document via `PDFDocument.load(sourceBytes)`.
3. Iterates through each page:
   - Retrieves the page's vector stroke collection from IndexedDB/memory.
   - If annotations exist, creates an offscreen `<canvas>` at matching PDF page dimensions ($72 \text{ DPI} \times \text{scale factor}$).
   - Re-draws strokes using exact normalized coordinates.
   - Exports the offscreen canvas as a PNG `dataURL`.
   - Embeds the PNG into the PDF via `pdfDoc.embedPng(pngBytes)`.
   - Draws the image overlay seamlessly onto the PDF page using `page.drawImage()`.
4. Saves the combined document via `pdfDoc.save()`.
5. Uploads the resulting `Blob` to the backend submit endpoint.

The result is a universally viewable, standard PDF containing both the original assignment questions and the student's handwritten solutions, viewable in Adobe Acrobat, Apple Books, Google Drive, or any browser.

---

## 10. Authorization & Security

Security is enforced at both the route middleware layer and the policy layer:

1. **Authentication:** All interactive routes require `auth` middleware.
2. **Policy Enforcement:** Handled by [StudentAssignmentSubmissionPolicy.php](file:///c:/xampp/htdocs/olympiascholar/app/Policies/StudentAssignmentSubmissionPolicy.php):
   - `view`:
     - Student can view only their own submission.
     - Teacher can view only submissions belonging to courses they teach.
     - Admin can view all submissions across the platform.
   - `update` / `savePage`:
     - Strictly restricted to the owning student when the submission is in `draft` status.
     - If the status is `submitted` or `locked`, `update` returns HTTP 403 Forbidden.
   - `submit`:
     - Only the owning student can submit, and only once.
   - `review`:
     - Restricted to authorized teachers of the course and system administrators.
3. **IDOR & Anti-Tampering:**
   - Attempting to access another student's submission URL or stream PDF returns HTTP 403 Forbidden.
   - Stroke payloads undergo server-side validation (max 50,000 characters/strokes per page, valid JSON).

---

## 11. Storage Management & Directory Structure

All files reside within Laravel's storage filesystem (`storage/app/public/`):

```
storage/app/public/
├── assignments/
│   ├── source_pdfs/                  # Immutable teacher assignment PDFs
│   │   └── form4-addmath-task1.pdf
│   └── submissions/
│       └── final/                    # Flattened, student-submitted PDFs
│           ├── submission_1_1727680000.pdf
│           └── submission_2_1727680000.pdf
```

### Path Resolution
`InteractiveAssignmentController::resolvePdfPath()` provides multi-location fallback:
1. `Storage::disk('public')->path($cleanPath)`
2. Direct disk path check if absolute
3. Public path check `public_path($cleanPath)`
4. Root fallback for legacy relative storage references

---

## 12. Deployment Guide (cPanel / Shared Hosting)

1. **Run Migrations:**
   ```bash
   php artisan migrate --force
   ```
2. **Compile Production Frontend Assets:**
   ```bash
   npm run build
   ```
   *Note: Ensure `public/build` is committed or deployed to the cPanel `public_html` directory.*
3. **Verify Storage Symlink:**
   ```bash
   php artisan storage:link
   ```
   *If SSH is disabled on cPanel, create a simple PHP script or cron job with `Artisan::call('storage:link');` once.*
4. **Environment Variables:**
   Verify `.env` has:
   ```env
   FILESYSTEM_DISK=public
   APP_URL=https://your-domain.com
   ```

---

## 13. Testing & Automated Verification

A dedicated feature test suite is located in [tests/Feature/InteractiveAssignmentTest.php](file:///c:/xampp/htdocs/olympiascholar/tests/Feature/InteractiveAssignmentTest.php).

### Executing Tests
```bash
php artisan test --filter=InteractiveAssignmentTest
```

### Test Coverage Matrix
| Test Case | Verification Target |
|---|---|
| `test_student_can_open_interactive_viewer_for_enrolled_assignment` | Enrolled student opens viewer, receives HTTP 200, draft submission created. |
| `test_student_cannot_access_another_students_submission` | IDOR prevention; accessing another student's submission returns HTTP 403. |
| `test_unauthenticated_user_redirected_to_login` | Guest redirect to login screen. |
| `test_teacher_can_review_interactive_submission` | Course teacher accesses read-only submission reviewer. |
| `test_teacher_cannot_review_submission_from_unrelated_course` | Unauthorized teacher blocked from viewing submissions outside their course. |
| `test_admin_can_review_any_interactive_submission` | Administrator has global review capability. |
| `test_student_can_save_page_annotation_draft` | Autosave endpoint saves page strokes and increments version. |
| `test_malformed_annotation_payload_is_rejected` | Malformed JSON payload triggers HTTP 422 Unprocessable Entity. |
| `test_student_can_submit_assignment_and_it_locks` | Final submission uploads PDF, locks status, updates parent records. |
| `test_submitted_assignment_rejects_further_page_saves` | Post-submission stroke save attempts are rejected with HTTP 403. |

---

## 14. Known Limitations & Future Roadmap

1. **Multi-page Batch Flattening on Low-Memory Mobile Devices:**
   - PDFs with $>50$ pages may experience memory pressure during client-side flattening. Recommended max length for interactive assignments is 1–25 pages.
2. **Rich Text / Typed Notes:**
   - The current engine is optimized for handwriting, Apple Pencil, and drawing. A typed text box tool can be added in a future phase using canvas text rendering.
3. **Real-time Teacher Co-Presence:**
   - Currently, teachers review submitted work or drafts asynchronously. A future enhancement could use WebSockets (Laravel Reverb) for live collaborative annotations during online classes.
