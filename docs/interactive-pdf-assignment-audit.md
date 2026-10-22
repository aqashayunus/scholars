# Technical Audit: Interactive PDF Assignment Architecture

**Date:** September 30, 2026  
**Project:** Olympia Scholars (Laravel 11 / PHP 8.2 / Tailwind CSS / Alpine.js)  
**Document Target:** Comprehensive audit of existing assignment, submission, PDF rendering, storage, and authorization systems prior to implementing the Interactive PDF Assignment Answering System.

---

## 1. Existing Architecture Overview

Olympia Scholars is a multi-role educational portal serving **Admins**, **Teachers**, **Students**, and **Parents**. The platform delivers online classes, diagnostic testing, syllabus curriculum progression, billing/invoicing, and assignment homework submissions.

### Role & Access Ecosystem
- **Students:** Enrolled in courses via `enrollments` table; access assignments through `student.tasks` (`StudentDashboardController::tasks`). Can be restricted if payments are overdue (`EnsureStudentAccessNotRestricted` middleware).
- **Teachers:** Manage courses, live classes, learning materials, and homework assignments via `teacher.tasks` (`TeacherDashboardController::tasks`). Grade homework submissions via `TeacherDashboardController::gradeSubmission`.
- **Admins:** Global curriculum oversight, course creation, global assignments, diagnostic tests, and submissions grading via `admin.tasks` (`AdminDashboardController::storeTask`, `getTaskSubmissions`, `gradeTask`).
- **Parents:** Monitor enrolled children, view attendance, academic progress, and homework submissions (`ParentDashboardController::childAssignments`).

---

## 2. Relevant Existing Files & Code Modules

| Component | File Path | Architectural Role |
| :--- | :--- | :--- |
| **Assignment Model** | `app/Models/Assignment.php` | Stores assignment metadata, original PDF attachment (`file_path`, `file_name`, `file_size`), questions data, and type (`'Assignment'`, `'Diagnostic Test'`, `'Assessment'`, `'Exam'`). |
| **StudentAssignment Model** | `app/Models/StudentAssignment.php` | Pivot/tracking record linking student to assignment. Stores status (`pending`, `submitted`, `graded`, `late`), score, feedback, submission file attachment, and timestamps. |
| **Student Controller** | `app/Http/Controllers/StudentDashboardController.php` | `tasks()` loads pending/completed/catch-up assignments. `submitTask()` accepts text notes and file upload (`pdf,doc,docx,png,etc.`). |
| **Teacher Controller** | `app/Http/Controllers/TeacherDashboardController.php` | `storeGeneralTask()` handles teacher assignment creation. `gradeSubmission()` handles score recording and notifications. |
| **Admin Controller** | `app/Http/Controllers/AdminDashboardController.php` | `storeTask()` creates global or course-specific tasks. `getTaskSubmissions()` returns JSON submission records. `gradeTask()` records grades. |
| **Diagnostic Controller** | `app/Http/Controllers/DiagnosticTestController.php` | Implements Mozilla PDF.js rendering and resilient `resolvePdfPath()` helper for cross-environment storage access without symlink dependencies. |
| **Student Tasks View** | `resources/views/student/tasks.blade.php` | Alpine.js interface for viewing tasks, downloading worksheet attachments, and submitting files. |
| **Teacher Tasks View** | `resources/views/teacher/tasks.blade.php` | Alpine.js teacher workspace with slide-over modals for reviewing submissions and inputting scores/feedback. |
| **Admin Dashboard View** | `resources/views/admin/dashboard.blade.php` | Admin workspace containing courses, tasks, and submission review tables. |
| **Bundled PDF.js Assets** | `public/vendor/pdfjs/` | Pre-bundled `pdf.min.js` (320 KB) and `pdf.worker.min.js` (1.08 MB) with automated CDN fallback. |

---

## 3. Database Structure & Current Submission Workflow

### Current Database Tables
1. **`assignments`:**
   - Primary Key: `id`
   - Relationships: `course_id` (nullable for global), `is_global` (boolean), `target_level` (string).
   - Content: `title`, `type`, `topic`, `description`, `instructions`, `due_date`, `total_marks`.
   - File Attachments: `file_path` (string, e.g. `assignments/filename.pdf`), `file_name`, `file_size`.
   - Diagnostic Fields: `answer_key`, `total_questions`, `time_limit_minutes`, `section_b_answer_key`, `section_b_questions`, `questions_data`.
2. **`student_assignments`:**
   - Primary Key: `id`
   - Foreign Keys: `student_id` (FK to `students`), `assignment_id` (FK to `assignments`).
   - Workflow Fields: `status` (`pending`, `submitted`, `graded`, `late`), `score`, `submitted_at`.
   - Submission Content: `submission_notes` (text), `file_path` (string), `file_name` (string), `teacher_feedback` (text).

### The Inefficient Legacy Workflow
```text
Teacher uploads Assignment PDF
              ↓
Student clicks "Download Worksheet" in modal
              ↓
Student exits platform, opens external PDF app (Notability / Acrobat)
              ↓
Student writes answers, exports modified PDF
              ↓
Student returns to Olympia Scholars, clicks "Attach Completed Homework File"
              ↓
Uploads file, clicks "Submit Task"
```

---

## 4. Existing PDF Handling Analysis

The application already has a working implementation of Mozilla PDF.js in `resources/views/student/diagnostic.blade.php`:
- **Asset Loading:** Loads `public/vendor/pdfjs/pdf.min.js` with CDN fallback to `cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js`.
- **Worker Configuration:** Configures `pdfjsLib.GlobalWorkerOptions.workerSrc` using a robust Blob fallback for environments with CORS or proxy restrictions.
- **Cross-Platform Path Resolution:** In `DiagnosticTestController::resolvePdfPath()`, a lookup checks:
  1. `storage_path('app/public/' . $clean)`
  2. `storage_path('app/' . $clean)`
  3. `public_path('storage/' . $clean)`
  4. `public_path($clean)`
  This ensures compatibility with cPanel shared hosting where `php artisan storage:link` is unavailable or restricted.
- **HiDPI Scaling:** Uses `window.devicePixelRatio` to multiply backing canvas dimensions while preserving CSS layout dimensions, producing crisp typography.

---

## 5. Reusable Components & Modules

1. **Storage Path Resolver:** Reuse `resolvePdfPath()` pattern for `InteractiveAssignmentController` to safely stream assignment documents regardless of local or cPanel deployment topology.
2. **Local PDF.js Vendor Assets:** Point to `public/vendor/pdfjs/pdf.min.js` without duplicating files or installing redundant viewer packages.
3. **Notification Engine:** Reuse `UserNotification::create()` for alerting teachers of student submissions and students of teacher grades.
4. **Student Access Restriction:** Respect `EnsureStudentAccessNotRestricted` middleware to prevent fee-defaulted students from accessing academic work.
5. **Grading Pipeline:** Reuse existing `gradeSubmission()` and `gradeTask()` endpoints to prevent parallel grading systems.

---

## 6. Identified Risks & Mitigation Architecture

| Risk | Mitigation |
| :--- | :--- |
| **Overwriting Original PDF** | Enforce immutability. Original PDF is read-only. Student work is saved purely as vector stroke JSON records in a child table. |
| **IDOR / Student Data Leakage** | Enforce strict Laravel Policy (`StudentAssignmentSubmissionPolicy`) on all viewing, saving, and submission endpoints. |
| **Mobile Safari Canvas Memory Limits** | Implement page virtualization. Free canvas memory for distant pages on large PDFs ($>15$ pages). |
| **Accidental Finger Scribbles on iPad** | Use W3C Pointer Events. Filter drawing exclusively to `e.pointerType === 'pen'` and desktop `e.pointerType === 'mouse'`. Let single finger touch perform native scrolling and panning. |
| **Network Drops / Data Loss** | Implement client-side IndexedDB buffer. Every stroke is persisted locally first, then synced to the server with a 1,000ms debounce. |
| **cPanel Shared Hosting PDF Generation** | Avoid server-side Chrome/Node.js dependencies. Use client-side `pdf-lib` to flatten the final submission PDF upon student submit, verified and stored by Laravel. |
| **Multi-tab Concurrency Conflicts** | Maintain incremental `version` numbers per page. Return HTTP 409 Conflict if an older tab attempts to overwrite newer server state. |

---

## 7. Recommended Integration Points

1. **Database:**
   - Add `is_interactive_pdf` (boolean, default false) to `assignments` table.
   - Create `student_assignment_submissions` table for high-level submission lifecycle tracking.
   - Create `submission_pages` table for normalized, page-by-page stroke storage.
2. **Controllers & Routes:**
   - Create `InteractiveAssignmentController` under `app/Http/Controllers/`.
   - Register routes under `student.assignments.interactive`, `student.submissions.*`, and teacher review routes.
3. **Student Task UI:**
   - In `resources/views/student/tasks.blade.php`, check `task.is_interactive_pdf`. If true, render direct "Launch Interactive Assignment" button.
4. **Teacher / Admin UI:**
   - Add "Interactive Work" review button in `resources/views/teacher/tasks.blade.php` and `resources/views/admin/dashboard.blade.php` opening the interactive viewer in review mode.
