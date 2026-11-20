# ShuleXpert Teacher Mobile API

Give this document to the mobile app developer. It mirrors the website teacher portal (`/teachersDashboard` and every page in the teacher sidebar). The app calls the same business code as the website, so validation, permissions and results are identical to the web.

Base URL: `{APP_URL}/api/v1/teacher` (login and profile helpers live under `/api/v1/auth`).

## How it works

- Every request carries `Authorization: Bearer {token}`. The server reads `teacherID`, `schoolID` and the user from the token. **Never send `teacherID`, `schoolID`, `user_id` or `user_type` from the app.** They are ignored/overwritten.
- The website shows or hides menu items based on the teacher's roles. The app must do the same using `GET /teacher/capabilities` (see below). Endpoints enforce the same rules on the server and return `403`/`422` with a message when the teacher is not allowed.
- Three response shapes exist, because the endpoints reuse the website's code:

| Kind | Shape | Used for |
|---|---|---|
| Page data | `{ "success": true, "data": { ... } }` | Screens that are pages on the website. `data` holds exactly what the web page receives (lists, filters, counts). |
| Web JSON | The website's own JSON, e.g. `{ "success": true, "students": [...] }` or `{ "success": true, "message": "..." }` | AJAX actions of the web portal (attendance, logbook, results, ...). |
| Message | `{ "success": true/false, "message": "..." }` | Form posts that redirect on the web. Failures return `422`. |

- File downloads (PDF/Excel) return the file with its normal content type. Send the same `Authorization` header when downloading.
- Validation errors are `422` with `errors` (Laravel format), or `{ "success": false, "message" | "error": "..." }`.
- Language: send `Accept-Language: en` or `sw` (default `sw`).
- Date format is `YYYY-MM-DD`, time `HH:MM`.

## Authentication

### Login

`POST /api/v1/auth/login`

```json
{ "username": "T007", "password": "********", "device_name": "Teacher Android" }
```

`username` is the teacher's **employee number**. Success `200` returns `token`, `token_type: "Bearer"`, `expires_at`, `user`, `schoolID`, `teacherID`, `teacher{ id, first_name, last_name, employee_number, full_name }`. Errors: `401` wrong credentials, `403` account/school suspended, `404` teacher record missing, `429` too many attempts. Tokens last 90 days, max 8 devices per user.

The login response also has `force_password_change` (`true` when the teacher is still on the default password and the school requires a change, same rule as the website). When it is `true`, send the user to **Change password** before anything else.

While the default password is still in use, every `/api/v1/teacher/*` endpoint returns `403` with `"code": "password_change_required"`. The exceptions are `GET /api/v1/auth/me`, `POST /api/v1/auth/password`, `POST /api/v1/auth/logout`, `GET /api/v1/teacher/profile`, `POST /api/v1/teacher/password/change` and `POST /api/v1/teacher/profile/update`.

### Session / capabilities

| Purpose | Endpoint |
|---|---|
| Who am I (+ school + capabilities) | `GET /api/v1/auth/me` |
| Change password | `POST /api/v1/auth/password` (`current_password`, `password`, `password_confirmation`) |
| Logout | `POST /api/v1/auth/logout` |

`GET /api/v1/teacher/capabilities` (also embedded in `/auth/me` as `data.capabilities`) returns the flags the web sidebar uses:

```json
{
  "teacher": { "id": 9501, "full_name": "Halima Masele", "employee_number": "002", "schoolID": 9, "photo": "https://.../userImages/x.jpeg" },
  "school": { "schoolID": 9, "name": "We Go Green Daycare" },
  "roles": [{ "id": 4, "name": "Academic Master" }],
  "is_hod": false, "hod_departments": [],
  "is_class_teacher": true, "is_coordinator": false, "has_assigned_class": true,
  "is_on_duty": false,
  "is_student_requester": false,
  "can_add_results": false,
  "can_collect_exam_attendance": false,
  "has_black_book_access": false,
  "has_inventory_access": false,
  "student_portal_enabled": false,
  "badges": { "pending_paper_approvals": 0, "pending_result_approvals": 0, "pending_permission_approvals": 0, "unread_suggestions": 0, "unread_incidents": 0 },
  "management_permission_categories": ["examination", "result"],
  "permissions_by_category": { "examination": ["create_examination", "..."] }
}
```

### Menu rules (same as the website sidebar)

| Menu item | Show when |
|---|---|
| Student leave | `is_student_requester` |
| Department logbooks, Team goals (HOD) | `is_hod` |
| My class | `has_assigned_class` |
| Add results (bulk) | `can_add_results` |
| Exam attendance (invigilation) | `can_collect_exam_attendance` |
| Black book (manage) | `has_black_book_access` (the "Report student" form is always available) |
| Learning portal | `student_portal_enabled` |
| Management menu, each module | the module's category in `management_permission_categories` (table under **Management** below) |
| Store inventory | `has_inventory_access` (Requisitions are always available) |
| "On duty this week" banner | `is_on_duty` |
| Badges on paper approvals / result approvals / Permission approvals / Suggestions / Incidents | `badges.*` (`pending_result_approvals` → `GET /result-approvals`) |
| Everything else | always |

## Endpoint reference

All paths below are relative to `/api/v1/teacher`. `{id}` style placeholders are numeric.

### Dashboard and account

| Screen | Endpoint | Notes |
|---|---|---|
| Dashboard | `GET /dashboard` | Same data as the website dashboard. See the block below |
| Profile | `GET /profile` | Full hub (identity, TANGE, personal, employment, timeline) — **`docs/TEACHER_PROFILE_API.md`** |
| Update profile | `POST /profile/update` | Same as web Edit Profile (TANGE fields); auto-fills `teacher_id` / `employee_number` |
| Update photo | `POST /profile/image` | multipart, field `teacher_image` (jpeg/png/jpg/gif, max 2 MB) |
| Profile PDF | `GET /profile/pdf` | TANGE PDF file |
| Upload / delete signature | `POST /signature`, `POST /signature/delete` | |
| Change password | `POST /password/change` | `current_password`, `new_password`, `new_password_confirmation` (or `confirm_password`); min 8 + complexity (same as web) |

#### Dashboard payload (`GET /dashboard`)

| Field | Shows on the dashboard as |
|---|---|
| `greeting` | `name`, `date` (welcome card) |
| `is_on_duty` | "On duty this week" banner |
| `session_alert` | "Session time" banner (`title`, `message`, `link`) or `null` |
| `upcoming_session` | "Upcoming session" / "Now in session" card: `subject`, `class`, `start`, `end`, `is_now` (or `null`) |
| `stat_cards[]` | Stat cards. `key`, `label`, `value`, `note`, `api_path` (`null` = no screen to open). Includes Sessions/Year, Sessions Entered, Scheme of Work, Coordinator (only if > 0) |
| `attendance_today` | `status` (`Present`, `Early`, `Late`, `Absent`, `Permitted`, `Not Available`), `check_in_time`, `check_out_time`, `official_start`, `official_end`. Check-in and check-out are recorded by the fingerprint device; the app only displays them |
| `tange` | TANGE profile: `completion_percent`, `is_complete`, `missing_fields`, `retirement_alert`, `api_path` (profile), `pdf_path` (profile PDF). `null` if the teacher record is missing |
| `approvals` | `pending` (you can approve now), `special` (class teacher / coordinator / HOD), `waiting` (blocked by an earlier step, with `current_approver`). Each item has `examID`, `exam_name`, `role`, `status`, `rejection_reason` |
| `rejection_notifications` | Exam rejections to show |
| `notifications` | Other notifications |
| `teaching_subjects` | Subject names for the "My subjects" list |

### My attendance

Full hand-off: **`docs/TEACHER_MY_ATTENDANCE_API.md`**.

`GET /attendance` (defaults to today / day). Query: `filter_type=day|month`, `date` (day) or `month` (1-12) + `year` (month). PDF: `GET /attendance/pdf` (same query).

### Student leave (only when `is_student_requester`)

| Endpoint | Purpose |
|---|---|
| `GET /student-leave/search?query=` | Find a student |
| `GET /student-leave/requests` | `pending`, `approved`, `rejected` lists |
| `GET /student-leave/{id}` | Request details |
| `GET /student-leave/{id}/progress` | Approval chain progress |
| `POST /student-leave` | Create. `student_id`, `time_mode=days|hours`, `reason_type`, `reason_description`, optional `attachment` (pdf/jpg/png, 5 MB). Days: `days_count`, `start_date`, `end_date`. Hours: `start_date_hours`, `start_time`, `end_time` |

### Teaching (full hand-off)

See **`docs/TEACHER_TEACHING_API.md`** for every Teaching submenu (Sessions, Tasks, Subjects, Scheme of Work, Lesson Plans, Logbook, Calendar, Department Logbooks) with request/response shapes and app flows.

Scheme of Work alone (full app hand-off): **`docs/TEACHER_SCHEME_OF_WORK_API.md`**.

Lesson Plans alone (full app hand-off): **`docs/TEACHER_LESSON_PLANS_API.md`**.

Logbook alone (full app hand-off): **`docs/TEACHER_LOGBOOK_API.md`**.

Sessions (full hand-off): **`docs/TEACHER_SESSIONS_API.md`**.  
Timetable (full hand-off): **`docs/TEACHER_TIMETABLE_API.md`**.

Quick index: `GET /sessions`, `GET /tasks`, `GET /subjects`, `GET /scheme-of-work`, `GET /lesson-plans`, `GET /logbook`, `GET /calendar`, `GET /department-logbook` (web `/teacher/department-logbook`, HOD only; + `/department-logbook/subclasses/{classID}`).

### Class

`GET /my-class` (class teacher / coordinator view).  
`GET /timetable` — see **`docs/TEACHER_TIMETABLE_API.md`**.

### Exams

Full hand-off for every Exams submenu: **`docs/TEACHER_EXAM_API.md`**.

Quick index: `GET /exams` → Add Results `/add-results` · Supervise `/exams/supervise` · Papers `/exam-papers` · Approvals `/exam-paper-approvals` · Attendance `/exam-attendance` (gated by capabilities).

### HR

| Screen | Endpoint |
|---|---|
| My permissions | `GET /permissions` (tabs: `tab=request|pending|approved|rejected`, date filters as on web) |
| Request permission | `POST /permissions` - `time_mode=days|hours`, `reason_type` (`medical,official,professional,emergency,other`), `reason_description` (min 5). Days: `days_count`, `start_date`, `end_date`. Hours: `start_time`, `end_time` (HH:MM) |
| Approvals (only if the teacher is in an approval chain) | `GET /permission-approvals`, `POST /permission-approvals/approve`, `POST /permission-approvals/reject` |
| Permission details | `GET /permissions/progress`, `GET /permissions/{permissionID}/pdf`, `GET /permissions/{permissionID}/attachment` |

### Goals and performance

Full hand-off: **`docs/TEACHER_GOALS_API.md`**.

| Screen | Endpoint |
|---|---|
| Goals menu | `GET /goals` |
| HOD team goals (web `/goals/hod/assigned`) | `GET /goals/hod/assigned` (alias `/goals/team`), `GET /goals/hod/tasks/{id}`, `GET /goals/hod/member-tasks/{parent_task_id}`, `GET /goals/hod/department-members`, `POST /goals/hod/assign-member`, `POST /goals/review/approve-subtask`, `POST /goals/review/reset-marks/{id}` — HOD only |
| Direct Goals | `GET /goals/direct` |
| Assigned Tasks | `GET /goals/assigned` (also `GET /goals/my-tasks`) |
| Work on sub-tasks | `GET /goals/subtasks/{task_id}`, `GET /goals/subtask/{id}`, `POST /goals/subtask`, `POST /goals/subtask/{id}/update`, `POST /goals/subtask/{id}/submit`, `DELETE /goals/subtask/{id}`, `POST /goals/step`, `POST /goals/step/{id}/update`, `DELETE /goals/step/{id}` |
| Notifications | `GET /goals/notifications`, `POST /goals/notifications/read-all`, `POST /goals/notifications/{id}/read` |
| My tasks (SGPM) | `GET /sgpm/tasks`, `POST /sgpm/tasks`, `POST /sgpm/tasks/{id}/submit`, `POST /sgpm/tasks/{id}/evaluate`, `POST /sgpm/subtasks`, `POST /sgpm/subtasks/{id}/submit`, `POST /sgpm/subtasks/{id}/approve`, `POST /sgpm/subtasks/{id}/reject` |
| Scorecard | `GET /performance/staff`, `GET /performance/hod` |

### Feedback

`GET /suggestions`, `GET /incidents` (page data with `suggestions`, `incidents`, stats; use `tab`, `section`, `date_from`, `date_to`), `POST /feedback` with `type=suggestion|incident` and `message`.

### Character assessment

Full hand-off: **`docs/TEACHER_CHARACTER_ASSESSMENT_API.md`**.

`GET /character-assessment`, `GET /character-assessment/students?classID=&subclassID=&timetableID=`, `GET /character-assessment/history?studentID=&timetableID=`, `POST /character-assessment`.

### Black book

Report Student (everyone) — full hand-off **`docs/TEACHER_BLACK_BOOK_API.md`**:  
`GET /black-book/report-form` (alias `/black-book/report-student`), `GET /black-book/students/search?for_report=1`, `POST /black-book/reports`, `GET /black-book/reports?mine=1`.

Management (only `has_black_book_access`, otherwise `403`):
- Page data: `GET /black-book/manage`, `GET /black-book/reports`, `/problem-categories`, `/punishment-categories`, `/actors`, `/student-summary`, `/dashboard-stats`, `/analytics`, `GET /black-book/export-pdf`
- Actors: `POST /black-book/actors`, `POST /black-book/actors/{actorID}/update|delete`
- Categories: `POST /black-book/problem-categories`, `.../{categoryID}/update|delete`; `POST /black-book/punishment-categories`, `.../{punishmentCategoryID}/update|delete`
- Reports: `POST /black-book/reports/{reportID}/view|resolve|end-monitoring`

### Inventory (store management)

Visible when `has_inventory_access` is `true`. Teachers who only have requisitions (Main > Requisitions) do not get these. Actor management (`/inventory/actors/*`) returns `403` unless the teacher is an inventory actor manager.

| Area | Endpoints |
|---|---|
| Page and reports | `GET /inventory`, `/inventory/dashboard`, `/inventory/export-pdf`, `/inventory/reports/all`, `/inventory/reports/requisitions`, `/inventory/reports/specific-item` |
| Actors | `GET /inventory/actors`; `POST /inventory/actors`, `.../{actorID}/update|delete` |
| Categories | `POST /inventory/categories`, `.../{categoryID}/update|delete` |
| Measurement types and units | `POST /inventory/measurement-types`, `.../{measurementTypeID}/update|delete`, `GET .../{measurementTypeID}/units`; `POST /inventory/units`, `.../{unitID}/update|delete` |
| Items and packages | `POST /inventory/items`, `.../{itemID}/update|delete`; `POST /inventory/packages/bulk`, `.../{packageID}/update|delete` |
| Stock | `POST /inventory/stock`, `POST /inventory/stock/{stockEntryID}/delete` |
| Requisitions | `POST /inventory/requisitions`, `POST /inventory/requisitions/{requisitionID}/return` |
| Item requests | `POST /inventory/item-requests/mark-read`, `.../{requestID}/approve|reject` |
| Incidents | `POST /inventory/incidents`, `.../{incidentID}/update|resolve|delete` |

Forms use the same field names as the website. Send them as `application/json` or `multipart/form-data`, whichever the form on the website uses.

### Duty book

Full hand-off in `docs/TEACHER_DUTY_BOOK_API.md`. `GET /duty-book` (weeks > days > report state), `GET /duty-book/report` (`date` or `report_id`), `POST /duty-book/report` (`report_date`, `action=send|draft`, plus report fields), `GET /duty-book/parent-permissions` (`date`), PDFs: `GET /duty-book/pdf`, `GET /duty-book/report/pdf`.

### Store requisitions

Full hand-off: **`docs/TEACHER_STORE_REQUISITIONS_API.md`**.  
`GET /store-requisitions` (hub: dashboard, available items + take_options, pending/history, issued, incidents), `POST /store-requisitions` (`issues[]` with `stock_unitID` / `take_key` / `quantity`), `GET /store-requisitions/pdf?section=`.

### Learning portal (only when `student_portal_enabled`, otherwise `422`)

`GET /learning-portal`; notes `POST /learning-portal/notes`, `POST /learning-portal/notes/{id}/toggle`, `DELETE /learning-portal/notes/{id}`; books `POST /learning-portal/books`, `.../books/{id}/toggle`, `DELETE .../books/{id}`; quizzes `POST /learning-portal/quizzes`, `PUT .../quizzes/{id}`, `POST .../quizzes/{id}/toggle`, `DELETE .../quizzes/{id}`; marking `GET .../quizzes/{quizId}/attempts/{attemptId}`, `POST .../quizzes/{quizId}/attempts/{attemptId}/mark`; export `GET .../results/export[/{quizId}]`.

### Management (sidebar Admin > Management)

Full hand-off for the nine main Management pages (library, results, accommodation, classes, subjects, examinations, students, timetable, fees): **`docs/TEACHER_MANAGEMENT_API.md`**.  
Menu index: `GET /management`. Each hub is reshaped JSON and returns **403** without the matching `permission_category`.

These are the modules a teacher can be given through roles. Show a module only when its category is in `capabilities.management_permission_categories`. Without that category the endpoint returns `403` with `"message": "You do not have access to this module."`.

**Path rule:** the API path is the website URL with `/api/v1/teacher` in front. Example: website `/manageExamination` becomes `GET /api/v1/teacher/manageExamination`; website `/admin/school-visitors` becomes `GET /api/v1/teacher/admin/school-visitors`. Pages that return HTML on the website return the same data as `{ "success": true, "data": { ... } }`. Write actions (`POST`, `PUT`, `DELETE`) use the same paths and the same fields as the website forms, and return `{ "success": true, "message": "..." }` or a validation error (`422`).

| Category | Module (sidebar label) | Example entry points |
|---|---|---|
| `examination` | Examinations | `GET /manageExamination`, `GET /get_exam_timetables`, `POST /store_exam_timetable` |
| `subject` | Subjects | `GET /manageSubjects` |
| `subject_analysis` | Topic analysis | `GET /admin/subject-analysis` |
| `classes` | Classes | `GET /manageClasses`, `GET /get_classes` |
| `result` | Results | `GET /manageResults` |
| `attendance` | Attendance | `GET /manageAttendance`, `GET /manageTeacherStaffAttendance` |
| `student` | Students | `GET /manage_student`, `GET /admin/student-id-cards/{classID?}` |
| `timetable` | Timetable | `GET /timeTable`, `POST /admin/save-session-timetable-definition` |
| `fees` | Fees | `GET /manage_fees`, `GET /fees/active_banks` |
| `accommodation` | Hostel | `GET /manage_accomodation` |
| `library` | Library | `GET /manage_library` |
| `calendar` | Calendar | `GET /admin/calendar` |
| `fingerprint` | Fingerprint devices | `GET /fingerprint_device_settings` |
| `task` | Tasks | `GET /task-management` |
| `sms` | SMS | `GET /sms_notification` (sending SMS is an explicit action; check before using in the app) |
| `teacher` | Staff | `GET /manageTeachers` |
| `printing_unit` | Printing unit | `GET /admin/printing-unit` |
| `watchman` | Watchman | `GET /manage_watchman` |
| `school_visitors` | School visitors | `GET /admin/school-visitors` |
| `scheme_of_work` | Schemes (all) | `GET /admin/scheme-of-work` |
| `lesson_plans` | Lesson plans (all) | `GET /admin/lesson-plans` |
| `academic_years` | Academic years | `GET /admin/academic-years` |
| `school` | School | `GET /school`, `POST /update_school` |
| `sponsor` | Sponsors | `GET /manage_sponsors` |
| `student_id_card` | Student ID cards | `GET /admin/student-id-cards/cards` |
| `hr` | HR setup | `GET /admin/hr/permissions`, `GET /admin/hr/roles-positions` |
| `teacher_duty` | Duties | `GET /admin/teacher-duties`, `GET /admin/teacher-duties/report` |
| `feedback` | Suggestions and incidents | `GET /admin/suggestions`, `GET /admin/incidents` |
| `staff_feedback` | Staff suggestions and incidents | `GET /admin/staff-suggestions`, `GET /admin/staff-incidents` |
| `performance` | Performance | `GET /admin/performance` |
| `accountant` | Expenses, income, budget, finance reports | `GET /accountant/expenses`, `GET /accountant/income`, `GET /accountant/budget`, `GET /accountant/reports` |
| `goal` | Goals admin and goal reports | `GET /goals/list`, `GET /goals/reports` |
| `department` | Departments | `GET /sgpm/departments` |

About 530 routes are mirrored in total. The full list is in `routes/mobile_teacher_management.php`; each row names the web controller method it runs. Parameter names in the path match the website route (for example `{classID?}`).

Known limits (these come from the website code, not the API):

- Some pages in a permitted category still refuse teachers. The web's own check runs, for example admin-only pages such as `admin/suggestions`, `admin/performance`, `admin/hr/permissions`, `fees/active_banks`, and watchman dashboards return `403` with `"Unauthorized access"` or `"Only school Admin ..."`. Show the module, but expect these endpoints to refuse.
- Some pages need extra input that depends on the screen. `GET /get_subclass/{id}`, `GET /get_exam_paper_questions/{id}` and similar return `404`/`422`/`500` when the id is wrong. Send only ids the app got from the same module.
- `GET /get_student_library_for_year` fails with `500` because the `library_transactions` table is not created by any migration in the repository. The feature needs a decision (create the table or hide the screen) before the app shows it.
- Export and PDF endpoints under these modules return files; use the same `Authorization` header when downloading them.
- Not every write has been run with real data yet. Test writes on a test school before releasing.

## Verification status

Checked against a copy of the live database (all writes were made on the copy, never on live data):

| Check | Result |
|---|---|
| Every sidebar link (Main, Teaching, Exams, Class, HR & Goals, Feedback, Account, Management) has an API route | Yes, all 75 links map to an endpoint |
| Every teacher `GET` route (353) with a permitted teacher | 216 `200`, 32 `403`, 1 `500` (`library_transactions`, see above). The other 104 returned `400`, `404` or `422` because the sweep sent placeholder IDs, not real ones |
| Every teacher write route (`POST`/`PUT`/`DELETE`, 310) with an empty body | No `500` except `POST /test_device_connection`, which needs the PHP `sockets` extension enabled on the server |
| Teacher with no permissions, all management `GET` routes (237) | All `403` |
| Happy path with real data | Session attendance (save and read back), session task, lesson plan, student leave (after the teacher has the requester flag), scheme of work, logbook entry, Black Book report, teacher permission request |
| Forced password change | Dashboard and modules return `403 password_change_required` until the password is changed |

**Before the teacher features work on a server, run the pending migrations.** On the development database 13 migrations are still pending, including subject notes, scheme-of-work item weeks, scheduled SMS and the Black Book `custom_problem_name` column. Without them, the Black Book report fails with a `500`. Run `php artisan migrate` (after backing up the database).

Behaviour notes for the app:

- `401` means the token is invalid or expired: log the user out. `403` means the teacher is not allowed (permission, forced password change, or the website refused the page). Web code that answered `401` is now returned as `403`.
- Some website actions return HTTP `200` with `"success": false` (for example student leave when the teacher is not a requester). Check the `success` field, not only the HTTP status.
- `POST /scheme-of-work` returns a `redirect` value pointing at the website (`http://localhost/...`). Ignore it in the app.
- Date-only fields such as `task_date` can come back as UTC timestamps for local midnight (for example `2026-11-15T21:00:00Z` for 16 November in Tanzania). Use the date part in the school's local time.

## Notes for the app developer

- Do not cache `capabilities` for long. Reload it after login and when the app returns to the foreground.
- Many "page data" payloads are Eloquent models serialised as the website receives them, so they can contain extra fields. Use only the fields you need; the field names are stable because the website depends on them.
- Writes that need a file (profile photo, signature, exam paper, Excel results, learning-portal files, attachments) must be `multipart/form-data`.
- The older unauthenticated routes under `/api/teacher/*` still exist. Do not use them in new code. They trust IDs sent by the client. Use `/api/v1/teacher/*` only.
- The token is tied to one teacher. If the teacher's password is reset or the school is suspended, requests fail with `401`/`403` and the app should return to the login screen.
