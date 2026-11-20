# Teacher Timetable - Mobile API

Web page: `/teacher_time_table`  
Mobile: `{HOST}/api/v1/teacher/timetable`  
Menu: Class → Timetable

Read-only weekly grid of the teacher’s permanent session periods (Mon–Fri).  
Interactive work (attendance, tasks, lesson plans) is on **My Sessions** — see `docs/TEACHER_SESSIONS_API.md`.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |

- Teacher tokens only.
- No create / edit / delete on this screen (same as website).

---

## 1. Screens

1. **Desktop-style grid** — rows = unique time slots, columns = Monday–Friday; cell = subject + class.
2. **Mobile list** — accordion/list per day with subject, class, time.
3. Empty / message when school has no timetable definition.

Website also has a button “Open Sessions” → navigate to Sessions (`GET /sessions`).

---

## 2. Endpoint

### `GET /timetable`

```json
{
  "success": true,
  "data": {
    "title": "My Timetable",
    "days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
    "time_slots": [
      { "start": "08:00", "end": "08:40" },
      { "start": "08:40", "end": "09:20" }
    ],
    "sessions_by_day": {
      "Monday": [
        {
          "session_timetableID": 3006,
          "day": "Monday",
          "start_time": "11:40:00",
          "end_time": "13:00:00",
          "start_time_formatted": "11:40 AM",
          "end_time_formatted": "1:00 PM",
          "subject_name": "BIOLOGY",
          "class_name": "FORM FOUR",
          "subclass_name": "A",
          "class_label": "FORM FOUR A",
          "is_prepo": false,
          "subjectID": 175,
          "class_subjectID": 1445
        }
      ],
      "Tuesday": [],
      "Wednesday": [],
      "Thursday": [],
      "Friday": []
    },
    "message": null
  }
}
```

| Field | Use |
|---|---|
| `time_slots` | Build grid rows (unique start/end) |
| `sessions_by_day[day]` | Cells for that weekday |
| `message` | Non-null when no definition — show warning, empty grid |

If a day/slot has no session, show “—” (website behaviour).

---

## 3. Related

- Weekly **interactive** sessions: `docs/TEACHER_SESSIONS_API.md` → `GET /sessions`
- Auth: `docs/TEACHER_MOBILE_API.md`
