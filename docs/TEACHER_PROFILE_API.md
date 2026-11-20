# Teacher Profile - Mobile API

Web page: `/teacher/profile`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Account → Profile

Everything on the website profile: identity + photo, TANGE score, personal / employment / service timeline, edit profile (full TANGE), download TANGE PDF, change password. Signature endpoints are included (same account).

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` or `multipart/form-data` for file uploads |

- Teacher tokens only.
- Phone: `255` + 9 digits (e.g. `255614863345`).
- Dates: `Y-m-d`.
- `GET /profile` and `POST /profile/update` / `POST /password/change` remain available even when `password_change_required` is forced.

---

## 1. Screens (match website)

1. **Identity band** — photo, name, C/N, position, status, TANGE %, HR retirement alert.
2. **Personal info** — sex, age, DOB, national ID, phone, email, residence, status.
3. **Employment info** — C/N, TSC, rank, salary scale, position, station, education, experience, subjects.
4. **Service timeline** — hired / confirmed / current rank / retirement dates.
5. **Edit Profile** modal — same fields as web (including TANGE section).
6. **Change photo** — tap avatar (quick upload) or include `image` in edit form.
7. **Download TANGE PDF**.
8. **Account security** — change password.

---

## 2. Flow

```text
GET /profile
  → identity, tange, personal, employment, service_timeline, account, editable_fields

Change photo:
  POST /profile/image   multipart field teacher_image

Edit profile:
  POST /profile/update  multipart or JSON (same fields as web)

Download TANGE:
  GET /profile/pdf      → application/pdf

Change password:
  POST /password/change

Signature (account):
  POST /signature
  POST /signature/delete
```

---

## 3. Endpoints

### 3.1 Hub — `GET /profile`

```json
{
  "success": true,
  "data": {
    "title": "My Profile",
    "identity": {
      "teacher_id": 4,
      "full_name": "JANE DOE",
      "first_name": "JANE",
      "middle_name": null,
      "last_name": "DOE",
      "employee_number": "T123",
      "position": "Teacher",
      "status": "Active",
      "image_url": "http://…/userImages/….jpg",
      "has_custom_image": true
    },
    "tange": {
      "completion_percent": 85,
      "is_complete": false,
      "missing_fields": ["TSC Number (TSC/N)"],
      "retirement_alert": null
    },
    "personal": { "gender": "Female", "age": 34, "date_of_birth": "1992-01-10", "national_id": "…", "phone_number": "2557…", "email": "…", "address": "…", "bank_account_number": null, "status": "Active" },
    "employment": { "employee_number": "T123", "tsc_number": "…", "establishment_rank": "…", "salary_scale": "…", "position": "…", "work_station": "…", "qualification": "…", "years_of_experience": 8, "experience": "8 years", "specialization": "…", "subjects_teaching": "…" },
    "service_timeline": {
      "date_hired": "2018-01-01",
      "date_confirmed": "2019-01-01",
      "date_current_rank": "2022-01-01",
      "date_of_retirement": "2048-01-01"
    },
    "account": {
      "username": "T123",
      "has_user_account": true,
      "has_signature": false,
      "signature_url": null
    },
    "editable_fields": ["first_name", "middle_name", "last_name", "gender", "phone_number", "email", "national_id", "bank_account_number", "employee_number", "tsc_number", "…", "image"],
    "actions": {
      "update": "POST /profile/update",
      "update_photo": "POST /profile/image",
      "download_tange_pdf": "GET /profile/pdf",
      "change_password": "POST /password/change"
    }
  }
}
```

| Field | Use |
|---|---|
| `tange.completion_percent` | Ring / progress |
| `tange.missing_fields` | List what to complete in Edit |
| `tange.retirement_alert` | `{ level, message, days }` or `null` — show HR banner |
| `editable_fields` | Build the edit form |

---

### 3.2 Update profile — `POST /profile/update`

Same as website **Edit Profile** (`update_teacher` + TANGE).  
Prefer `multipart/form-data` when uploading `image`.

**Required**

| Field | Notes |
|---|---|
| `first_name` | |
| `last_name` | |
| `gender` | `Male` \| `Female` |
| `phone_number` | `255#########` |
| `email` | unique among teachers |
| `national_id` | unique |
| `employee_number` | optional in request — server fills from session if omitted |

**Optional / TANGE**

`middle_name`, `bank_account_number`, `tsc_number`, `establishment_rank`, `salary_scale`, `position`, `work_station`, `date_of_birth`, `date_hired`, `date_confirmed`, `date_current_rank`, `date_of_retirement`, `qualification`, `specialization`, `subjects_teaching`, `address`, `image` (jpg/png ≤2MB).

`teacher_id` and `status` are auto-filled from the logged-in teacher (do not spoof another id).

Success: `{ "success": "Teacher updated successfully!" }`  
Validation: `422` with `errors` map.

---

### 3.3 Quick photo — `POST /profile/image`

```http
Content-Type: multipart/form-data
```

| Field | Required |
|---|---|
| `teacher_image` | yes — jpeg/png/jpg/gif ≤2MB |

```json
{ "success": true, "message": "Profile image updated successfully", "image_url": "http://…/userImages/…" }
```

---

### 3.4 TANGE PDF — `GET /profile/pdf`

Returns PDF binary (`application/pdf`).  
Suggested name: `TANGE_{Name}_{YmdHis}.pdf`.

---

### 3.5 Change password — `POST /password/change`

```json
{
  "current_password": "OldPass1!",
  "new_password": "NewPass1!",
  "new_password_confirmation": "NewPass1!"
}
```

Also accepts `confirm_password` instead of `new_password_confirmation`.

Rules (same as website):
- min **8** characters  
- at least one uppercase, lowercase, number, special character  
- must differ from current password  

---

### 3.6 Signature (account, related)

| Action | Method | Path | Body |
|---|---|---|---|
| Upload | `POST` | `/signature` | multipart `signature` (jpg/png/gif/webp/svg ≤5MB) |
| Delete | `POST` | `/signature/delete` | — |

Hub shows `account.has_signature` / `signature_url`.

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Open Profile | `GET /profile` |
| Tap photo / Change photo | `POST /profile/image` |
| Edit Profile → Save | `POST /profile/update` |
| Download TANGE | `GET /profile/pdf` |
| Update password | `POST /password/change` |
| TANGE % / missing | `data.tange` |
| Retirement banner | `data.tange.retirement_alert` |

---

## 5. Related

- Auth / forced password change: `docs/TEACHER_MOBILE_API.md`
- Login password helper: `POST /api/v1/auth/password`
