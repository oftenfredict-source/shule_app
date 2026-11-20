# Teacher My Sessions - Mobile API

Web page: `/teacher/my-sessions`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Teaching → Sessions

Full hand-off for everything on the website Sessions page: week navigation, collect/update attendance, assign task, PDF, open lesson plan.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST bodies) |

- Teacher tokens only. Academic year must be open.
- Dates: `Y-m-d`. Attendance status: `Present` | `Absent` | `Late` | `Excused`.
- Website shows Collect / Assign / Lesson plan buttons even when the session is not “live”. Prefer `can_collect_attendance` / `can_assign_task` / `can_open_lesson_plan` for mobile UX.
- Do not render raw path strings as UI cards.

---

## 1. Screens (match the website)

1. **Week toolbar** — Prev / This / Next week + day picker (Mon–Fri with date, session count, holiday/today).
2. **Day session list** — Time | Subject/Class (+ task badge) | Status (Active / Done / Upcoming) | Actions.
3. **Collect attendance** modal — tabs Collect | Update.
4. **Assign task** modal.
5. **Download PDF** for the selected week.
6. **Open lesson plan** — gate with attendance-stats, then Lesson Plans flow.

---

## 2. Flow

```text
GET /sessions?week=0
  → days[].sessions[] with action flags

Prev / This / Next:
  GET /sessions?week=-1 | 0 | 1

Collect attendance:
  GET  /sessions/students?session_timetableID=&attendance_date=
  POST /sessions/attendance   { is_update: false, attendance: [...] }

Update attendance:
  GET  /sessions/attendance?session_timetableID=&attendance_date=
  POST /sessions/attendance   { is_update: true, attendance: [one or many] }

Assign task:
  POST /sessions/tasks

PDF:
  GET /sessions/pdf?week=0   → application/pdf

Open lesson plan:
  GET /sessions/attendance-stats?session_timetableID=&date=
  then Lesson Plans APIs (docs/TEACHER_LESSON_PLANS_API.md)
       with session_timetableID + date + auto-open create/edit
```

---

## 3. Endpoints

### 3.1 Week list — `GET /sessions`

Query: `week` (int, default `0`). `-1` = previous week, `1` = next, etc. Weeks start Monday.

```json
{
  "success": true,
  "data": {
    "title": "My Sessions",
    "week_offset": 0,
    "week_start": "2026-11-16",
    "week_end": "2026-11-22",
    "holiday_dates": ["2026-11-17"],
    "days": [
      {
        "day": "Monday",
        "date": "2026-11-16",
        "is_holiday": false,
        "is_weekend": false,
        "session_count": 2,
        "sessions": [
          {
            "session_timetableID": 3006,
            "day": "Monday",
            "date": "2026-11-16",
            "start_time": "11:40:00",
            "end_time": "13:00:00",
            "start_time_formatted": "11:40 AM",
            "end_time_formatted": "1:00 PM",
            "subject_name": "BIOLOGY",
            "class_name": "FORM FOUR",
            "subclass_name": "A",
            "class_label": "FORM FOUR - A",
            "is_prepo": false,
            "is_session_time": true,
            "is_past": false,
            "can_interact": true,
            "status": "Active",
            "can_collect_attendance": true,
            "can_assign_task": true,
            "can_open_lesson_plan": true,
            "can_view_task": false,
            "task": null,
            "has_approved_task": false
          }
        ]
      }
    ]
  }
}
```

| Flag / field | Meaning |
|---|---|
| `status` | `Active` (in clock window) · `Done` (past end) · `Upcoming` |
| `can_collect_attendance` | Show Collect (live session, not holiday/weekend) |
| `can_assign_task` | Show Assign when no task yet for that date |
| `can_open_lesson_plan` | Allow deep-link to lesson plan (blocked on holiday/weekend) |
| `task` | Existing task for that session+date (badge on website) |
| `is_holiday` on day | Show Holiday badge; block interactions |

If no timetable definition: `success: false` with message (404).

---

### 3.2 PDF — `GET /sessions/pdf?week=0`

Returns PDF binary (`application/pdf`).  
Suggested filename: `My_Sessions_{Teacher}_{Ymd}.pdf`.

---

### 3.3 Students for Collect — `GET /sessions/students`

Query: `session_timetableID`, `attendance_date`.

```json
{
  "success": true,
  "students": [
    {
      "studentID": 1040,
      "name": "Jane Doe",
      "status": "Present",
      "remark": null,
      "enrollmentID": 55,
      "enrollment_status": "Active",
      "can_mark": true
    }
  ],
  "attendance_exists": false,
  "can_collect": true,
  "active_enrollment_count": 30
}
```

If `can_collect` is false or `attendance_exists` is true, use the Update tab instead.  
Possible `error_code`: `no_academic_year`, `no_active_enrollments`.

---

### 3.4 Saved attendance (Update tab) — `GET /sessions/attendance`

Query: `session_timetableID`, `attendance_date`.

```json
{
  "success": true,
  "data": [
    {
      "attendanceID": 88,
      "studentID": 1040,
      "student_name": "Jane Doe",
      "status": "Present",
      "remark": null,
      "enrollment_status": "Active",
      "can_edit": true
    }
  ]
}
```

Only edit rows with `can_edit: true`.

---

### 3.5 Save / update attendance — `POST /sessions/attendance`

**Collect (new sheet):**

```json
{
  "session_timetableID": 3006,
  "attendance_date": "2026-11-17",
  "is_update": false,
  "attendance": [
    { "studentID": 1040, "status": "Present", "remark": "" },
    { "studentID": 1041, "status": "Absent", "remark": "Sick" }
  ]
}
```

**Update (one or more students):** same body with `"is_update": true`.

Success: `{ "success": true, "message": "Attendance collected successfully" }`  
Duplicate collect without `is_update` → 422.

---

### 3.6 Assign task — `POST /sessions/tasks`

```json
{
  "session_timetableID": 3006,
  "task_date": "2026-11-17",
  "topic": "Classification",
  "subtopic": "Phylum chordata",
  "task_description": "At least 10 characters of description"
}
```

| Field | Required |
|---|---|
| `session_timetableID` | yes |
| `task_date` | yes |
| `topic` | yes |
| `task_description` | yes (min 10) |
| `subtopic` | no |

One task per session+date. Success includes `task` object.  
Edit rejected tasks: `POST /sessions/tasks/{taskID}` (Tasks screen) — same fields; only when status is `rejected`.

---

### 3.7 Lesson plan gate — `GET /sessions/attendance-stats`

Query: `session_timetableID`, `date` (or `lesson_date`).

Used before opening the Lesson Plan form (same as website). On success, open Lesson Plans with that session + date.  
Blocked responses may include `date_status`: `weekend` | `holiday` | `no_session`.

Full lesson-plan create/edit: **`docs/TEACHER_LESSON_PLANS_API.md`**.

---

## 4. Helpers (not on My Sessions UI, but related)

| Endpoint | Used by |
|---|---|
| `GET /sessions/by-subject?subjectID=` | Lesson Plans subject → periods |
| `GET /sessions/all-for-year?session_timetableID=&year=` | Lesson Plans calendar |
| `GET /sessions/terms-for-year`, `/sessions/exams-for-year-term` | Other teaching filters |
| `GET /sessions/collected-attendance` | Class management collected sheets (not this page) |

---

## 5. Website UI → API map

| Website | API |
|---|---|
| Week label + Prev / This / Next | `GET /sessions?week=` |
| Day dropdown | Client filter on `days[]` |
| Holiday / Today badges | `is_holiday`, compare `date` to today |
| Status Active/Done/Upcoming | `status` |
| Task badge | `task.status` |
| Collect Attendance | §3.3 + §3.5 (`is_update: false`) |
| Update Attendance tab | §3.4 + §3.5 (`is_update: true`) |
| Assign Task | §3.6 |
| Download PDF | §3.2 |
| Lesson Plan action | §3.7 → Lesson Plans APIs |

---

## 6. Related docs

- Timetable (read-only grid): `docs/TEACHER_TIMETABLE_API.md`
- Lesson Plans: `docs/TEACHER_LESSON_PLANS_API.md`
- Tasks list / resubmit: `docs/TEACHER_TEACHING_API.md` §2
- Auth: `docs/TEACHER_MOBILE_API.md`
