# Teacher Duty Book - Mobile API

Web page: `/teacher/duty-book` · Mobile base URL: `{HOST}/api/v1/teacher`

Everything the website Duty Book page does is available here: see my duty weeks, open a day, fill and save the daily duty report (draft), send it to the admin, view the admin's approval, download PDFs, and see parent permissions for the day (with approve/reject for the people allowed to act).

---

## 0. Rules for every call

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` (from `POST /api/v1/auth/login`) |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (for POST) |

- Only teacher tokens work. The school's academic year must be open.
- `401`/`403` -> not logged in or not allowed. `422` -> a rule below was broken; show `message` to the user.
- Dates are `YYYY-MM-DD`. Times are local (Africa/Dar_es_Salaam).
- No Duty Book menu rule exists on the website: every teacher sees the page. A teacher with no duty weeks just gets an empty `weeks` list.

---

## 1. Screens the app needs

1. **Duty weeks** - list of weeks assigned to me, expandable into 7 days, each day with a status and buttons.
2. **Daily report form** - class attendance grid (**editable** boy/girl numbers; totals + % auto) + 9 text fields. Buttons: Save draft, Send to admin, Sync from attendance.
3. **Report view (read-only)** - the same form once Sent/Approved, plus admin comments/signature if approved. Button: Download PDF.
4. **Parent permissions for the day** - list shown inside the report form (who is away with a parent's permission).
5. **Class-teacher permissions card** (only if `is_class_teacher`) - approved parent permissions for my class, last 30 days.
6. **Download duty roster PDF** for a date range.

---

## 2. Endpoints

### 2.1 `GET /duty-book` - the duty weeks screen

Query (optional): `from_date`, `to_date` (default: current month). Only weeks that overlap the range are returned.

```json
{
  "success": true,
  "data": {
    "period": { "from_date": "2026-11-01", "to_date": "2026-11-30", "title": "November 2026" },
    "school_type": "Secondary",
    "student_label": "student",
    "is_class_teacher": true,
    "is_on_duty_today": true,
    "current_duty": { "start_date": "2026-11-16", "end_date": "2026-11-22" },
    "weeks": [
      {
        "week_number": 1,
        "start_date": "2026-11-16",
        "end_date": "2026-11-22",
        "is_active_week": true,
        "teachers": [ { "teacher_id": 6132, "name": "RUTAGANYWA NYERERE", "is_me": true } ],
        "days": [
          {
            "date": "2026-11-16", "day": "Monday", "is_today": true, "is_future": false,
            "state": "waiting",
            "report": null,
            "can_view": false, "can_fill": true, "can_send": true, "can_download_pdf": false
          },
          {
            "date": "2026-11-15", "day": "Sunday", "is_today": false, "is_future": false,
            "state": "sent",
            "report": { "reportID": 185, "status": "Sent", "attendance_percentage": "90.00", "signed_by": null, "signed_at": null },
            "can_view": true, "can_fill": false, "can_send": false, "can_download_pdf": true
          }
        ]
      }
    ],
    "classes": [ { "classID": 59, "class_name": "FORM FIVE" } ],
    "class_teacher_permissions": [
      { "id": 12, "student_name": "A B", "parent_name": "C D", "reason_type": "medical",
        "start_date": "2026-11-10", "end_date": "2026-11-12", "days_count": 3, "status": "approved" }
    ]
  }
}
```

Day `state` -> badge to show:

| `state` | Meaning | Buttons |
|---|---|---|
| `upcoming` | Day is in the future | locked, show "Available on 20 Nov" |
| `waiting` | Past/today, nothing filled | Fill form, Send to admin |
| `draft` | Saved, not sent | View, Fill form (edit), Send to admin |
| `sent` | Sent, waiting for admin | View, Download PDF |
| `approved` | Admin approved and signed | View, Download PDF |

Use the booleans `can_view`, `can_fill`, `can_send`, `can_download_pdf` instead of re-deriving the rule. `student_label` ("pupil" for primary schools, "student" for secondary) is used in the form wording ("Sick pupils", "Pupils' cleanliness").

The "On duty this week" banner = `is_on_duty_today` + `current_duty`.

### 2.2 `GET /duty-book/report` - open a day (form data)

Query: `date=YYYY-MM-DD` **or** `report_id=<reportID>` (one is required, else `400`).
A teacher can only open their own reports (`404` otherwise).

```json
{
  "success": true,
  "report": null,
  "teacher_name": "RUTAGANYWA NYERERE",
  "classes": [ { "classID": 59, "class_name": "FORM FIVE" } ],
  "system_attendance": {
    "59": { "reg_b": 2, "reg_g": 5, "pres_b": 0, "pres_g": 0, "abs_b": 0, "abs_g": 0,
            "perm_b": 0, "perm_g": 0, "new_b": 0, "new_g": 0, "shifted_b": 0, "shifted_g": 0,
            "sick_b": 0, "sick_g": 0 }
  },
  "attendance_source": "system",
  "enrollment_status": "Active",
  "academic_year_label": "2026 / 2025/2026",
  "total_active_students": 282,
  "calculated_attendance_percentage": 0
}
```

- `report` is `null` when nothing was saved for that day -> **new report**: pre-fill the form from `system_attendance` and `calculated_attendance_percentage`. Show `teacher_name` as Teacher on Duty.
- Attendance class counts are **editable**. Prefill from Active enrollments / Sync; row totals + `%` stay auto-calculated. Observation text fields are also editable. Then Save Draft / Send.
- When a report exists, `report` holds the saved values:

```json
"report": {
  "reportID": 185, "schoolID": 8, "teacherID": 6132, "report_date": "2026-11-16",
  "status": "Draft",
  "attendance_data": { "59": { "reg_b": "2", "reg_g": "5", "pres_b": "0", "pres_g": "0", "shift_b": "0", "shift_g": "0",
                               "new_b": "0", "new_g": "0", "abs_b": "0", "abs_g": "0", "perm_b": "0", "perm_g": "0",
                               "sick_b": "0", "sick_g": "0" } },
  "attendance_percentage": "90.00",
  "school_environment": "...", "pupils_cleanliness": "...", "meals": "...", "timetable_status": "...",
  "outside_activities": "...", "special_events": "...", "english_speaking": "...",
  "sick_students_list": "...", "teacher_comments": "...",
  "teacher_name": "RUTAGANYWA NYERERE",
  "admin_comments": null, "signed_by": null, "signed_at": null, "signature_image": null
}
```

- Show admin feedback (comments, `signed_by`, `signed_at`, `signature_image` = base64 data URL) only when `signed_by` or `signature_image` is set (status Approved).
- `attendance_source`: `fingerprint` when the school uses fingerprint attendance that day, otherwise `system` (manual class attendance). Show it as a note ("Counts come from fingerprint attendance").
- `academic_year_label` / `enrollment_status`: show as the note "Counts are taken from Active enrollments in <label>".
- **Key name difference:** `system_attendance` uses `shifted_b/shifted_g`, the saved `attendance_data` uses `shift_b/shift_g`. Map `shifted_*` -> `shift_*` when saving.

### 2.3 `POST /duty-book/report` - save draft / send to admin

Body (JSON):

| Field | Type | Required | Notes |
|---|---|---|---|
| `report_date` | date | yes | Not in the future; must be inside one of my duty weeks (for a new report) |
| `action` | `draft` \| `send` | yes | `send` -> status `Sent` and an SMS goes to the school admin |
| `attendance_data` | object | no | Keyed by `classID` (see below). A JSON string is also accepted |
| `attendance_percentage` | number 0-100 | no | |
| `school_environment` | string | no | max 2000 |
| `pupils_cleanliness` | string | no | max 2000 |
| `meals` | string | no | max 5000 |
| `timetable_status` | string | no | max 2000 |
| `outside_activities` | string | no | max 2000 |
| `special_events` | string | no | max 2000 |
| `english_speaking` | string | no | max 2000 |
| `sick_students_list` | string | no | max 5000 |
| `teacher_comments` | string | no | max 5000 |

`attendance_data` (one entry per class, all numbers):

```json
{ "59": { "reg_b": 2, "reg_g": 5, "pres_b": 0, "pres_g": 0, "shift_b": 0, "shift_g": 0,
          "new_b": 0, "new_g": 0, "abs_b": 0, "abs_g": 0, "perm_b": 0, "perm_g": 0,
          "sick_b": 0, "sick_g": 0 } }
```

Behaviour:
- Fields you leave out are **not** overwritten on an existing report. So "quick send" = `{"report_date": "...", "action": "send"}` and sends what was already saved as a draft.
- A brand-new report with `action=send` and no other fields sends an empty report (the website allows it too). The app should open the form first.
- `attendance_percentage` is also recalculated from `attendance_data` when the report is read.

Success:

```json
{ "success": true, "message": "Report saved as Draft",
  "report": { "reportID": 185, "report_date": "2026-11-16", "status": "Draft", "attendance_percentage": 90 } }
```

Errors (`422` with `{ "success": false, "message": "..." }`):

| Message | Cause |
|---|---|
| `You cannot fill a duty report for a future date.` | `report_date` is after today |
| `You are not assigned on duty on this date.` | No duty week covers that date (new reports only) |
| `This report was already sent to the admin and is locked.` | Status is `Sent` |
| `This report is already approved and can no longer be changed.` | Status is `Approved` |
| `Validation error: ...` | Bad field (e.g. percentage above 100) |

So: **Draft can be edited and re-saved any number of times; Sent and Approved are read-only.**

### 2.4 `GET /duty-book/parent-permissions` - parents' permissions for a day

Query: `date` (required, else `400`).

```json
{ "success": true,
  "permissions": [
    { "id": 12, "student_name": "A B", "parent_name": "C D", "class_name": "FORM ONE A",
      "reason_type": "medical", "reason": "...", "start_date": "2026-11-15", "end_date": "2026-11-17",
      "days_count": 3, "status": "pending", "can_act": true }
  ] }
```

Show in the report form as a table (student, parent, class, reason + dates, status badge). `status`: `pending` / `approved` / `rejected`.
`can_act = true` -> show **Approve** and **Reject** buttons; otherwise "View only". `can_act` is only true for a pending permission that is at this teacher's step of the approval chain and who is not that student's own class teacher.

Approve / reject (both need a comment of at least 2 characters):

| Action | Call |
|---|---|
| Approve | `POST /permission-approvals/approve` |
| Reject | `POST /permission-approvals/reject` |

Body: `{ "permissionID": 12, "admin_response": "Approved, get well soon", "is_student_permission": 1 }`
(`approve` also accepts a multipart `admin_attachment` file: pdf/jpg/png, max 2 MB.)
These are the shared permission-approval endpoints; they can notify the parent/admin, so do not call them in automated tests against live data. After the call, reload `parent-permissions`.

### 2.5 PDF downloads

Both return `application/pdf` bytes. Send the Bearer token; save the response to a file and open it. Errors come back as JSON.

| Download | Call | Notes |
|---|---|---|
| Daily report | `GET /duty-book/report/pdf?date=YYYY-MM-DD` or `?report_id=185` | `404` if there is no report for the day or it is not mine |
| Duty roster | `GET /duty-book/pdf?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD` | Only my own duty weeks. Defaults to the current month |

Suggested file names: `Daily_Duty_Report_2026-11-16.pdf`, `Teacher_Duty_Roster_2026-11-16_to_2026-11-22.pdf`.

---

## 3. App flow

```text
Open Duty Book
  GET /duty-book                              -> weeks, banner, class-teacher card
Tap a week -> days list
Tap a day (Fill / View)
  GET /duty-book/report?date=D                -> form data (new: prefilled from system_attendance)
  GET /duty-book/parent-permissions?date=D    -> permissions table
  [Sync from attendance] -> call GET /duty-book/report?date=D again and refill the grid
  [Save draft]  POST /duty-book/report {action:"draft", ...}
  [Send]        POST /duty-book/report {action:"send", ...}   (confirm first - it cannot be undone)
View an approved/sent day
  GET /duty-book/report?date=D  (read-only) + [Download PDF] GET /duty-book/report/pdf?date=D
Roster
  [Download roster] GET /duty-book/pdf?from_date=..&to_date=..
```

Form details copied from the website:
- The class attendance grid has 7 column groups, each Boys / Girls / Total: Registered, Present, Shifted, New comers, Absent, Permission, Sick. **Boy/Girl cells are editable** (prefilled from Active enrollments / Sync). Row Total cells and Attendance % stay read-only and are calculated on the phone: `% = total present / total registered × 100`. Sync = call `GET /duty-book/report?date=D` again and replace the grid (warn if the user already typed numbers).
- Text fields, in order: 1 Attendance % (read-only), 2 School environment, 3 Cleanliness (pupils/students), 4 Meals, 5 Time table, 6 Outside activities, 7 Special events, 8 English speaking, 9 List of sick pupils/students, 10 Teacher's comments.
- Header: show `teacher_name` (top-level on the response, or `report.teacher_name` when a draft exists). Note under the grid: prefills from Active enrollments (`academic_year_label`); user may edit or Sync.

---

## 4. What is NOT in the teacher app

The admin side of the Duty Book (create/delete the weekly roster, approve/sign a report) is not part of the teacher API. The teacher only sees their own weeks and reports, and an `Approved` report is set by the school admin on the website.

## 5. Verified

Tested against the live data inside a rolled-back transaction with SMS sending stubbed: weeks list (past, current and future weeks), open new/existing day, 404 for another teacher's report, save draft with class attendance, send, edit-after-send blocked, future/off-duty dates blocked, validation errors, parent permissions (empty and missing date), roster PDF, daily report PDF, 404 for missing report. Approving/rejecting a parent permission is the existing, shared endpoint and was not run (it sends SMS to parents).
