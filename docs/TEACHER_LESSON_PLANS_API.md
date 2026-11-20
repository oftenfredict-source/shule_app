# Teacher Lesson Plans - Mobile API

Web page: `/teacher/lesson-plans`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Teaching → Lesson Plans

This document is the full hand-off for the mobile app. It covers every screen and action the website supports for Lesson Plans.

---

## 0. Rules for every call

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` (from `POST /api/v1/auth/login`) |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (for POST bodies) |

- Teacher tokens only. The school academic year must be open.
- `401` → log the user out. `403` → not allowed. `404` → not found. `409` → already exists / already sent. `400`/`422` → show `message` (or `error`).
- Dates are `Y-m-d` (e.g. `2026-11-17`). Prefer query/body key `lesson_date` on create; for check/get/stats the server accepts **both** `date` and `lesson_date`.
- A teacher can only create/manage plans for **their own** timetable sessions (`session_timetableID` where they are the session teacher).
- There is **no server-side draft**. The website keeps drafts in browser `localStorage`. The app should keep its own offline draft if needed.
- Do **not** render raw path strings or an `actions` object as UI cards. Use the flags and fields below; paths live only in this doc.

---

## 1. Website tabs → which API (this is what you asked)

On the website, after you pick a **subject** then a **session**, a modal opens with three tabs.  
There are **no** separate URLs like `/create-lesson-plan` or `/manage-lesson-plan`.  
Those tabs reuse the same endpoints below.

| Website UI | What it does | Mobile API |
|---|---|---|
| **Create Lesson Plan** → Create new | Blank form for a chosen date → save | Prefill: `GET /sessions/attendance-stats` (+ optional `GET /lesson-plans/previous`) → **`POST /lesson-plans`** |
| **Create Lesson Plan** → **Use existing** | List old plans for this session → pick one → choose **new date** → save as **new** plan | List: **`GET /lesson-plans/by-filter?filter_type=year&year=`** → load: **`GET /lesson-plans/by-id?lesson_planID=`** → save new: **`POST /lesson-plans`** with new `lesson_date` + copied fields |
| **Manage Lesson Plan** → Filter (Filter Type / Select Date / From–To / Year) | Find plans to **edit** | Same list API: **`GET /lesson-plans/by-filter`** (see §1.1) → open: **`GET /lesson-plans/by-id`** → save: **`POST /lesson-plans/{id}/update`** → send: **`POST /lesson-plans/send-to-admin`** → PDF: **`GET /lesson-plans/{id}/pdf`** |
| **View Lesson Plan** → Filter (Filter Type / From Date / To Date / Year) | Find plans to **read / PDF / bulk PDF** | Same list API: **`GET /lesson-plans/by-filter`** → open: **`GET /lesson-plans/by-id`** → PDF / **`POST /lesson-plans/bulk-pdf`** |

Hub before the modal:

| Website | API |
|---|---|
| Subject list | `GET /lesson-plans` |
| Sessions after subject | `GET /sessions/by-subject?subjectID=` |
| Session calendar dates | `GET /sessions/all-for-year?session_timetableID=&year=` |

### 1.1 Filter Lesson Plans (Manage + View) — exact calls

Both Manage and View filters call **one** endpoint:

`GET /api/v1/teacher/lesson-plans/by-filter`

Always send `session_timetableID` (the period you opened).

| Website Filter Type | Query to send |
|---|---|
| **Select Date** (Manage only — “single date”) | `filter_type=date_range&from_date=YYYY-MM-DD&to_date=YYYY-MM-DD` (same date twice — website does this) |
| **Date range** / From Date + To Date | `filter_type=date_range&from_date=YYYY-MM-DD&to_date=YYYY-MM-DD` |
| **By year** | `filter_type=year&year=2026` |

Examples:

```http
GET /api/v1/teacher/lesson-plans/by-filter?session_timetableID=3006&filter_type=date_range&from_date=2026-09-01&to_date=2026-09-30
GET /api/v1/teacher/lesson-plans/by-filter?session_timetableID=3006&filter_type=year&year=2026
```

Response list is under `data.lesson_plans[]` (`lesson_planID`, `lesson_date`, `subject`, `class_name`, `sent_to_admin`, `can_send`, …).

Then:

- **Manage one** → `GET /lesson-plans/by-id?lesson_planID=55` → edit → `POST /lesson-plans/55/update`
- **View one** → `GET /lesson-plans/by-id?lesson_planID=55` (read-only UI) → optional PDF
- **Use existing** → same list → `GET /lesson-plans/by-id` → copy fields into create form → `POST /lesson-plans` with a **new** `lesson_date`

---

## 2. Screens the app needs

1. **Hub** — subjects (`GET /lesson-plans`).
2. **Sessions** — periods for subject (`GET /sessions/by-subject`).
3. **Create tab** — Create new + Use existing (APIs in §1).
4. **Manage tab** — filter list → edit / send / PDF.
5. **View tab** — filter list → read-only / PDF / bulk PDF.

---

## 3. How the flow works

```text
Open Lesson Plans
  GET /lesson-plans
      └─ pick subjectID

  GET /sessions/by-subject?subjectID={id}
      └─ pick session_timetableID  → open Create / Manage / View

── Create tab ──────────────────────────────────────────────
Create new:
  GET /sessions/attendance-stats?session_timetableID=&date=
  GET /lesson-plans/check-exists?session_timetableID=&date=
  GET /lesson-plans/previous?session_timetableID=&before_date=   (optional)
  POST /lesson-plans

Use existing:
  GET /lesson-plans/by-filter?session_timetableID=&filter_type=year&year=
  GET /lesson-plans/by-id?lesson_planID=
  (user picks NEW lesson_date)
  POST /lesson-plans   ← creates a new plan; does not update the old one

── Manage tab ──────────────────────────────────────────────
  GET /lesson-plans/by-filter?...   (year | date_range | single date as date_range)
  GET /lesson-plans/by-id?lesson_planID=
  POST /lesson-plans/{lessonPlanID}/update
  POST /lesson-plans/send-to-admin
  GET /lesson-plans/{lessonPlanID}/pdf

── View tab ────────────────────────────────────────────────
  GET /lesson-plans/by-filter?...   (year | date_range From–To)
  GET /lesson-plans/by-id?lesson_planID=
  GET /lesson-plans/{lessonPlanID}/pdf
  POST /lesson-plans/bulk-pdf  { "lesson_plan_ids": [1,2] }
```

---

## 4. Endpoints

All paths below are under `/api/v1/teacher`.

### 4.1 Hub — `GET /lesson-plans`

```json
{
  "success": true,
  "data": {
    "title": "Lesson Plans",
    "subjects": [
      {
        "subjectID": 120,
        "subject_name": "History"
      }
    ],
    "school_type": "SECONDARY SCHOOL",
    "current_year": 2026,
    "message": null
  }
}
```

If there is no timetable definition, `subjects` may be empty and `message` explains why.

---

### 4.2 Sessions for a subject — `GET /sessions/by-subject`

Query: `subjectID` (required).

```json
{
  "success": true,
  "sessions": [
    {
      "session_timetableID": 8821,
      "day": "Monday",
      "start_time": "08:00:00",
      "end_time": "08:40:00",
      "subject_name": "History",
      "class_name": "FORM FOUR",
      "subclass_name": "A",
      "is_prepo": false
    }
  ],
  "count": 1
}
```

If none: `success: false`, `sessions: []`, `error` message.

---

### 4.3 Dates for a session year — `GET /sessions/all-for-year`

Query: `session_timetableID` (required), `year` (optional, default current calendar year).

Returns every matching weekday in the year that is **not** a weekend or school holiday/non-working event, plus whether a plan already exists.

```json
{
  "success": true,
  "data": {
    "session": {
      "session_timetableID": 8821,
      "day": "Monday",
      "start_time": "08:00:00",
      "end_time": "08:40:00"
    },
    "dates": [
      {
        "date": "2026-01-05",
        "formatted_date": "05/01/2026",
        "day_name": "Monday",
        "has_lesson_plan": true,
        "lesson_plan_id": 55
      }
    ],
    "total_sessions": 40,
    "year": 2026,
    "period": "year"
  }
}
```

---

### 4.4 Attendance stats (form header) — `GET /sessions/attendance-stats`

Query: `session_timetableID`, `date` **or** `lesson_date`.

Valid day →:

```json
{
  "success": true,
  "data": {
    "registered_girls": 18,
    "registered_boys": 22,
    "registered_total": 40,
    "present_girls": 16,
    "present_boys": 20,
    "present_total": 36,
    "subject": "History",
    "class_name": "FORM FOUR",
    "teacher_name": "Jane Doe",
    "start_time": "08:00:00",
    "end_time": "08:40:00"
  }
}
```

Blocked day → `success: false` with `error` / `message` and often `date_status`:

| `date_status` | Meaning |
|---|---|
| `weekend` | Saturday / Sunday |
| `holiday` | School holiday or non-working event |
| `no_session` | Date weekday ≠ session `day` |

Present counts treat attendance status `present`, `late`, and `excused` as present (not `absent`). Registered students come from **Active** enrollments for the selected academic year.

---

### 4.5 Check exists — `GET /lesson-plans/check-exists`

Query: `session_timetableID`, `date` **or** `lesson_date`.

```json
{
  "success": true,
  "exists": true,
  "lesson_plan_id": 55,
  "lesson_planID": 55
}
```

Use either id field; both are set when a plan exists.

---

### 4.6 Get one by session + date — `GET /lesson-plans/get`

Query: `session_timetableID`, `date` **or** `lesson_date`.

```json
{
  "success": true,
  "data": {
    "lesson_planID": 55,
    "session_timetableID": 8821,
    "lesson_date": "2026-11-17",
    "lesson_time_start": "08:00:00",
    "lesson_time_end": "08:40:00",
    "subject": "History",
    "class_name": "FORM FOUR",
    "year": 2026,
    "teacher_name": "Jane Doe",
    "status": "draft",
    "sent_to_admin": false,
    "sent_at": null,
    "attendance": {
      "registered_girls": 18,
      "registered_boys": 22,
      "registered_total": 40,
      "present_girls": 16,
      "present_boys": 20,
      "present_total": 36
    },
    "main_competence": "...",
    "specific_competence": "...",
    "main_activity": "...",
    "specific_activity": "...",
    "teaching_learning_resources": "...",
    "references": "...",
    "lesson_stages": [
      {
        "stage": "Introduction",
        "time": "5 minutes",
        "teaching_activities": "...",
        "learning_activities": "...",
        "assessment_criteria": "..."
      }
    ],
    "remarks": "...",
    "reflection": "...",
    "evaluation": "...",
    "teacher_signature": "data:image/png;base64,...",
    "supervisor_signature": null,
    "can_send": true
  }
}
```

`404` if none. `can_send` is false when already sent.

---

### 4.7 Get one by id — `GET /lesson-plans/by-id`

Query: `lesson_planID`. Same `data` shape as §4.6. `404` if not found / not yours.

---

### 4.8 Previous plan (prefill) — `GET /lesson-plans/previous`

Query: `session_timetableID` (required), `before_date` (optional `Y-m-d`).

Looks up the latest earlier plan for the **same subject** (same `class_subjectID` sessions) for this teacher.

```json
{
  "success": true,
  "found": true,
  "data": {
    "lesson_planID": 50,
    "lesson_date": "2026-11-10",
    "main_competence": "...",
    "specific_competence": "...",
    "main_activity": "...",
    "specific_activity": "...",
    "teaching_learning_resources": "...",
    "references": "...",
    "lesson_stages": [ ... ],
    "remarks": "...",
    "reflection": "...",
    "evaluation": "..."
  }
}
```

If none: `"found": false`, `"data": null`.

---

### 4.9 List by filter — `GET /lesson-plans/by-filter`

Query:

| Param | Notes |
|---|---|
| `session_timetableID` | required |
| `filter_type` | `year` (default) or `date_range` |
| `year` | when `filter_type=year` (default = current year) |
| `from_date`, `to_date` | required when `filter_type=date_range` |

```json
{
  "success": true,
  "data": {
    "session_timetableID": 8821,
    "filter_type": "year",
    "lesson_plans": [
      {
        "lesson_planID": 55,
        "session_timetableID": 8821,
        "lesson_date": "2026-11-17",
        "lesson_time_start": "08:00:00",
        "lesson_time_end": "08:40:00",
        "subject": "History",
        "class_name": "FORM FOUR",
        "main_competence": "...",
        "status": "draft",
        "sent_to_admin": false,
        "sent_at": null,
        "can_send": true
      }
    ]
  }
}
```

---

### 4.10 Create — `POST /lesson-plans`

```json
{
  "session_timetableID": 8821,
  "lesson_date": "2026-11-17",
  "main_competence": "...",
  "specific_competence": "...",
  "main_activity": "...",
  "specific_activity": "...",
  "teaching_learning_resources": "...",
  "references": "...",
  "lesson_stages": [
    {
      "stage": "Introduction",
      "time": "5 minutes",
      "teaching_activities": "...",
      "learning_activities": "...",
      "assessment_criteria": "..."
    },
    {
      "stage": "Competence development",
      "time": "15 minutes",
      "teaching_activities": "...",
      "learning_activities": "...",
      "assessment_criteria": "..."
    },
    {
      "stage": "Design",
      "time": "15 minutes",
      "teaching_activities": "...",
      "learning_activities": "...",
      "assessment_criteria": "..."
    },
    {
      "stage": "Realization",
      "time": "5 minutes",
      "teaching_activities": "...",
      "learning_activities": "...",
      "assessment_criteria": "..."
    }
  ],
  "remarks": "...",
  "reflection": "...",
  "evaluation": "...",
  "teacher_signature": "data:image/png;base64,...",
  "supervisor_signature": ""
}
```

**Required:** `session_timetableID`, `lesson_date`.  
All other fields optional. Signatures are **base64 data URLs** (or empty string), not file uploads.

Website default stage names (use these labels so PDF matches):

1. Introduction  
2. Competence development  
3. Design  
4. Realization  

Attendance registered/present counts are computed on the server at create (same logic as attendance-stats). Status is set to `draft`.

Success:

```json
{
  "success": true,
  "message": "Lesson plan created successfully",
  "lesson_planID": 55
}
```

| Status | When |
|---|---|
| `422` | Validation / no active enrollments |
| `403` | Session not yours |
| `409` | Plan already exists for that session + date (response may include existing `lesson_planID`) |

---

### 4.11 Update — `POST /lesson-plans/{lessonPlanID}/update`

Same optional body fields as create (**not** `session_timetableID` / `lesson_date` — date is fixed). Attendance counts are refreshed from current records on save.

```json
{
  "success": true,
  "message": "Lesson plan updated successfully"
}
```

`404` if not found / not yours. `422` on validation / enrollment gate.

---

### 4.12 Send to admin — `POST /lesson-plans/send-to-admin`

```json
{ "lesson_planID": 55 }
```

Sets `sent_to_admin = true` and `sent_at`. **No SMS.** Website still allows edit after send; only send is blocked once already sent.

| Status | When |
|---|---|
| `404` | Not found |
| `409` | Already sent |

---

### 4.13 Single PDF — `GET /lesson-plans/{lessonPlanID}/pdf`

Returns PDF binary (`application/pdf`).  
`404` JSON if not found (mobile). Suggested filename: `Lesson_Plan_{id}_{YYYY-MM-DD}.pdf`.

---

### 4.14 Bulk PDF — `POST /lesson-plans/bulk-pdf`

```json
{ "lesson_plan_ids": [55, 56] }
```

Only plans owned by the teacher are included. Returns PDF binary.  
`400` if empty list, `404` if none found.

---

## 5. Form field map (website → API)

| Website label | JSON field |
|---|---|
| Main competence | `main_competence` |
| Specific competence | `specific_competence` |
| Main activity | `main_activity` |
| Specific activity | `specific_activity` |
| Teaching & learning resources | `teaching_learning_resources` |
| References | `references` |
| Stage / Time / Teaching / Learning / Assessment | `lesson_stages[]` |
| Remarks | `remarks` |
| Reflection | `reflection` |
| Evaluation | `evaluation` |
| Teacher signature pad | `teacher_signature` (data URL) |
| Supervisor signature | `supervisor_signature` (data URL or stored URL) |

Header attendance numbers are **read-only** in the UI; they come from attendance-stats / are re-snapshotted on create/update.

---

## 6. Suggested app navigation

```text
Lesson Plans hub (subjects)
  → Subject sessions
      → Pick date (calendar or date picker + attendance-stats)
          → if exists: Edit screen
          → else: Create screen (optional previous prefill)
      → Filter list (year / range)
          → open by id
          → send / PDF / multi-select bulk PDF
```

---

## 7. Related docs

- Teaching module overview: `docs/TEACHER_TEACHING_API.md`
- Auth / token: `docs/TEACHER_MOBILE_API.md`
- Scheme of Work (sibling Teaching feature): `docs/TEACHER_SCHEME_OF_WORK_API.md`
