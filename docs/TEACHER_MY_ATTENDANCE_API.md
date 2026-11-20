# Teacher My Attendance - Mobile API

Web page: `/teacher/my-attendance`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Main → My Attendance

View the logged-in teacher’s own attendance (day or month), matching the website.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |

Teacher tokens only.

---

## 1. Screens (match website)

1. Filter — Day or Month
2. Day mode — pick a date
3. Month mode — pick month + year
4. Summary / statistics cards
5. Daily records for the selected period
6. Optional PDF export

---

## 2. Flow

```text
GET /attendance
  → defaults to today (filter_type=day)

Day:
  GET /attendance?filter_type=day&date=YYYY-MM-DD

Month:
  GET /attendance?filter_type=month&month=11&year=2026

PDF:
  GET /attendance/pdf?filter_type=day&date=YYYY-MM-DD
  GET /attendance/pdf?filter_type=month&month=11&year=2026
```

---

## 3. Endpoints

### 3.1 Show — `GET /attendance`

| Query | Required | Notes |
|---|---|---|
| `filter_type` | no | `day` (default) or `month` |
| `date` | day mode | Defaults to today (`Y-m-d`) if omitted |
| `month` | month mode | `1`–`12`; defaults to current month |
| `year` | month mode | Defaults to current year |

```json
{
  "success": true,
  "data": {
    "title": "My Attendance",
    "screen_title": "My Attendance - …",
    "filter_type": "day",
    "filters": { "date": "2026-11-18", "month": null, "year": null },
    "person": { "name": "…", "employee_number": "T007" },
    "statistics": {},
    "work_start_time": "07:30",
    "work_end_time": "15:30",
    "period_incomplete": false,
    "period_label": "18 Nov 2026",
    "daily_records": [],
    "day_summary": null,
    "actions": {
      "day": "GET /attendance?filter_type=day&date=YYYY-MM-DD",
      "month": "GET /attendance?filter_type=month&month=1-12&year=YYYY",
      "pdf": "GET /attendance/pdf?filter_type=… (same query as report)"
    }
  }
}
```

`statistics`, `daily_records`, `person`, and `day_summary` follow the website JSON from `teacherMyAttendanceData` (wrapped under `data`). Treat unknown extra fields as pass-through.

---

### 3.2 PDF — `GET /attendance/pdf`

Same query params as show. Returns a PDF download (not JSON). Use the same Bearer token; save the binary response as `.pdf`.

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Open My Attendance | `GET /attendance` |
| Day filter + date | `GET /attendance?filter_type=day&date=` |
| Month filter | `GET /attendance?filter_type=month&month=&year=` |
| Export PDF | `GET /attendance/pdf?…` |

---

## 5. Related

- Auth: `docs/TEACHER_MOBILE_API.md`
