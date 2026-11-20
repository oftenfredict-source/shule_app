# Teacher Exams Module - Mobile API

Web menu: **Exams** (sidebar)  
Mobile base: `{HOST}/api/v1/teacher`

Submenus:

1. Add Results  
2. Supervise  
3. Exam Papers  
4. Approvals  
5. Attendance  

This is the full hand-off for the mobile app. Visibility flags come from `GET /capabilities`.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (or `multipart/form-data` for file uploads) |

- Teacher tokens only. Academic year must be open.
- `401` → log out. `403` → hide/disable. `422` → show `message`.
- Dates: `Y-m-d`. Do not render raw path strings as UI cards.

### Capability gates (`GET /capabilities`)

| Submenu | Show when |
|---|---|
| Add Results | `can_add_results === true` |
| Supervise | always |
| Exam Papers | always |
| Approvals (exam **papers**) | always (badge: `badges.pending_paper_approvals`) |
| Attendance | `can_collect_exam_attendance === true` |
| Result approvals (Academic / dashboard) | badge: `badges.pending_result_approvals` → `GET /result-approvals` (not under Exams → Approvals) |

### ⚠ What went wrong (read this)

Academic teachers saw **Approvals = 0 pending** on mobile while the website still had work to do. Two separate bugs/confusions:

1. **Wrong screen** — Exams → **Approvals** is only for **exam papers** (`/exam-paper-approvals`). Approving **marks/results** (website `/approve_result/{examID}`, e.g. Midterm exam 45) is a different feature: use **`GET /result-approvals`** and **`GET /result-approvals/{examID}`**. Academic’s main pending work is usually **result** approvals (`badges.pending_result_approvals`), not paper approvals.
2. **Year filter hid papers** — Paper Approvals used to default to the current calendar / academic year, so older pending papers (badge `pending_paper_approvals > 0`) did not appear and the list looked empty. Mobile now defaults to `year=all` so `pending_count` matches the badge. If the app re-applies a year/term filter, pending can look empty again even when the badge is non-zero — that is expected; clear filters or call without `year` / with `year=all`.

Do **not** bind Academic “approve Midterm results” UI to `/exam-paper-approvals`.

---

## 1. Module index — `GET /exams`

Returns submenu list with `visible` / `badge` / `path` for navigation.

---

## 2. Add Results

Web: `/admin/add-results`  
Gate: `can_add_results`

### Flow

```text
GET /add-results
  → pick year / exam / class / subclass / subject
GET /add-results/class-subject?classID=&subclassID=&subjectID=
  → class_subjectID
GET /subjects/{classSubjectID}/examinations?for_view=1
GET /subjects/{classSubjectID}/students?all_subclasses=1
GET /subjects/{classSubjectID}/results/{examID}
GET /subjects/{classSubjectID}/papers          (duo)
GET /exam-papers/questions/{classSubjectID}/{examID}
POST /results/save
GET  /subjects/{classSubjectID}/results/{examID}/pdf
POST /results/upload-excel | /results/parse-excel
```

### `GET /add-results`

Hub with assigned catalogs: `years`, `classes`, `subclasses`, `subjects` (+ duo `components`), `exams`, `result_button_status`.

### `GET /add-results/class-subject`

Query: `classID`, `subjectID` required; `subclassID` optional (`all` / empty = all streams).

### `POST /results/save`

```json
{
  "class_subjectID": 55,
  "examID": 12,
  "componentID": null,
  "test_week": null,
  "results": "[{\"studentID\":1,\"marks\":45}]"
}
```

`results` may be a JSON string (website) or array. Same as Teaching subjects results APIs.

---

## 3. Supervise

Web: `/supervise_exams`  
Always visible.

### Flow

```text
GET /exams/supervise?category=all|school_exams|test&year=2026
  → assignments[]
GET /exams/supervise/students?hall_id=&subject_id=&examID=&exam_category=&timetable_id=&classID=&scope=&date=
GET /exams/supervise/take-attendance?... (same query keys)
GET /exams/supervise/hall-students/{hall_id}?subject_id=&examID=&exam_category=&classID=&timetable_id=&scope=
POST /exams/{examID}/attendance
GET /exams/supervise/timetable
```

**Query key names use snake_case:** `hall_id`, `subject_id` (not `hallID`).

### Assignment row (hub)

Includes `examID`, `hall_id`, `subjectID`, `exam_date`, `start_time` / `end_time`, `students_count`, `is_active`, `exam_category`, etc.

### `POST /exams/{examID}/attendance`

Bulk:

```json
{
  "subjectID": 45,
  "date": "2026-11-17",
  "attendance": [
    { "studentID": 1, "status": "Present" },
    { "studentID": 2, "status": "Absent" }
  ]
}
```

Or single: `{ "studentID": 1, "subjectID": 45, "status": "Present" }`.  
Status: `Present` | `Absent`.

---

## 4. Exam Papers

Web: `/exam_paper` (Exams / Exam Papers)  
Always visible.  
**One hub call powers the whole page:** `GET /exam-papers`

### 4.1 Website tabs → API (app must mirror these)

| Website tab | Badge / count | Where the data is | How to build the list |
|---|---|---|---|
| **Upload** | — | Form uses `subjects`, `examinations` | Show create form (see §4.2) |
| **Pending** | `tabs.pending.count` | `pending_slots[]` | Scheduled slots that still need a file (tests this week / next week) |
| **Approved** | `tabs.approved.count` | `my_papers[]` | Keep rows where `status == "approved"` |
| **Rejected** | `tabs.rejected.count` | `my_papers[]` | Keep rows where `status == "rejected"` |
| **Waiting** | `tabs.waiting.count` | `my_papers[]` | Keep rows where `status` is `wait_approval` **or** `pending` (already uploaded, in approval chain) |

Client-side filters on Approved / Rejected / Waiting (website Exam / Term / Year dropdowns): filter `my_papers` by `examID`, `examinations[].term`, `examinations[].year` — no extra API needed.  
Refresh list anytime with `GET /exam-papers/mine` (same papers, JSON).

Empty tabs: show empty state (website: “No … papers found”). That is normal.

### 4.2 Upload tab form → API fields

| Website label | API field | Notes |
|---|---|---|
| Examination * | `examID` | From `examinations[]` |
| Subject * | `class_subjectID` | From `subjects[]` (`label` for display) |
| Duo paper * | `componentID` | Only if `subjects[].is_duo_subject` — options in `duo_papers[]` |
| Exam paper file * | `file` | multipart pdf/doc/docx ≤10MB |
| Use existing upload (optional) | `existing_exam_paper_id` | Pick from already uploaded papers for same teacher; skip new `file` |
| Question Formats & Marks (Total 100) | see below | Required for **Secondary** unless exam has `allow_no_format: true` |
| Qn / Description | `question_descriptions[]` | One per row |
| Marks | `question_marks[]` | Must sum to **100** (or duo paper `max_marks`) |
| Opt | `question_optional[]` | `1` / `0` per row |
| Optional ranges | `optional_ranges[]` + `optional_required_counts[]` | When optional questions are grouped |
| Period / scheduled subject (tests) | `GET /exam-papers/available-periods`, `GET /exam-papers/scheduled-subjects` then `test_week`, `test_date` | Weekly / Monthly exams |
| Pending slot “Upload now” | `placeholder_id` = pending slot `exam_paperID` | From **Pending** tab |

Website warning *“Marks are less than 100…”* is **client-side**. Server also rejects incomplete formats when required.  
Hub includes `question_format_rules` and each exam has `allow_no_format`.

### 4.3 Row actions per tab

| Status | Actions | API |
|---|---|---|
| Pending slot | Upload now | Open Upload form with `placeholder_id` → `POST /exam-papers` |
| Waiting (`pending` / `wait_approval`) | Edit, Download, Chain | `POST /exam-papers/{id}/update`, `GET .../download`, `GET /exam-papers/{examID}/approval-chain` |
| Rejected | Edit & resend, Download, Chain | `POST /exam-papers/{id}/update` or `.../resubmit`, download, chain |
| Approved | Questions, Download, Chain | `GET/POST /exam-papers/{id}/questions`, download, chain |
| Rejection toast | Dismiss | `POST /result-approvals/dismiss-rejection` |

### 4.4 `POST /exam-papers` (multipart)

| Field | Required | Notes |
|---|---|---|
| `examID` | yes | |
| `class_subjectID` | yes | must teach / duo-assign |
| `upload_type` | yes | `upload` |
| `file` | yes* | *or* `existing_exam_paper_id` |
| `componentID` | duo | |
| `test_week`, `test_date` | tests | |
| `placeholder_id` | optional | pending slot |
| `question_descriptions[]`, `question_marks[]`, `question_optional[]` | secondary unless `allow_no_format` | sum marks = 100 |
| `optional_ranges[]`, `optional_required_counts[]` | with optionals | |
| `apply_to_all_subjects` | optional | `"1"` |

Helpers: `GET /exam-papers/available-periods`, `/exam-papers/scheduled-subjects`, `/exam-papers/allowed-classes/{examID}`.

### 4.5 View / download the uploaded file

Use the same endpoint as the website Download button. Response is the **file bytes** (not JSON).

```http
GET /api/v1/teacher/exam-papers/{examPaperID}/download
Authorization: Bearer <token>
Accept: */*
```

| Goal | Call | Notes |
|---|---|---|
| **View / preview in app** (PDF viewer, WebView, etc.) | `GET …/download?inline=1` | `Content-Disposition: inline` — open in viewer |
| **Save / share file** | `GET …/download` | Attachment download for non-PDF; PDFs still return inline by default |

When to show the button: hub row has `has_file: true` / `can_download: true` (pending slots without a file have neither).

Auth: owner of the paper, or an approver in the chain (same rules as web).  
Errors: `403` no access, `404` paper or file missing.

Mobile tip: do **not** expect JSON. Stream/save the body with the returned `Content-Type` (`application/pdf`, Word mime, etc.). For in-app preview prefer `?inline=1` and open PDF in a built-in viewer; for `.doc`/`.docx` usually save then open with an external app.

Approvals screen uses the same URL after `GET /exam-paper-approvals/{examPaperID}/review`.

---

## 5. Exam Paper Approval

Web: `/admin/exam-paper-approval` (Exams / Paper Approval)  
Badge: `badges.pending_paper_approvals`  
**Hub:** `GET /exam-paper-approvals`  
(This is **not** Academic result approval — see §7.)

### Mobile note — empty pending while badge > 0

If you open Approvals and see `pending_count: 0` but `capabilities.badges.pending_paper_approvals` is `1+`, the list is filtered (year / academic year / term / exam). Call the hub **without** a year (server uses `year=all`) or pass `year=all`. Narrowing filters can correctly hide items that still count toward the unfiltered badge.

### 5.1 Website UI → API

| Website | API |
|---|---|
| Tab **Pending** + count | `data.pending[]` / `data.pending_count` |
| Tab **History** + count | `data.history[]` (length = history count) |
| Filter **Year** | query `year` (`all` = every year; omit on first load) |
| Filter **Term** | query `term` = `first_term` \| `second_term` \| empty |
| Filter **Exam** | query `examID` (`examinations[]` or `GET .../filters/exams?year=&term=`) |
| Filter **Class** | query `classID` (`classes[]`) |
| Filter **Subclass** | query `subclassID` (`subclasses[]` or `GET .../filters/subclasses?classID=`) |
| Filter **Subject** | query `subjectID` (`subjects[]` or `POST .../filters/subjects` with `{ "subclassID": ... }`) |
| Week nav (Weekly/Monthly) | query `week` + `available_weeks[]` / `GET .../filters/weeks/{examID}` |
| Table empty “No pending approvals found.” | `pending` is `[]` — check filters before treating as “nothing to do” |
| Row columns Exam / Subject & Class / Teacher / Step / Uploaded | fields on each pending/history item |
| Action Approve / Reject | `POST /exam-paper-approvals/{examPaperID}` |
| Preview / review | `GET /exam-paper-approvals/{examPaperID}/review` |
| Download | `GET /exam-papers/{examPaperID}/download` |

Reload after changing filters:

`GET /exam-paper-approvals?year=2026&term=first_term&examID=&classID=&subclassID=&subjectID=&week=`

### 5.2 `POST /exam-paper-approvals/{examPaperID}`

Approve:

```json
{
  "action": "approve",
  "paper_approval_log_id": 99,
  "approval_comment": "optional"
}
```

Reject:

```json
{
  "action": "reject",
  "paper_approval_log_id": 99,
  "rejection_reason": "Missing questions"
}
```

Use `paper_approval_logID` from the pending row (`can_approve: true`).

---

## 6. Attendance (invigilator assignments)

Web: `/teacher/exam-attendance`  
Gate: `can_collect_exam_attendance`

### Flow

```text
GET /exam-attendance
  → assignments[]
GET /exam-attendance/{id}
  → subjects + students with status
POST /exam-attendance/save
GET /exam-attendance/template?examID=&subjectID=&classID=&subclassID=&with_data=1
```

### `POST /exam-attendance/save`

```json
{
  "examID": 12,
  "subjectID": 45,
  "attendance": [
    { "studentID": 1, "status": "Present" },
    { "studentID": 2, "status": "Absent" }
  ]
}
```

Only include students with `can_enter_result !== false` from the details response.

---

## 7. Result approvals (web: `/approve_result/{examID}`)

Used by **Academic** (and class teacher / coordinator) from dashboard pending cards — **not** the Exams → Approvals paper screen.

Badge: `capabilities.badges.pending_result_approvals`  
List also on `GET /dashboard` → `approvals.pending` / `special` / `waiting`.

### Mobile note — Academic “pending 0” on wrong API

Website link like `http://…/approve_result/45` is **result** approval. Mapping that to `GET /exam-paper-approvals` made the app show **0** (or paper-only items) while Academic still had Midterm/etc. waiting on `/approve_result/45`. Correct mapping:

| Website | Mobile |
|---|---|
| Dashboard “pending your approval” card | `GET /result-approvals` or `dashboard.approvals.pending` |
| Open `/approve_result/45` | `GET /result-approvals/45` |
| Load marks / stats tables | `GET /result-approvals/45/filtered` |
| Approve / reject | `POST /result-approvals/45/submit` |

| Action | Endpoint |
|---|---|
| List pending | `GET /result-approvals` |
| Open exam (hub) | `GET /result-approvals/{examID}` ← mirrors `/approve_result/{examID}` |
| Chain | `GET /result-approvals/{examID}/chain` |
| Filtered results | `GET /result-approvals/{examID}/filtered` |
| Class teacher details | `GET /result-approvals/{examID}/class-teacher` |
| Coordinator details | `GET /result-approvals/{examID}/coordinator` |
| Submit approve/reject | `POST /result-approvals/{examID}/submit` |
| Dismiss paper rejection toast | `POST /result-approvals/dismiss-rejection` |

### Flow

```text
GET /result-approvals
  → pending[] / special[] / waiting[]

Open Midterm (examID 45):
  GET /result-approvals/45
  → current_approval, chain, stats, can_approve

Load marks table:
  GET /result-approvals/45/filtered?classID=&subclassID=

Approve:
  POST /result-approvals/45/submit
  { "action": "approve", "approval_comment": "optional" }
```

### Why mobile looked emptier than `http://…/approve_result/45`

The website page is **several blocks**. If the app only draws the marks list, it looks incomplete next to the browser.

Follow `data.page_sections[]` in order (`visible: true` only):

| # | Website block | Hub field | What it contains |
|---|---|---|---|
| 1 | Approval Chain | `chain[]` | Academic → Headmaster steps |
| 2 | Examination info | `examination` | name, term, dates |
| 3 | **Examination Statistics** | `attendance_stats` | Expected / Present / Absent + **Attendance by Subject** (for exam 45 this lists ~11 subjects). Show when `exam_has_ended` and section `visible` |
| 4 | Filter Results | `filters` | class / subclass pickers |
| 5 | **Statistics za Matokeo** | `results_statistics` | `subject_stats`, `grade_gender_stats`, `class_pass_rates`, charts data |
| 6 | **Results** tables | `results[]` | class → subclass → students → subjects/marks |
| 7 | Approval Action | `approval_action` | Approve / Reject → `POST …/submit` |

**Important for exam 45 (Midterm):**

- Marks in DB exist only for **FORM THREE A · PHYISICS · 19 students**. The website results table is the same — it is **not** missing other subject mark columns on the API.
- The big multi-subject block on the website is **`attendance_stats.by_subject`**, not `results[]`. If the app skips `attendance_stats`, users will say “I don’t see all subjects.”
- Use `results_statistics.subject_stats` for grade A–F breakdowns (Physics A/B/C/…), not only `overall_statistics.grade_stats` (often zeros when student-level grade is N/A).

Reload filters (same as website Filter button):

`GET /result-approvals/{examID}/filtered?main_class_id=all&subclass_id=all`

### `GET /result-approvals/{examID}` hub

```json
{
  "success": true,
  "data": {
    "title": "Approve Results",
    "can_approve": true,
    "show_only_roadmap": false,
    "examination": { "examID": 45, "exam_name": "Midterm", "term": "first_term" },
    "current_approval": {
      "result_approvalID": 48,
      "approval_order": 1,
      "role_name": "Academic",
      "status": "pending"
    },
    "chain": [],
    "overall_statistics": {},
    "results": [
      {
        "class_name": "FORM THREE",
        "subclassID": "20",
        "subclass_name": "A",
        "students": [
          {
            "studentID": 1,
            "admission_number": "S…",
            "student_name": "…",
            "subjects": [{ "subject_name": "PHYSICS", "marks": 83, "grade": "A" }],
            "total_marks": 83,
            "average_marks": 83
          }
        ]
      }
    ],
    "results_total_count": 19,
    "results_statistics": {},
    "actions": {
      "filtered_results": "GET /result-approvals/45/filtered?main_class_id=all&subclass_id=all",
      "submit": "POST /result-approvals/45/submit"
    }
  }
}
```

If `can_approve` is false / `show_only_roadmap` true → show chain only (earlier step still pending); `results` will be empty.

### `POST /result-approvals/{examID}/submit`

```json
{
  "action": "approve",
  "approval_comment": "optional",
  "subclass_id": null,
  "main_class_id": null
}
```

Reject requires `rejection_reason`. Class-teacher / coordinator may need `subclass_id` / `main_class_id`.

**Do not confuse with** `GET /exam-paper-approvals` (exam **papers**). Academic result work is `/result-approvals`.

---

## 8. Suggested app navigation

```text
Exams (GET /exams or capabilities)
 ├─ Add Results      → GET /add-results → save via /results/save
 ├─ Supervise        → GET /exams/supervise → students / take attendance
 ├─ Exam Papers      → GET /exam-papers → upload / manage / PDF
 ├─ Approvals        → GET /exam-paper-approvals → paper approve/reject
 └─ Attendance       → GET /exam-attendance → details → save

Dashboard / Academic result approvals
 └─ Result Approvals → GET /result-approvals → GET /result-approvals/{examID}
```

---

## 9. Related docs

- Auth / capabilities: `docs/TEACHER_MOBILE_API.md`
- Teaching subjects results (overlap with Add Results): `docs/TEACHER_TEACHING_API.md`
