# Teacher Teaching Module - Mobile API

Web section: sidebar **Teaching** (and HOD **Department Logbooks**)  
Mobile base: `{HOST}/api/v1/teacher`  
Auth: `Authorization: Bearer <token>`, `Accept: application/json`

Teaching submenus on the website:

1. Sessions  
2. Tasks  
3. Subjects (students, results, attendance, papers)  
4. Scheme of Work  
5. Lesson Plans  
6. Logbook  
7. Calendar  
8. Department Logbooks (HOD only — shown outside the Teaching flyout)

Class (`My Class`, `Timetable`) is a **separate** sidebar group. Its endpoints are at the end of this doc for convenience.

Capabilities that affect Teaching menus: `GET /capabilities` → `is_hod`, `can_add_results`, `student_portal_enabled` (learning portal is Main, not Teaching).

---

## Shared rules

| Code | Meaning |
|---|---|
| `401` | Log the user out |
| `403` | Not allowed for this teacher |
| `422` | Validation / business rule — show `message` |
| `404` | Not found or not yours |

Dates are `YYYY-MM-DD`. Some endpoints return HTTP `200` with `"success": false` — always check `success`.

---

## 1. Sessions

Full hand-off: **`docs/TEACHER_SESSIONS_API.md`**.  
Read-only Class timetable: **`docs/TEACHER_TIMETABLE_API.md`**.

Quick index: `GET /sessions?week=` → collect/update attendance → assign task → PDF → lesson-plan deep link.

---

## 2. Tasks

| Action | Method | Path | Notes |
|---|---|---|---|
| My tasks list | `GET` | `/tasks` | Query: `status=pending\|approved\|rejected`, `date=YYYY-MM-DD` |

```json
{
  "success": true,
  "data": {
    "filters": { "status": null, "date": null },
    "tasks": [
      {
        "session_taskID": 9,
        "session_timetableID": 12,
        "task_date": "2026-11-17",
        "status": "rejected",
        "topic": "...",
        "subtopic": "...",
        "task_description": "...",
        "admin_comment": "...",
        "can_edit": true,
        "subject_name": "Mathematics",
        "class_name": "FORM ONE A",
        "session_start": "08:00:00",
        "session_end": "08:40:00",
        "approver_name": "Admin Name"
      }
    ]
  }
}
```

Show **Edit & resubmit** only when `can_edit` is true (rejected). New tasks are created from Sessions.

---

## 3. Subjects and results

### Screens
- My subjects cards (student count)
- Students of a subject
- View / add results for an exam
- Excel template, upload, preview
- Subject session attendance report (by date or month)
- Subject exam attendance
- Duo papers list (when school uses paper components)

### Endpoints

| Action | Method | Path | Notes |
|---|---|---|---|
| Subject list | `GET` | `/subjects` | `class_subject_id`, subject, class, `total_students` |
| Students | `GET` | `/subjects/{classSubjectID}/students` | Main or duo paper teacher |
| Examinations | `GET` | `/subjects/{classSubjectID}/examinations` | Add `for_view=1` for the "View results" list (includes exams with entry closed) |
| Results | `GET` | `/subjects/{classSubjectID}/results` | Optional `/{examID}` |
| Results PDF | `GET` | `/subjects/{classSubjectID}/results/{examID}/pdf` | PDF bytes |
| Excel template | `GET` | `/subjects/{classSubjectID}/results/{examID}/template` | xlsx |
| Save marks | `POST` | `/results/save` | JSON below |
| Upload Excel | `POST` | `/results/upload-excel` | multipart `file`, `class_subject_id`, `exam_id` |
| Preview Excel | `POST` | `/results/parse-excel` | multipart |
| Exam attendance | `GET` | `/subjects/{classSubjectID}/exam-attendance` | `examID`, `subjectID` |
| Session attendance report | `GET` | `/subjects/{classSubjectID}/session-attendance` | `filter_type=date\|month`, `attendance_date` or `month=YYYY-MM` |
| Duo papers | `GET` | `/subjects/{classSubjectID}/papers` | |
| All streams (bulk) | `GET` | `/results/all-streams/{classID}/{subjectID}` | |
| Resolve class-subject | `GET` | `/resolve-class-subject` | `classID`, `subjectID`, `subclassID` |
| Term helpers | `GET` | `/results/exams-for-term`, `/results/tests-for-term`, `/results/school-exams-for-term` | |
| Bulk add results page | `GET` | `/add-results` | Only when `capabilities.can_add_results` |

#### `POST /results/save`

```json
{
  "class_subject_id": 55,
  "exam_id": 10,
  "results": [
    { "student_id": 1040, "marks": 78, "grade": "B", "remark": "" }
  ]
}
```

Marks `0–100`. Ownership allows the main assigned teacher **or** a duo paper teacher. Gate the Add Results button with `capabilities.can_add_results` (and school result-entry rules returned by the exams list).

**Not in Teaching mobile today (website-only extras on the subjects page):** home packages, subject notes/past papers, optional-subject election. Learning portal notes/quizzes are under Main → Learning Portal.

---

## 4. Scheme of Work

Full hand-off for the mobile app: **`docs/TEACHER_SCHEME_OF_WORK_API.md`**.

Quick index: hub `GET /scheme-of-work` → create / use existing / view `GET /scheme-of-work/{id}` / manage `GET /scheme-of-work/{id}/manage` → update, remark, delete, PDF, Excel.

---

## 5. Lesson Plans

Full hand-off for the mobile app: **`docs/TEACHER_LESSON_PLANS_API.md`**.

Quick index: hub `GET /lesson-plans` → `GET /sessions/by-subject` → attendance-stats / check-exists / previous → create / update / send / PDF / by-filter.

---

## 6. Logbook

Full hand-off for the mobile app: **`docs/TEACHER_LOGBOOK_API.md`**.

Quick index: hub `GET /logbook` → entries `GET /logbook/{classSubjectID}` → add `POST /logbook/entry` → PDF `GET /logbook/{classSubjectID}/pdf`.

---

## 7. Calendar

| Action | Method | Path | Notes |
|---|---|---|---|
| Full year | `GET` | `/calendar` | optional `year`; holidays, events, stats, auto holidays |
| Month slice | `GET` | `/calendar/data` | `year`, `month` |

Teachers cannot create/edit holidays or events here (admin only). No teacher calendar PDF.

---

## 8. Department Logbooks (HOD)

Show only when `capabilities.is_hod` is true.

| Action | Method | Path | Notes |
|---|---|---|---|
| Bootstrap | `GET` | `/department-logbook` | departments, subjects, classes + flow |
| Streams of a class | `GET` | `/department-logbook/subclasses/{classID}` | |
| Entries | `GET` | `/department-logbook/data` | **required** `subjectID`, `subclassID` (`classID` is ignored by the server) |
| HOD comment | `POST` | `/logbook/sign` | `{ "logbookID": 1, "hod_comment": "..." }` — can SMS the teacher |
| PDF | `GET` | `/logbook/{classSubjectID}/pdf` | use `classSubjectID` from the data response |

Only the HOD of the department that owns the subject may sign. Signing triggers an SMS to the assigned teacher — do not automate this against live data.

---

## 9. Class (separate menu, related)

| Action | Method | Path | Doc |
|---|---|---|---|
| My class | `GET` | `/my-class` | — |
| My timetable | `GET` | `/timetable` | **`docs/TEACHER_TIMETABLE_API.md`** |

---

## Recommended app navigation

```text
Teaching
 ├─ Sessions  → GET /sessions
 │               Collect → students + POST /sessions/attendance
 │               Task    → POST /sessions/tasks
 │               Plan    → Lesson Plans flow
 ├─ Tasks     → GET /tasks
 ├─ Subjects  → GET /subjects → students / exams / results / attendance
 ├─ Scheme of Work → GET /scheme-of-work → create / manage / export
 ├─ Lesson Plans   → GET /lesson-plans → sessions → create / send / PDF
 ├─ Logbook        → GET /logbook → entries → POST /logbook/entry
 └─ Calendar       → GET /calendar

(HOD) Department Logbooks → GET /department-logbook → subclasses → data → sign
```

---

## What was hardened for mobile

- Scheme of work: update / PDF / Excel require creator or assigned teacher; reuse only into a subject you teach.
- Logbook: save only for subjects you teach; PDF for teacher or HOD; HOD sign checks department ownership and school.
- Subjects results APIs: duo paper teachers can load/save like the website; `for_view=1` on examinations.
- Session attendance read uses the website method (includes `can_edit`).
- Lesson plan check/get accept both `date` and `lesson_date`.
- Hub screens (`/tasks`, `/scheme-of-work`, `/lesson-plans`, `/logbook`, `/department-logbook`, `/calendar`) return mobile-shaped JSON instead of raw Blade props.
- HOD stream picker: `GET /department-logbook/subclasses/{classID}`.

---

## Verified

List hubs and core GETs were exercised with a teacher Bearer token inside a rolled-back transaction (no SMS). Write paths for attendance/task/lesson plan/scheme were previously exercised in the broader teacher API sweep; HOD `POST /logbook/sign` was not run against live data because it can SMS teachers.
