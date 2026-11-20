# Teacher Logbook - Mobile API

Web page: `/teacher/logbook`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Teaching → Logbook

This document is the full hand-off for the **teacher’s own logbook** (subjects they teach).  
Department / HOD logbooks are covered in §7 (separate web page `/teacher/department-logbook`).

---

## 0. Rules for every call

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` (from `POST /api/v1/auth/login`) |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (for POST bodies) |

- Teacher tokens only. The school academic year must be open.
- `401` → log out. `403` → not allowed. `404` → not found. `422` → show `message`.
- Dates are `Y-m-d`.
- Only the **assigned class-subject teacher** can list/add for that subject. HOD/HM comments are **read-only** on this screen.
- There is **no edit/delete** of entries on the website (same for mobile).
- Do not render path strings as UI cards. Paths live only in this doc.

---

## 1. Screens the app needs

1. **Hub** — list subjects I teach (with class/stream label).
2. **Entries** — table for the selected subject for a year (default = current calendar year).
3. **Add entry** — form → save → refresh list.
4. **PDF** (optional on teacher page; available in API) — download year logbook for that subject.

Website columns: SN, Main topic, Sub topic, Content covered, Date started, Date ended, Teacher comment, HOD comment, HM comment.

---

## 2. How the flow works

```text
Open Logbook
  GET /logbook
      └─ pick class_subjectID

  GET /logbook/{class_subjectID}            (optional ?year=2026)
      └─ show header + entries[]

Add entry:
  POST /logbook/entry
      → { logbookID }
  then refresh GET /logbook/{class_subjectID}

PDF (optional):
  GET /logbook/{class_subjectID}/pdf
```

---

## 3. Endpoints

All paths under `/api/v1/teacher`.

### 3.1 Hub — `GET /logbook`

```json
{
  "success": true,
  "data": {
    "title": "Logbook",
    "current_year": 2026,
    "subjects": [
      {
        "class_subjectID": 1445,
        "subjectID": 175,
        "subject_name": "BIOLOGY",
        "class_name": "FORM FOUR",
        "subclass_name": "A",
        "label": "BIOLOGY - FORM FOUR A",
        "can_add_entry": true,
        "can_download_pdf": true
      }
    ]
  }
}
```

Use `label` in the subject picker. Tap → open entries with `class_subjectID`.

---

### 3.2 Entries — `GET /logbook/{classSubjectID}`

Query (optional): `year` (default = current calendar year).

```json
{
  "success": true,
  "data": {
    "class_subjectID": 1445,
    "subject_name": "BIOLOGY",
    "class_name": "FORM FOUR",
    "subclass_name": "A",
    "year": 2026,
    "teacher_name": "SIMON LAMECK",
    "can_add_entry": true,
    "can_download_pdf": true,
    "entries": [
      {
        "logbookID": 88,
        "class_subjectID": 1445,
        "main_topic": "CLASSIFICATION",
        "sub_topic": "Phylum chordata",
        "content_covered": "...",
        "date_started": "2026-09-01",
        "date_ended": "2026-09-05",
        "teacher_comment": "Lesson completed",
        "hod_comment": null,
        "hm_comment": null,
        "year": 2026,
        "status": "Active",
        "hod_signed": false,
        "hm_signed": false
      }
    ]
  }
}
```

| Flag | Meaning |
|---|---|
| `hod_signed` | HOD has left a comment |
| `hm_signed` | Headmaster has left a comment |

`404` if the subject is not yours. Empty `entries` is normal (show empty state + Add).

---

### 3.3 Add entry — `POST /logbook/entry`

```json
{
  "class_subjectID": 1445,
  "main_topic": "CLASSIFICATION",
  "sub_topic": "Phylum chordata",
  "content_covered": "Distinctive features of mammals",
  "date_started": "2026-11-17",
  "date_ended": "2026-11-17",
  "teacher_comment": "optional"
}
```

| Field | Required |
|---|---|
| `class_subjectID` | yes |
| `main_topic` | yes |
| `sub_topic` | yes |
| `content_covered` | yes |
| `date_started` | no |
| `date_ended` | no |
| `teacher_comment` | no |

Server sets `year` = current calendar year and `status` = `Active`.

```json
{
  "success": true,
  "message": "Logbook entry saved successfully",
  "logbookID": 89
}
```

`403` if you do not teach that class-subject. `422` on validation errors.

---

### 3.4 PDF — `GET /logbook/{classSubjectID}/pdf`

Returns PDF binary (`application/pdf`) for the **current calendar year**.  
Allowed for the assigned teacher **or** the HOD of that subject’s department.

`403` / `404` JSON if not allowed / not found.

Suggested filename: `Logbook_{Subject}_{Class}_{Year}.pdf`.

---

## 4. Form field map (website → API)

| Website | JSON |
|---|---|
| Main topic | `main_topic` |
| Sub topic | `sub_topic` |
| Content covered | `content_covered` |
| Date started | `date_started` |
| Date ended | `date_ended` |
| Teacher comment | `teacher_comment` |
| HOD comment | `hod_comment` (read-only) |
| HM comment | `hm_comment` (read-only) |

---

## 5. Suggested app navigation

```text
Logbook hub (subjects)
  → Subject entries (year header)
      → Add entry
      → optional PDF
```

---

## 6. What is NOT on `/teacher/logbook`

- No edit / delete of entries  
- No year filter UI on the website (API still accepts `?year=` for older years)  
- No HOD sign from this page (use Department Logbooks)  
- No “use existing” / manage / view tabs (those are Lesson Plans)

---

## 7. Department Logbooks (HOD only)

Web: `/teacher/department-logbook`  
Mobile: `GET /department-logbook` (alias `GET /teacher/department-logbook`)  
Show only when the teacher is HOD (`capabilities.is_hod`). Non-HOD → `403` with *"You are not assigned as HOD of any department."*

| Action | Method | Path |
|---|---|---|
| Hub | `GET` | `/department-logbook` (alias `/teacher/department-logbook`) |
| Streams of a class | `GET` | `/department-logbook/subclasses/{classID}` |
| Entries | `GET` | `/department-logbook/data?subjectID=&subclassID=` (optional `year`; alias `/teacher/get-department-logbook-data`) |
| HOD comment | `POST` | `/logbook/sign` body `{ "logbookID": 1, "hod_comment": "..." }` — may SMS teacher |
| PDF | `GET` | `/logbook/{classSubjectID}/pdf` (use `class_subjectID` from entries response) |

Flow: hub → pick subject + class → subclasses → entries → optional sign / PDF.

Entries response is shaped like §3.2 under `data.entries`, plus `can_sign` / `can_download_pdf`.  
Do **not** automate `POST /logbook/sign` against live data in tests (SMS).

---

## 8. Related docs

- Teaching overview: `docs/TEACHER_TEACHING_API.md`
- Auth / token: `docs/TEACHER_MOBILE_API.md`
- Lesson Plans: `docs/TEACHER_LESSON_PLANS_API.md`
