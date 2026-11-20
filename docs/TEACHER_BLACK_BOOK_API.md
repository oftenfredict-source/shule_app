# Teacher Black Book — Report Student Mobile API

Web page: `/black_book/report-student`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Main → Report Student (always available for teachers)

This hand-off covers the **Report Student** screen only (submit a report + see my reports).  
Full Black Book management (actors, resolve, categories) is gated by `has_black_book_access` — see `docs/TEACHER_MOBILE_API.md` § Black book.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST) |

- Teacher tokens only. Academic year must be selected/open.
- Only **Active** enrollments can be reported.
- Statuses: `pending` · `viewed` · `monitored` · `resolved`.

---

## 1. Screens (match website)

1. **SMS notify bar** — optional “send SMS to parent” default (client preference; also on the form).
2. **My reports** — tabs All / Pending / Viewed / Monitored / Resolved + list/grid.
3. **Report detail** — read-only card (no resolve actions on this page).
4. **Report Student modal** — search student → category → description → optional parent SMS → Submit.
5. Optional link to **Black Book manage** when `can_manage: true`.

---

## 2. Flow

```text
GET /black-book/report-form
  → problem_categories, my_reports, tabs, form_rules

Search student:
  GET /black-book/students/search?search=jo&for_report=1

Submit:
  POST /black-book/reports
  { studentID, problem_categoryID, description, send_parent_sms?, custom_problem_name? }

Refresh my list:
  GET /black-book/reports?mine=1
  GET /black-book/reports?mine=1&status=pending
```

---

## 3. Endpoints

### 3.1 Hub — `GET /black-book/report-form`

Alias: `GET /black-book/report-student` (same payload).

```json
{
  "success": true,
  "data": {
    "title": "Report Student",
    "can_report": true,
    "can_manage": false,
    "manage_path": null,
    "parent_sms_default": false,
    "problem_categories": [
      { "categoryID": 3, "name": "Fighting", "weight": 80, "status": "Active" }
    ],
    "other_category": {
      "value": "other",
      "label": "Other",
      "requires_custom_name": true,
      "custom_name_min": 2,
      "custom_name_max": 150
    },
    "form_rules": {
      "student_search_min": 2,
      "description_min": 5,
      "description_max": 2000,
      "active_enrollments_only": true
    },
    "tabs": [
      { "key": "all", "status": null, "label": "All", "count": 2 },
      { "key": "pending", "status": "pending", "label": "Pending", "count": 1 }
    ],
    "my_reports": [
      {
        "reportID": 12,
        "studentID": 1040,
        "student_name": "Jane Doe",
        "admission_number": "A001",
        "class_name": "FORM ONE A",
        "problem_category": "Fighting",
        "problem_weight": 80,
        "custom_problem_name": null,
        "description": "…",
        "status": "pending",
        "status_label": "Pending",
        "created_at": "2026-11-18 09:10",
        "resolve_method": null,
        "punishment_category": null,
        "punishment_description": null,
        "monitoring_deadline": null,
        "resolved_by_name": null,
        "parent_sms_sent": false
      }
    ],
    "actions": {
      "search_students": "GET /black-book/students/search?search=&for_report=1",
      "submit_report": "POST /black-book/reports",
      "refresh_my_reports": "GET /black-book/reports?mine=1"
    }
  }
}
```

| Field | Use |
|---|---|
| `problem_categories` | Category picker (excludes built-in “Other”) |
| `other_category` | Extra option `value: "other"` → show custom name field |
| `tabs` | Filter chips; filter `my_reports` client-side by `status` |
| `can_manage` | Show “Black Book” manage button if true |
| `my_reports` | Up to 100 of **my** reports (same as website) |

Do **not** use `GET /black-book/problem-categories` for this screen — that endpoint requires manage privilege. Categories come from the hub.

---

### 3.2 Search students — `GET /black-book/students/search`

| Query | Required | Notes |
|---|---|---|
| `search` | yes | min 2 characters |
| `for_report` | yes for this form | `1` — Active enrollments only |

```json
{
  "success": true,
  "students": [
    {
      "studentID": 1040,
      "enrollmentID": 55,
      "enrollment_status": "Active",
      "can_report": true,
      "full_name": "Jane Doe",
      "admission_number": "A001",
      "class_name": "FORM ONE A",
      "display": "Jane Doe - FORM ONE A - Adm: A001"
    }
  ]
}
```

Only pick students with `can_report: true`. Empty + `error_code: no_active_enrollments` → show empty hint.

---

### 3.3 Submit report — `POST /black-book/reports`

```json
{
  "studentID": 1040,
  "problem_categoryID": 3,
  "description": "At least 5 characters describing what happened",
  "send_parent_sms": true
}
```

**Other category**

```json
{
  "studentID": 1040,
  "problem_categoryID": "other",
  "custom_problem_name": "Late to assembly",
  "description": "Was late three times this week",
  "send_parent_sms": false
}
```

| Field | Required | Notes |
|---|---|---|
| `studentID` | yes | from search |
| `problem_categoryID` | yes | int **or** `"other"` |
| `custom_problem_name` | if other | 2–150 chars |
| `description` | yes | 5–2000 chars |
| `send_parent_sms` | no | boolean; SMS parent when true |

Success:

```json
{
  "success": true,
  "message": "Student reported successfully. Admin has been notified.",
  "parent_sms_sent": true,
  "report": { "reportID": 13, "status": "pending", "…": "…" }
}
```

Errors: `422` validation / `enrollment_not_active`; `404` student or category.

After success: prepend `report` to local list, or call `GET /black-book/reports?mine=1`.

---

### 3.4 Refresh my reports — `GET /black-book/reports?mine=1`

| Query | Notes |
|---|---|
| `mine=1` | **Required** for reporters (without manage access) |
| `status` | optional: `pending` \| `viewed` \| `monitored` \| `resolved` (or comma list) |

```json
{ "success": true, "reports": [ /* same shape as hub my_reports */ ] }
```

Report detail on this page is **read-only** (no view/resolve/end-monitoring). Those actions are on Manage Black Book.

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Open Report Student | `GET /black-book/report-form` |
| Parent SMS toggle | Client state → `send_parent_sms` on submit |
| Status tabs | Client filter on `my_reports` **or** `GET …/reports?mine=1&status=` |
| Tap report → detail | Show fields from that `my_reports` row |
| Report Student button / modal | Search §3.2 → Submit §3.3 |
| Category “Other” | `problem_categoryID: "other"` + `custom_problem_name` |
| Link to Black Book manage | Only if `can_manage` → `/black-book/manage` |

---

## 5. Related

- Capabilities: `has_black_book_access` for manage screen  
- Manage endpoints: `docs/TEACHER_MOBILE_API.md` § Black book  
- Auth: `docs/TEACHER_MOBILE_API.md`
