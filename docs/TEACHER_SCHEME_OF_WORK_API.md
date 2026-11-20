# Teacher Scheme of Work - Mobile API

Web pages under `/teacher/scheme-of-work/*`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Teaching → Scheme of Work

This document is the full hand-off for the mobile app. It covers every screen and action the website supports for Scheme of Work.

---

## 0. Rules for every call

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` (from `POST /api/v1/auth/login`) |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (for POST / DELETE bodies) |

- Teacher tokens only. The school academic year must be open.
- `401` → log the user out. `403` → not allowed for this teacher. `400`/`422` → show `message`.
- Month names are English full names: `January` … `December` (same as the website).
- A teacher can only create/manage schemes for subjects where they are the **assigned class-subject teacher**.
- Access to view/manage/update/delete/export: the teacher who **created** the scheme **or** the assigned teacher of that class-subject.
- Some older success payloads still include website `redirect` / `manageUrl`. Ignore those. Use `scheme_of_workID` and the paths in this doc.

---

## 1. Screens the app needs

1. **Hub** — list my subjects for a year; each shows whether a scheme exists and which buttons to show (Create / View / Manage / Use existing).
2. **Create** — blank scheme form for one subject: learning objectives + monthly rows. Holidays shown as non-editable markers.
3. **Use existing** — pick another scheme for the same subject (any year/stream) and copy it into my subject for the current year.
4. **View** — read-only scheme (objectives + rows). Download PDF/Excel. Button to open Manage.
5. **Manage** — editable scheme: edit objectives/rows, add/remove rows, mark row Done, save, delete, export.

---

## 2. How the flow works

```text
Open Scheme of Work
  GET /scheme-of-work?year=2026
      └─ for each subject look at actions.*

If has_scheme = false
  ├─ Create new
  │    GET  /scheme-of-work/create/{classSubjectID}   → holidays + subject header
  │    POST /scheme-of-work                           → creates scheme (status Draft)
  │    then open Manage with the new scheme_of_workID
  └─ Use existing
       GET  /scheme-of-work/existing/{classSubjectID} → list of schemes to copy
       POST /scheme-of-work/{sourceId}/use            → { class_subjectID }
       then open Manage with the new scheme_of_workID

If has_scheme = true
  ├─ View   GET /scheme-of-work/{id}
  └─ Manage GET /scheme-of-work/{id}/manage
       ├─ Save all changes     POST …/{id}/update
       ├─ Mark row done/undo   POST …/{id}/remark
       ├─ Delete scheme        DELETE …/{id}
       ├─ PDF                  GET …/{id}/pdf
       └─ Excel                GET …/{id}/excel
```

Optional helper (website uses it before navigating):  
`POST /scheme-of-work/check-existing` with `{ "class_subjectID": 1445, "year": 2026 }`  
→ `{ "exists": true/false, "scheme": { "id", "status", "created_by" } | null }`  
The hub already includes this info, so the app does not have to call check-existing first.

---

## 3. Endpoints

All paths below are under `/api/v1/teacher`.

### 3.1 Hub — `GET /scheme-of-work`

Query (optional): `year` (default = current calendar year).

```json
{
  "success": true,
  "data": {
    "year": 2026,
    "subjects": [
      {
        "class_subjectID": 1445,
        "subjectID": 120,
        "subject_name": "Kiswahili",
        "class_name": "FORM THREE",
        "year": 2026,
        "has_scheme": true,
        "scheme": {
          "scheme_of_workID": 19,
          "status": "Draft",
          "created_by": 4365
        },
        "actions": {
          "can_create": false,
          "can_view": true,
          "can_manage": true,
          "can_use_existing": false
        }
      }
    ]
  }
}
```

How to use `actions`:

| Flag | Show button | Next call |
|---|---|---|
| `can_create` | Create | create form → `POST /scheme-of-work` |
| `can_use_existing` | Use existing | existing list → `POST …/use` |
| `can_view` | View | `GET /scheme-of-work/{scheme_of_workID}` |
| `can_manage` | Manage | `GET /scheme-of-work/{scheme_of_workID}/manage` |

If `has_scheme` is true, `can_create` and `can_use_existing` are false (one scheme per subject per year).

---

### 3.2 Create form data — `GET /scheme-of-work/create/{classSubjectID}`

Returns the website create-page data (bridged). Use it for:
- subject / class header
- `currentYear`
- `allHolidays` (holidays + non-working events) so the form can show blocked days by month

If the teacher does not teach that subject, or a scheme already exists for the year → `403` / `422` with a message.

Then create with:

### 3.3 Create — `POST /scheme-of-work`

```json
{
  "class_subjectID": 1445,
  "year": 2026,
  "learning_objectives": ["Students can …"],
  "items": [
    {
      "month": "January",
      "row_order": 1,
      "main_competence": "…",
      "specific_competences": "…",
      "learning_activities": "…",
      "specific_activities": "…",
      "week": "1",
      "number_of_periods": 4,
      "teaching_methods": "",
      "teaching_resources": "",
      "assessment_tools": "",
      "references": ""
    }
  ]
}
```

| Field | Required | Notes |
|---|---|---|
| `class_subjectID` | yes | Must be one of the teacher's subjects |
| `year` | no | Defaults to current calendar year |
| `learning_objectives` | no | Array of strings; empty strings ignored |
| `items` | no | Only rows with a non-empty `month` are saved |
| `items[].month` | yes (per row) | Full English month name |
| other item fields | no | Strings / numbers as above |

Success: `{ "success": true, "message": "…", … }`.  
After create, open **Manage** with the new `scheme_of_workID` (if the response includes a website redirect, ignore it and use the id from a fresh `GET /scheme-of-work` or from the create response when present).

Errors:
- `400` already exists for that subject+year
- `403` subject not yours

---

### 3.4 Use an existing scheme

**List** — `GET /scheme-of-work/existing/{classSubjectID}`  
Returns schemes for the same subject (any year / stream), including archived history where available. Bridged page data; look for the schemes list and each scheme's `scheme_of_workID`.

**Copy into my subject** — `POST /scheme-of-work/{sourceSchemeOfWorkID}/use`

```json
{ "class_subjectID": 1445 }
```

- `class_subjectID` must be a subject **you teach**.
- Fails if you already have a scheme for that subject in the current year (`400`, includes `scheme_of_workID` of the existing one).
- On success, a new Draft scheme is created for your subject; open Manage with the new id.

---

### 3.5 View — `GET /scheme-of-work/{schemeOfWorkID}`

Same content as website `/teacher/scheme-of-work/view/{id}`. Read-only.  
**Do not render nested objects blindly.** Use only these fields:

```json
{
  "success": true,
  "data": {
    "scheme_of_workID": 19,
    "class_subjectID": 1445,
    "school_name": "UROKI HIGH SCHOOL",
    "teacher_name": "ZULUHIJA SWAI",
    "subject": "KISWAHILI",
    "year": 2026,
    "class": "FORM THREE",
    "learning_objectives_title": "Learning Objectives",
    "learning_objectives": ["huuuuj"],
    "table": [
      {
        "row_number": 1,
        "type": "item",
        "itemID": 41,
        "main_competence": "yuggg",
        "specific_competences": "fvg",
        "learning_activities": "gvh",
        "specific_activities": "",
        "month": "January",
        "week": "1",
        "number_of_periods": 4,
        "teaching_methods": "",
        "teaching_resources": "",
        "assessment_tools": "",
        "references": "",
        "remarks": "",
        "is_done": false
      }
    ]
  }
}
```

How to paint the View screen (match the website):

1. Header lines  
   - NAME OF SCHOOL: `school_name`  
   - TEACHER'S NAME: `teacher_name`  
   - SUBJECT: `subject`  
   - YEAR: `year`  
   - CLASS: `class`
2. Section title: `learning_objectives_title`  
   List each string in `learning_objectives` (e.g. `huuuuj`).
3. **Scheme of Work Table** — one row per entry in `table` (`type = "item"`). Columns:

| # | Main Competence | Specific Competences | Learning Activities | Specific Activities | Month | Week | Number of Periods | Teaching and Learning Methods | Teaching and Learning Resources | Assessment Tools | References | Remarks |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| `row_number` | `main_competence` | `specific_competences` | `learning_activities` | `specific_activities` | `month` | `week` | `number_of_periods` | `teaching_methods` | `teaching_resources` | `assessment_tools` | `references` | tick if `is_done` |

Toolbar buttons (fixed paths, not returned in JSON):
- Manage → `GET /scheme-of-work/{scheme_of_workID}/manage`
- PDF → `GET /scheme-of-work/{scheme_of_workID}/pdf`
- Excel → `GET /scheme-of-work/{scheme_of_workID}/excel`

---

### 3.6 Manage — `GET /scheme-of-work/{schemeOfWorkID}/manage`

Same content as website `/teacher/scheme-of-work/manage/{id}`.

```json
{
  "success": true,
  "data": {
    "title": "Manage Scheme of Work",
    "scheme_of_workID": 19,
    "class_subjectID": 1445,
    "subject": "Kiswahili",
    "class": "FORM THREE",
    "year": 2026,
    "learning_objectives_title": "Define Learning Objectives",
    "learning_objectives": [
      { "objectiveID": 15, "text": "huuuuj" }
    ],
    "table": [
      {
        "row_number": 1,
        "type": "item",
        "itemID": 41,
        "main_competence": "yuggg",
        "specific_competences": "fvg",
        "learning_activities": "gvh",
        "specific_activities": "",
        "month": "January",
        "week": "1",
        "number_of_periods": 4,
        "teaching_methods": "",
        "teaching_resources": "",
        "assessment_tools": "",
        "references": "",
        "remarks": "",
        "is_done": false
      },
      {
        "type": "holiday",
        "month": "January",
        "label": "OPENNING STAFF MEETING (11.1.2026)",
        "name": "OPENNING STAFF MEETING",
        "start_date": "2026-01-11",
        "end_date": "2026-01-11"
      },
      {
        "row_number": 2,
        "type": "empty_month",
        "itemID": null,
        "month": "February",
        "main_competence": "",
        "specific_competences": "",
        "learning_activities": "",
        "specific_activities": "",
        "week": "",
        "number_of_periods": "",
        "teaching_methods": "",
        "teaching_resources": "",
        "assessment_tools": "",
        "references": "",
        "remarks": "",
        "is_done": false
      }
    ]
  }
}
```

How to paint Manage:
1. Title: `title`  
   Subtitle: `Subject: {subject} | Class: {class} | Year: {year}`
2. **Define Learning Objectives** — editable list from `learning_objectives[].text`
3. **Scheme of Work Table** — walk `table` in order:
   - `type = "item"` → editable row (`itemID` present)
   - `type = "empty_month"` → empty editable placeholder for that month
   - `type = "holiday"` → read-only label row (`label`), not saved
4. Done checkbox on `item` rows → `POST …/remark` immediately
5. Save → `POST …/update` with all objectives + all `item` / filled `empty_month` rows  
6. Delete → `DELETE …/{id}`  
7. PDF / Excel → same paths as View

Do **not** display API metadata, paths, or `can_*` flags. Those are not in this response.

---

### 3.7 Save changes — `POST /scheme-of-work/{schemeOfWorkID}/update`

```json
{
  "learning_objectives": ["Objective 1", "Objective 2"],
  "items": [
    {
      "itemID": 41,
      "month": "January",
      "main_competence": "…",
      "specific_competences": "…",
      "learning_activities": "…",
      "specific_activities": "",
      "week": "1",
      "number_of_periods": 4,
      "teaching_methods": "",
      "teaching_resources": "",
      "assessment_tools": "",
      "references": ""
    },
    {
      "month": "February",
      "main_competence": "New row",
      "specific_competences": "",
      "learning_activities": "",
      "specific_activities": "",
      "week": "1",
      "number_of_periods": 3,
      "teaching_methods": "",
      "teaching_resources": "",
      "assessment_tools": "",
      "references": ""
    }
  ]
}
```

Rules (important):
- If `learning_objectives` is sent, the old objectives are replaced with this list.
- If `items` is sent:
  - row with `itemID` → update that row
  - row without `itemID` → create a new row (must have `month`)
  - any existing DB row whose `itemID` is **missing** from the array is **deleted**
- Do **not** put Done/remarks in this payload. Use the remark endpoint.
- Success: `{ "success": true, "message": "Scheme of work updated successfully" }`
- Not yours / not found → `403` / `404`

After a successful save, reload Manage (`GET …/manage`) so new rows get their `itemID`s.

---

### 3.8 Mark row Done / undo — `POST /scheme-of-work/{schemeOfWorkID}/remark`

```json
{ "item_id": 41, "remark": "done" }
```

Undo:

```json
{ "item_id": 41, "remark": "" }
```

Call this when the user taps the Done checkbox. Do not wait for Save.  
Success: `{ "success": true, "message": "Remarks updated successfully" }`.

---

### 3.9 Delete — `DELETE /scheme-of-work/{schemeOfWorkID}`

No body. Deletes the scheme and its objectives/items.  
Then go back to the hub.  
Success includes a website `redirect` — ignore it.

---

### 3.10 PDF / Excel

| File | Call |
|---|---|
| PDF | `GET /scheme-of-work/{schemeOfWorkID}/pdf` |
| Excel | `GET /scheme-of-work/{schemeOfWorkID}/excel` |

Both return file bytes (`application/pdf` or Excel). Send the Bearer token.  
Suggested names: `Scheme_of_Work_19.pdf`, `Scheme_of_Work_19.xlsx`.  
Not yours → `403`.

---

## 4. Field reference (table row)

| Field | Type | Meaning |
|---|---|---|
| `row_number` | int | `#` column |
| `type` | string | `item` \| `empty_month` \| `holiday` |
| `itemID` | int\|null | Existing row id (`null` for empty months) |
| `month` | string | `January` … `December` |
| `main_competence` | string | Main Competence |
| `specific_competences` | string | Specific Competences |
| `learning_activities` | string | Learning Activities |
| `specific_activities` | string | Specific Activities |
| `week` | string | Week |
| `number_of_periods` | int\|string | Number of Periods |
| `teaching_methods` | string | Teaching and Learning Methods |
| `teaching_resources` | string | Teaching and Learning Resources |
| `assessment_tools` | string | Assessment Tools |
| `references` | string | References |
| `remarks` | string | Usually `""` or `"done"` |
| `is_done` | bool | Show ✓ when true |
| `label` | string | Holiday display text only (`type=holiday`) |

Learning objectives:
- View: array of strings, e.g. `["huuuuj"]`
- Manage: array of `{ "objectiveID": 15, "text": "huuuuj" }`

---

## 5. Errors the app should handle

| Situation | Typical response |
|---|---|
| Not logged in | `401` |
| Scheme / subject not yours | `403` + message |
| Scheme not found | `404` or redirect→`422` with message |
| Already have a scheme for subject+year | `400` |
| Trying to use existing into a subject you don't teach | `403` |
| Validation / bad input | `400` / `422` + `message` |

Always check `success` (or HTTP status). Show `message` to the user.

---

## 6. App checklist

1. Hub from `GET /scheme-of-work` — drive buttons from `actions`.
2. Create only when `can_create`; load holidays from create endpoint; POST create; then Manage.
3. Use existing only when `can_use_existing`; copy with `POST …/use`; then Manage.
4. **View** paints only: school/teacher/subject/year/class + `learning_objectives` + `table` columns. No metadata cards.
5. **Manage** paints title + subject/class/year + objectives + `table` (`item` / `empty_month` / `holiday`).
6. Save sends full `items` list; missing ids = deleted.
7. Done checkbox uses remark endpoint immediately.
8. PDF/Excel are authenticated downloads with known paths.
9. Ignore website `redirect` / `manageUrl` fields.

---

## 7. Verified

Scheme `19` (Kiswahili, FORM THREE) checked live:
- View returns school/teacher/subject/year/class, `learning_objectives: ["huuuuj"]`, and table row `#1 yuggg / fvg / gvh / January / 1 / 4`
- Manage returns the same table content plus empty months and holiday labels (`OPENNING STAFF MEETING (11.1.2026)`, etc.)
- No `actions` / path metadata in view or manage responses
