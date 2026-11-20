# Teacher Management — Mobile App Instructions

Build the Management section of the teacher app to match the website screens (cards + SETTINGS/ACTION buttons), with the same permissions.

**Base URL:** `http://192.168.100.105:5000/api/v1/teacher`  
**Login:** `POST http://192.168.100.105:5000/api/v1/auth/login`

---

## 1. Auth (every request)

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

1. Login with employee number + password → save `token`.
2. Call `GET /capabilities` (or use `capabilities` from `GET /api/v1/auth/me`).
3. If response is `403` with `password_change_required` → force password change first.
4. Never send `teacherID` / `schoolID` from the app — token sets them.

---

## 2. Show / hide Management menu

From capabilities:

```json
"management_permission_categories": ["classes", "examination", "library", ...]
```

Or call `GET /management` and use only `visible_modules` (`locked: true` → hide).

| Category | Menu label | Open hub |
|---|---|---|
| `examination` | Examinations | `GET /manageExamination` |
| `result` | Results | `GET /manageResults` |
| `subject` | Subjects | `GET /manageSubjects` |
| `classes` | Classes | `GET /manageClasses` |
| `student` | Students | `GET /manage_student` |
| `timetable` | Timetable | `GET /timeTable` |
| `fees` | Fees | `GET /manage_fees` |
| `library` | Library | `GET /manage_library` |
| `accommodation` | Hostel | `GET /manage_accomodation` |

If category missing → do not show tile. If user deep-links → hub returns `403` → show “No access”.

---

## 3. How every Management screen works

```text
1. GET /{hub}
2. Show title + settings_label (e.g. "SETTINGS")
3. Render toolbar[] as buttons (use label text exactly)
4. Render cards[] (or tabs[].cards / tabs[].groups)
5. On each card, render card.actions[]
6. On tap: HTTP method + BASE + path
   Replace {classID}, {examID}, {feeID}, {subjectID}, {studentID}, {bookID}, {blockID}, …
```

### Button object (toolbar or card.actions)

```json
{
  "key": "edit",
  "label": "Edit",
  "method": "GET",
  "path": "/get_class/{classID}",
  "enabled": true,
  "note": "Then POST /update_class"
}
```

**Rules for the app**

- Only render buttons with `"enabled": true`.
- Use `label` as the visible text (same as website).
- Full URL = `BASE + path` after replacing placeholders from the card.
- After a successful write (create/edit/delete/activate) → refresh the hub (`GET` again).
- `401` → logout. `403` → access denied message. `422` → show `message` / validation errors.

---

## 4. Screen-by-screen (what to build)

### 4.1 Classes — `GET /manageClasses`

**SETTINGS toolbar:** VIEW CLASSES · GRADING · VIEW COMBIES · ADD COMBIES · ADD SUBCLASS · ADD CLASS  

**Each card:**

| Field | UI |
|---|---|
| `title` | e.g. FORM TWO |
| `status` | Active / Inactive badge |
| `stats[]` | Subclasses count · Active enrolled count |
| `coordinator` | “Coordinator: NAME” (or Not Assigned) |
| `actions[]` | View Subclasses · Edit · Deactivate/Activate · Delete |
| `subclasses[]` | Open when “View Subclasses” tapped |

Example card actions:

- Edit → `GET /get_class/{classID}` then form → `POST /update_class`
- Deactivate → `POST /activate_class/{classID}`
- Delete → `DELETE /delete_class/{classID}`
- ADD CLASS → `POST /save_class` (body same as website form)

---

### 4.2 Subjects — `GET /manageSubjects`

**SETTINGS:** VIEW CLASS SUBJECTS · ADD CLASS SUBJECT · ADD SCHOOL SUBJECT  

**Cards:** school subjects (`title`, `subject_code`, `status`, duo `papers[]`)  
**Card actions:** Edit · Activate/Deactivate · Delete  

- View class subjects → `GET /get_class_subjects`
- Add school subject → `POST /save_school_subject`

---

### 4.3 Library — `GET /manage_library`

**ACTION:** ADD BOOK · BORROW / LEND · RECORD DAMAGE · RECORD LOSS · EDIT DETAILS · PDF  

**Summary strip:** TOTAL · AVAILABLE · BORROWED · OVERDUE · LOST · DAMAGED (`summary[]`)  

**Catalog cards:** `GET /get_books` then attach `card_actions_template` (EDIT · DELETE · LEND · RETURN · RESOLVE)

---

### 4.4 Fees — `GET /manage_fees`

**SETTINGS:** CREATE FEE · PAYMENTS · DISCOUNTS · AGREEMENTS · ASSIGN SPONSORS · EXPORT PDF · …  

**Tabs:** `tabs[]` → Single Account Fee / Multi Account Fee  
Each tab has `groups[]` by class; each fee row has `actions` (Installments · Edit · Delete)

---

### 4.5 Examinations — `GET /manageExamination`

**Toolbar:** Create New Examination · Search  

**Tabs:** School Exams / Test / Special (`tabs[].cards`)  
**Card:** exam name, term • year, expected students, status, approval  
**Card actions:** View More · Papers · Edit · toggles · Approve/Reject · Delete · …

---

### 4.6 Results — `GET /manageResults`

**Toolbar:** Define Grades · Define Report · Define CA · Filter · Export PDF/Excel · Send SMS  

1. Show filters (`filters`, `classes`, `available_exams`).
2. User selects filters → call hub again with query params (same as website).
3. Show student rows (`students_preview` / results data) with row actions (PDF · SMS · Publish).

---

### 4.7 Students — `GET /manage_student`

**Actions menu:** Register · Quick Registration · Template · Import · Enroll · Move · Export · …  

1. Hub is a shell (filters: `classes`, `academic_years`).
2. Load list: `GET /get_students_list` (+ stats `GET /get_student_statistics`).
3. Attach `row_actions_template` to each student (View · Edit · Move · Delete · …).

---

### 4.8 Timetable — `GET /timeTable`

**Toolbar:** Create Timetable · Load · PDF · Shuffle · Swap · Delete  

**Tabs:** Class · School · Exam · Test  

1. User picks a **subclass** from hub `subclasses[]` (same as website dropdown).
2. Load grid (required query):

```http
GET /admin/get-session-timetable?subclassID=15
```

3. **Do not send `schoolID`.** It comes from the Bearer token session. Calling Load without `subclassID` returns `422` (`subclassID_required`) — that is why you saw “School ID and Subclass ID required” before.
4. Empty state: “Select a class to view session timetable”.
5. If no timetable exists for that subclass → `success: false`, `has_timetable: false`.

---

### 4.9 Hostel — `GET /manage_accomodation`

**Toolbar / sidebar:** Register Block · View Blocks · Register Room · Register Beds · View Beds  

**Cards:** blocks (`title`, location, rooms/beds counts) + Edit · Remove Students · Delete  

Follow-up: `GET/POST /accommodation/rooms`, `/accommodation/beds`, assign/move/release.

---

## 5. Generic UI components (recommended)

1. **ManagementHome** — tiles from `GET /management` / capabilities.
2. **ManagementScreen** — title + expandable SETTINGS + toolbar chips + card grid.
3. **ManagementCard** — title, status badge, `stats` row, coordinator line, action icons.
4. **ActionRunner** — takes `{method, path}`, replaces `{id}` from selected card, runs HTTP, shows toast from `message`, refreshes hub.

Pseudo-flow:

```text
onOpen(module):
  hub = GET module.api_path
  show hub.toolbar
  show hub.cards

onToolbarTap(btn):
  if btn opens modal/form → collect fields → request(btn.method, btn.path, body)
  else → request(btn.method, btn.path)

onCardAction(card, btn):
  path = btn.path.replace("{classID}", card.classID) // etc.
  request(btn.method, path)
  if success → reload hub
```

---

## 6. Errors

| HTTP | App behaviour |
|---|---|
| `401` | Logout / re-login |
| `403` + `permission_category_required` | Hide module / “No access” |
| `403` + `password_change_required` | Password change screen |
| `422` | Show `message` (and validation errors if present) |
| `200` + `success: false` | Treat as failure; show `message` |

---

## 7. Do / Don’t

**Do**

- Match website labels from `toolbar` / `actions` (do not invent your own).
- Reload capabilities when app resumes.
- Refresh hub after writes.
- Use only `/api/v1/teacher/*` (not old `/api/teacher/*`).

**Don’t**

- Show a module without its category.
- Show `enabled: false` buttons.
- Hard-code teacher/school IDs.
- Assume every write succeeds — check `success` and status.

---

## 8. Quick test (Academic role)

```http
POST /api/v1/auth/login
{ "username": "<employee_number>", "password": "...", "device_name": "android" }

GET /api/v1/teacher/capabilities
GET /api/v1/teacher/management
GET /api/v1/teacher/manageClasses
```

Expect Classes toolbar with VIEW CLASSES … ADD CLASS, and cards like FORM TWO with Subclasses / Active enrolled / Coordinator / Edit·Deactivate·Delete.
