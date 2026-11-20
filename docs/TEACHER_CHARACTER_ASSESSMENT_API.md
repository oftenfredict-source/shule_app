# Teacher Character Assessment - Mobile API

Web page: `/teacher/character-assessment`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Main → Character Assessment

Assess assigned classes/groups for the current CA timetable period: load roster, rate categories (choice or text), save, view history.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST) |

- Teacher tokens only.
- Academic year must be selected (same as website header year). Otherwise students/save return `422` with `error_code: no_academic_year`.
- Only **Active** enrollments can be assessed (`can_assess: true`).

---

## 1. Screens (match website)

1. Period badge — `period_status`: `active` | `upcoming` | `ended` | `none`
2. Filters — Main class → subclass/group (or “All groups”)
3. Student roster — Ready / Pending / locked; Assess/Edit + History
4. Inline assessment form — categories from assignment (`choice` select or `text` textarea)
5. Save one student or Save All
6. History modal for current timetable

---

## 2. Flow

```text
GET /character-assessment
  → assignments, main_classes, categories, choices, period_status

Pick class + subclass:
  GET /character-assessment/students?classID=&subclassID=&timetableID=

Assess / edit:
  POST /character-assessment
  { assessments: [{ studentID, timetableID, categories: [...] }] }

History:
  GET /character-assessment/history?studentID=&timetableID=
```

---

## 3. Endpoints

### 3.1 Hub — `GET /character-assessment`

```json
{
  "success": true,
  "data": {
    "title": "Character Assessment",
    "period_status": "active",
    "can_assess": true,
    "main_classes": [{ "classID": 12, "class_name": "FORM ONE" }],
    "assignments": [
      {
        "assignmentID": 3,
        "timetableID": 7,
        "classID": 12,
        "subclassID": 40,
        "class_name": "FORM ONE",
        "subclass_name": "A",
        "is_all_groups": false,
        "selected_categories": [
          { "categoryID": 1, "number": 1, "name": "Discipline", "type": "choice" },
          { "categoryID": 2, "number": 2, "name": "Comment", "type": "text" }
        ],
        "timetable": {
          "timetableID": 7,
          "start_date": "2026-11-01",
          "end_date": "2026-11-30",
          "year": 2026,
          "term": 1
        }
      }
    ],
    "categories": [{ "categoryID": 1, "name": "Discipline", "type": "choice" }],
    "choices": [{ "choiceID": 5, "choice_name": "Excellent", "weight": 5 }],
    "subclasses": [{ "subclassID": 40, "classID": 12, "subclass_name": "A" }]
  }
}
```

| Field | Use |
|---|---|
| `period_status` | Header badge |
| `assignments` | Build class/subclass filter; each row has `timetableID` + `selected_categories` |
| `is_all_groups` | `subclassID` null → show all subclasses of that class |
| `selected_categories[].type` | `choice` → picker from `choices`; `text` → free text |
| `choices` | Global rating options (ordered by weight) |

If `assignments` is empty / `period_status: none` → empty state (not assigned).

---

### 3.2 Students — `GET /character-assessment/students`

| Query | Required |
|---|---|
| `classID` | yes |
| `subclassID` | optional (omit / empty = all groups for that class assignment) |
| `timetableID` | yes (from assignment) |

```json
{
  "success": true,
  "active_enrollment_count": 30,
  "students": [
    {
      "studentID": 1040,
      "full_name": "Jane Doe",
      "admission_number": "A001",
      "enrollment_status": "Active",
      "can_assess": true,
      "subclass_name": "A",
      "is_complete": true,
      "assessments": [
        { "categoryID": 1, "choiceID": 5, "description": null },
        { "categoryID": 2, "choiceID": null, "description": "Good participation" }
      ]
    }
  ]
}
```

- Prefill form from `assessments[]`.
- Disable Assess when `can_assess: false`.
- Status pill: complete if `is_complete`, else Pending (if Active), else locked.

Errors: `400` missing class; `422` `no_academic_year` / `no_active_enrollments`.

---

### 3.3 Save — `POST /character-assessment`

```json
{
  "assessments": [
    {
      "studentID": 1040,
      "timetableID": 7,
      "categories": [
        { "categoryID": 1, "choiceID": 5 },
        { "categoryID": 2, "description": "Good participation" }
      ]
    }
  ]
}
```

Send one student or many (Save All).  
For `choice` categories use `choiceID`; for `text` use `description`.

Success: `{ "success": true, "message": "Assessments saved successfully" }`  
Errors: `400` empty body; `422` `no_academic_year` / `enrollment_not_active`.

---

### 3.4 History — `GET /character-assessment/history`

Query: `studentID`, `timetableID`.

```json
{
  "success": true,
  "history": [
    {
      "categoryID": 1,
      "category_name": "Discipline",
      "category_type": "choice",
      "choiceID": 5,
      "choice_name": "Excellent",
      "choice_weight": 5,
      "description": null
    }
  ]
}
```

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Open page | `GET /character-assessment` |
| Class / group filter | Client from `assignments` / `main_classes` |
| Load roster | `GET …/students` |
| Assess form categories | `assignment.selected_categories` + `choices` |
| Save / Save All | `POST /character-assessment` |
| History | `GET …/history` |

---

## 5. Related

- Auth / academic year: `docs/TEACHER_MOBILE_API.md`
