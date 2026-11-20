# ShuleXpert Parent Mobile API

Give this document to the mobile app developer. It covers login and every parent-portal feature.

The parent website menu and the API match:

| App screen | Endpoint |
|---|---|
| Login | `POST /api/v1/auth/login` |
| Profile / session | `GET /api/v1/auth/me` |
| Change password | `POST /api/v1/auth/password` |
| Logout | `POST /api/v1/auth/logout` |
| Dashboard | `GET /api/v1/parent/dashboard` |
| Services (card menu) | `GET /api/v1/parent/services` |
| My children | `GET /api/v1/parent/children` |
| Exam results | `GET /api/v1/parent/results` |
| Term report | `GET /api/v1/parent/results?type=report` |
| Class attendance | `GET /api/v1/parent/attendance/overview` |
| One child's attendance calendar | `GET /api/v1/parent/attendance/students/{studentID}` |
| Fingerprint attendance | `GET /api/v1/parent/fingerprint-attendance` |
| Payments | `GET /api/v1/parent/payments` |
| Fees summary | `GET /api/v1/parent/fees-summary` |
| Request control number | `POST /api/v1/parent/students/{studentID}/control-number` |
| Subjects (elect optional subjects) | `GET /api/v1/parent/students/{studentID}/subjects` |
| Notes | `GET /api/v1/parent/learning?kind=note` |
| Past papers | `GET /api/v1/parent/learning?kind=past_paper` |
| Home packages | `GET /api/v1/parent/home-packages` |
| Notifications | `GET /api/v1/parent/notifications` |
| Permission requests | `GET /api/v1/parent/permissions` |

## Base URL

All paths below are relative to the Laravel app.

```
{APP_URL}/api/v1
```

Examples:

- Local XAMPP: `http://localhost/shule_app/api/v1`
- School server: `https://your-school-domain/api/v1`

`APP_URL` in the server `.env` must be the same public address the phone uses. File links in the JSON are built from that value.

Send this header on every call:

```
Accept: application/json
```

Language for labels (attendance, notifications). Default is Swahili.

```
Accept-Language: sw
Accept-Language: en
```

## Authentication

Login returns a bearer token. Save it on the device. Send it on every other request:

```
Authorization: Bearer {token}
```

The token lasts 90 days. A parent may stay logged in on up to 8 devices. Login again on a 9th device drops the oldest token.

Do not send `parentID` or `schoolID` from the app. The server reads them from the token. A parent can only see their own children.

### Login

`POST /api/v1/auth/login`

The parent username is the phone number they use on the website.

```json
{
  "username": "2557XXXXXXXX",
  "password": "secret",
  "device_name": "Parent Android"
}
```

`device_name` is optional.

Success `200`:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 15,
      "name": "2557XXXXXXXX",
      "email": null,
      "user_type": "parent"
    },
    "parentID": 4,
    "schoolID": 3,
    "parent": {
      "parentID": 4,
      "phone": "2557XXXXXXXX",
      "email": null,
      "first_name": "Amina",
      "middle_name": null,
      "last_name": "Juma",
      "full_name": "Amina Juma",
      "photo": null
    },
    "token": "plain-text-token-show-once",
    "token_type": "Bearer",
    "expires_at": "2027-02-10T12:00:00+03:00"
  }
}
```

Store `data.token`. Open the parent app only when `data.user.user_type` is `parent`.

Errors:

| HTTP | When |
|---|---|
| 422 | Username or password missing |
| 401 | Wrong username or password |
| 404 | User exists but the parent record does not |
| 429 | More than 5 failed attempts from the same IP. Wait and retry. |

### Current parent

`GET /api/v1/auth/me`

Same data is available at `GET /api/v1/parent/profile`.

Returns the parent, the school (`school_name`, `school_type`, `logo`), and `children`.

### Change password

`POST /api/v1/auth/password`

```json
{
  "current_password": "old-secret",
  "password": "new-secret",
  "password_confirmation": "new-secret"
}
```

Password minimum is 6 characters. Other devices are logged out. The current token stays valid.

### Logout

`POST /api/v1/auth/logout`

Header: `Authorization: Bearer {token}`

Then delete the token from the device.

### Auth errors on protected routes

```json
{ "success": false, "message": "Authorization token is required. Send Authorization: Bearer {token}." }
```

HTTP 401. Login again.

HTTP 403 means the token belongs to a teacher or admin, not a parent.

## Response shape

Success:

```json
{ "success": true, "message": "...", "data": {} }
```

Some existing result, payment, and fee payloads put lists directly in `data`. Read the keys described under each endpoint.

Failure:

```json
{ "success": false, "message": "What went wrong" }
```

Validation errors also include `errors`.

## Children

`GET /api/v1/parent/children`

Each child:

```json
{
  "studentID": 12,
  "full_name": "John Adam Peter",
  "first_name": "John",
  "middle_name": "Adam",
  "last_name": "Peter",
  "admission_number": "ST012",
  "gender": "Male",
  "status": "Active",
  "photo": "http://server/userImages/photo.jpg",
  "class": "Form II",
  "subclass": "A",
  "class_label": "Form II A"
}
```

Use `studentID` in later calls. Transferred children are omitted.

## Dashboard

`GET /api/v1/parent/dashboard`

`data` contains:

- `parent`, `school`
- `statistics` (`total_students`, `active_students`, `male_students`, `female_students`, `recent_results_count`)
- `students` with this month's attendance percentage
- `result_cards[]` — **use these for the Results card / list (not raw `recent_results`)**
- `recent_results` — subject-level rows (legacy); prefer `result_cards`
- `alerts[]` — **all alerts** (published results, attendance, upcoming exams, bell notifications)
- `services[]` — teacher-style service cards (same as `GET /services`)
- `recent_attendance`, `active_book_borrows`, `upcoming_exams`

### Why Results showed “5” but tap opened nothing

`statistics.recent_results_count` used to count **subject rows**. Tapping then called bare `GET /results` without `student` / `exam` / `term` / `year` → empty sheet.

**Correct app behaviour:**

1. Show `result_cards[]` (grouped by exam + child).
2. On tap, open:

```http
GET /api/v1/parent/results?student=2&exam=86&type=exam&term=second_term&year=2025&academic_yearID=11
```

Use the card’s `open` object or `open_path`.

Example `result_cards[]` item:

```json
{
  "title": "Annual Exam",
  "subtitle": "LEILAT SHEMAHONGE",
  "subject_count": 5,
  "screen": "results",
  "open": {
    "student": 2,
    "exam": 86,
    "type": "exam",
    "term": "second_term",
    "year": 2025,
    "academic_yearID": 11
  },
  "open_path": "/results?student=2&exam=86&type=exam&term=second_term&year=2025&academic_yearID=11"
}
```

### Services cards (like teacher)

`GET /api/v1/parent/services` (also on dashboard as `data.services`)

Render as **cards** (title, subtitle, icon), grouped by `sections` (Academics / Finance / Account). On tap navigate to `path` under `/api/v1/parent`.

### Alerts

- Home: show **all** `data.alerts[]` (not only unread).
- Bell screen: `GET /api/v1/parent/notifications?limit=100` — returns every stored notification (results published, home packages, notes, past papers).
- Tap uses `screen` + `open` when present.

## Academic year, exams, results, term report

### Academic years

`GET /api/v1/parent/academic-years`

Optional query (recommended for results / report wizard):

| Query | Required | Description |
|---|---|---|
| `student` or `studentID` | No | Child ID. On dual O/A schools this returns **only that child's level years**, same as the website after picking a student. |

`data.options[]` is the same year cards the website shows:

| Field | Meaning |
|---|---|
| `academic_yearID` | Send this when loading exams / results |
| `label` | Text shown on the card (e.g. `2026` or `2025/2026`) |
| `short_label` | Year name without O/A prefix |
| `calendar_year` | Primary calendar value used by the website picker |
| `is_selected` | Currently selected in the school header |
| `is_a_level` | `true` only for A-Level tracks on dual schools |

Do **not** invent extra years from slash labels. One option = one website card. If the website shows 2 years for a child, this endpoint returns 2 options when you pass that child.

`data.selected_ids` is what the school office has selected in the header.

### Published exams

`GET /api/v1/parent/exams?term=first_term&academic_yearID=5`

| Query | Required | Values |
|---|---|---|
| `term` | Yes | `first_term`, `second_term`, `third_term` |
| `academic_yearID` | No | From academic-years |
| `year` | No | Calendar year, for example `2026`, if you are not sending `academic_yearID` |

Response: `exams[]` with `examID`, `exam_name`, `academic_yearID`, `year`.

### Results

`GET /api/v1/parent/results`

| Query | Required | Values |
|---|---|---|
| `student` | Yes for one child | `studentID` |
| `academic_yearID` | Recommended | From academic-years / `data.academic_years` |
| `year` | Yes if no academic_yearID | `calendar_year` from the chosen academic year card |
| `term` | Yes | `first_term` or `second_term` (same as website) |
| `type` | Yes | `exam` for one exam, `report` for the term report |
| `exam` | When `type=exam` | `examID` |

`data.academic_years` matches the website year cards for that child.  
`data.years` is the list of those cards' `calendar_year` values (no duplicates from slash years).  
`data.terms` is `first_term`, `second_term` like the website.  
`data.results` is the sheet: subjects, marks, grades, total, average, division, and position when published.

Only results the school has released to parents are returned (`parent_status` enabled, status allowed or approved).

## Attendance

Two views. The calendar matches the parent website. The records list is the raw daily rows.

### Month overview (all children)

`GET /api/v1/parent/attendance/overview?month=11&year=2026&q=`

`q` is an optional name or admission-number search.

Each child has `stats`:

```json
{
  "present": 18,
  "absent": 1,
  "sick": 0,
  "permission": 1,
  "incomplete": 0,
  "working_days": 22,
  "attendance_rate": 90.0
}
```

### One child calendar

`GET /api/v1/parent/attendance/students/12?month=11&year=2026`

`data.days[]` is one object per date:

| Field | Meaning |
|---|---|
| `date` | `YYYY-MM-DD` |
| `date_label` | Localized long date |
| `status` | `present`, `absent`, `sick`, `permission`, `incomplete`, `non_school`, `future` |
| `label` | Text to show |

`non_school` is a weekend or holiday. `future` is a school day that has not happened. `incomplete` means the day passed and attendance was not marked.

`data.stats` is the same shape as the overview. `data.legend` is the color key.

### Raw records

`GET /api/v1/parent/attendance`

| Query | Default | Values |
|---|---|---|
| `search_type` | `month` | `month`, `year`, `date` |
| `year` | current year | |
| `month` | current month | `1`–`12` |
| `date` | | `YYYY-MM-DD` when `search_type=date` |
| `student` | all children | `studentID` |

`data.overview` has `total_days`, `present`, `absent`, `late`, `excused`, `attendance_rate`.

`data.daily_records` and `data.grouped_records` are the rows.

## Fingerprint attendance

Same shape as the calendar endpoints. A child whose class is not enabled for the parent fingerprint portal has `fingerprint_enabled: false` and `stats: null`.

- `GET /api/v1/parent/fingerprint-attendance?month=11&year=2026&q=`
- `GET /api/v1/parent/fingerprint-attendance/students/12?month=11&year=2026`

The student calendar returns HTTP 403 when that class is not enabled. Status values are `present`, `absent`, `sick`, `permission`, `non_school`.

## Payments and fees

### Payments

`GET /api/v1/parent/payments?year=2026&student=12&fee_type=&search=`

All query fields are optional. `year` defaults to the current year.

`data` includes the parent, school, children, available years, and each payment (control number, fee, amount, date, status).

**Filter UI (bottom sheet):** `data.filter_ui` tells the app how to build filters:

- Show a **filter icon** on Make Payment (`filter_ui.icon = "filter"`).
- On click, open a **bottom sheet** (`presentation: "bottom_sheet"`).
- Fields: Child (`student`), Year (`year`), Fee type (`fee_type`), Search (`search`).
- Apply → reload `GET /payments` with those query params.

### Fees summary

`GET /api/v1/parent/fees-summary?academic_yearID=16`

Query parameters (both optional):

| Param | Notes |
|-------|-------|
| `academic_yearID` | Pick one card from `data.academic_years`. Recommended. |
| `year` | Legacy: a calendar year. Used only when `academic_yearID` is missing; picks the card whose `calendar_year` matches. |

If neither is sent, the selected card (the school's current year) is used.

Response `data`:

- `academic_years`: the same cards as `GET /api/v1/parent/academic-years` for the school (one entry per academic year, no duplicates). Each has `academic_yearID`, `label`, `short_label`, `calendar_year`, `is_selected`, `is_a_level`. Use this list for the period dropdown.
- `filters`: the card in use: `academic_yearID`, `year` (its `calendar_year`), `label`.
- `summary[]`: one row per active child with `tuition_fees`, `other_fees`, and `total` (required / paid / balance).

Payments are counted per card: a payment belongs to the card with the same `academic_yearID`. Older payments with no `academic_yearID` fall back to the card's `calendar_year`.

The old `years` list (calendar years from payment dates, plus the current year) is removed. It was the cause of the duplicated year: for example, a school with cards `2026` and `2025/2026` showed `2025` and `2026` as separate entries.

The payload is the balance sheet the parent website shows: expected fees, amount paid, and balance per child.

### Request a control number

`POST /api/v1/parent/students/{studentID}/control-number`

If a control number **already exists** for the current academic year:

```json
{
  "success": true,
  "already_generated": true,
  "generated": 0,
  "message": "Control number already generated.",
  "message_sw": "Control number tayari imezalishwa.",
  "control_number": "334530719"
}
```

App: if `already_generated` is true, show that message (toast/dialog) and still display `control_number`. Do not pretend a new one was created. SMS is sent only on the first generation.

First-time success:

```json
{
  "success": true,
  "already_generated": false,
  "generated": 1,
  "message": "Control number imezalishwa kikamilifu.",
  "control_number": "334530719"
}
```

No body. The student must be Active and belong to this parent.

Success:

```json
{ "success": true, "message": "..." }
```

The school SMS flow still runs, the same as the website. Show `message` to the parent.

## Subjects

Only active children.

`GET /api/v1/parent/students/{studentID}/subjects`

```json
{
  "success": true,
  "student": { "studentID": 12, "subclass_name": "Form II A" },
  "subjects": [
    {
      "class_subjectID": 40,
      "subject_name": "History",
      "subject_code": "HIST",
      "teacher_name": "Jane Doe",
      "status": "Active",
      "student_status": "Optional",
      "is_elected": false,
      "elected_count": 10,
      "total_students": 35
    }
  ]
}
```

`student_status` is `Required` or `Optional`.

Elect an optional subject:

`POST /api/v1/parent/subjects/elect`

```json
{ "studentID": 12, "classSubjectID": 40 }
```

Remove it:

`POST /api/v1/parent/subjects/deselect`

```json
{ "studentID": 12, "classSubjectID": 40 }
```

Success body uses `success` as a string message (`"Subject elected successfully!"`). Treat any HTTP 200 as success. HTTP 400 means the subject is required, already elected, or not this child's class. The school still sends the same SMS as the website.

## Notes and past papers

`kind` is `note` or `past_paper`. Default is `note`.

1. Children and counts

`GET /api/v1/parent/learning?kind=note`

```json
{
  "success": true,
  "data": {
    "kind": "note",
    "children": [
      {
        "studentID": 12,
        "full_name": "John Peter",
        "class_label": "Form II A",
        "photo": null,
        "notes_count": 4,
        "unread_count": 1
      }
    ]
  }
}
```

2. Subjects for that child

`GET /api/v1/parent/learning/students/12/subjects?kind=note`

Each subject has `class_subjectID`, `subject_name`, `teacher_name`, `notes_count`, `unread_count`.

3. Files for one subject

`GET /api/v1/parent/learning/students/12/subjects/40?kind=past_paper`

Opening this list marks those items as read for this child.

```json
{
  "success": true,
  "subject_name": "History",
  "notes": [
    {
      "noteID": 9,
      "title": "Week 3",
      "original_filename": "week3.pdf",
      "mime_type": "application/pdf",
      "file_kind": "PDF",
      "file_size": 120034,
      "view_url": "http://server/api/v1/parent/files/notes/9?inline=1",
      "download_url": "http://server/api/v1/parent/files/notes/9"
    }
  ]
}
```

## Home packages

1. Holidays that have a package for this parent's children

`GET /api/v1/parent/home-packages`

```json
{
  "success": true,
  "data": {
    "holidays": [
      {
        "holidayID": 2,
        "holiday_name": "Midterm",
        "start_date": "2026-05-01",
        "end_date": "2026-05-10"
      }
    ]
  }
}
```

2. Files for one holiday

`GET /api/v1/parent/home-packages/holidays/2`

`packages[]` has `home_packageID`, `title`, `subject_name`, `class_label`, `teacher_name`, `view_url`, `download_url`.

## Downloading a file

`view_url` and `download_url` are not public. Call them with the same bearer token.

- `GET /api/v1/parent/files/notes/{noteID}` downloads the file
- `GET /api/v1/parent/files/notes/{noteID}?inline=1` opens a PDF inline
- `GET /api/v1/parent/files/home-packages/{homePackageID}`
- `GET /api/v1/parent/files/home-packages/{homePackageID}?inline=1`

The response is the file itself (`Content-Type` is the real mime type), not JSON. HTTP 404 means the parent cannot see that file or the file is missing on the server.

## Notifications

`GET /api/v1/parent/notifications`

```json
{
  "success": true,
  "unread": 3,
  "unread_results": 1,
  "unread_home_packages": 1,
  "unread_notes": 1,
  "unread_past_papers": 0,
  "notifications": []
}
```

Each notification:

| Field | Use |
|---|---|
| `id` | Pass this when marking one item read |
| `source` | `result`, `home_package`, `note`, `past_paper` |
| `type` | `exam`, `report`, `home_package`, `note`, `past_paper` |
| `screen` | Which app screen to open: `results`, `reports`, `home_packages`, `notes`, `past_papers` |
| `is_viewed` | `false` means unread |
| `title`, `message` | Show these |
| `student_name` | Present for results and notes |
| `view_url`, `download_url` | Present for files. Still need the bearer token |

Mark read:

`POST /api/v1/parent/notifications/read`

Mark everything:

```json
{ "source": "all" }
```

Mark one result notification:

```json
{ "source": "result", "id": 15 }
```

Mark every note notification:

```json
{ "source": "note", "all": true }
```

`source` is `all`, `result`, `home_package`, `note`, or `past_paper`.

## Permission requests

List:

`GET /api/v1/parent/permissions`

Optional filters, same as the website:

| Query | Values |
|---|---|
| `status` | `pending`, `approved`, `rejected` |
| `filter_type` | `all` (default), `range`, `month`, `year` |
| `date_from`, `date_to` | `YYYY-MM-DD` when `filter_type=range` |
| `filter_month`, `filter_month_year` | when `filter_type=month` |
| `filter_year` | when `filter_type=year` |

```json
{
  "success": true,
  "data": {
    "students": [],
    "pending": [],
    "approved": [],
    "rejected": [],
    "unread": { "pending": 0, "approved": 1, "rejected": 0 }
  }
}
```

Each permission has `id`, `student_id`, `student_name`, `start_date`, `end_date`, `days_count`, `reason_type` (`permission` or `sick`), `reason_description`, `status`, `attachment_url`, `is_read_by_parent`, `created_at`.

Submit (multipart, because of the optional file):

`POST /api/v1/parent/permissions`

`Content-Type: multipart/form-data`

| Field | Required | Notes |
|---|---|---|
| `student_id` | Yes | Active child |
| `start_date` | Yes | `YYYY-MM-DD` |
| `end_date` | Yes | On or after `start_date` |
| `reason_type` | Yes | `permission` or `sick` |
| `reason_description` | Yes | At least 5 characters |
| `attachment` | No | pdf, jpg, jpeg, png. Max 5 MB |
| `permission_id` | No | Send the rejected request id to submit it again |

Working days cannot be more than 7. The server counts working days. The parent and the school still receive the same SMS as the website.

```json
{ "success": true, "message": "Ombi la ruksa limewasilishwa kikamilifu." }
```

Mark a tab as read:

`POST /api/v1/parent/permissions/read`

```json
{ "status": "approved" }
```

`status` is `pending`, `approved`, or `rejected`.

## Suggested app flow

1. Login. If `user_type` is not `parent`, show "This app is for parents" and do not continue.
2. Save the token.
3. `GET /api/v1/parent/profile` for the header (name, school, children).
4. `GET /api/v1/parent/dashboard` for home: stats, `result_cards`, `alerts`, children.
5. Services tab: `GET /api/v1/parent/services` — render **cards** like the teacher app.
6. Alerts: show all `alerts` / `GET /notifications` (badge = `unread`).
7. Make Payment: filter **icon** → bottom sheet from `filter_ui` → reload `/payments`.
8. Control number: on tap show `already_generated` message when true.
9. Results: never open bare `/results`; use `result_cards[].open` or the wizard (child → year → term → exam).

When the API returns 401, clear the token and show the login screen.

## What the server team must do

Run this once on the school server so tokens can be stored:

```
php artisan migrate
```

The login call also creates the `mobile_api_tokens` table if that migration has not been run yet.

Set `APP_URL` to the address phones use, so photo and file links are correct.
