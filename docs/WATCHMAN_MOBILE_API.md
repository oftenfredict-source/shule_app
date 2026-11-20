# ShuleXpert Watchman Mobile API

Give this document to the mobile app developer. It mirrors the website Watchman gate screen (`/watchman/visitors`): register visitors for today, autocomplete previous guests, sign in / sign out.

| App screen | Website | Endpoint |
|---|---|---|
| Login | Login page | `POST /api/v1/auth/login` |
| Session / me | — | `GET /api/v1/auth/me` |
| Profile | Header name | `GET /api/v1/watchman/profile` |
| Change password | — | `POST /api/v1/auth/password` |
| Logout | Toka | `POST /api/v1/auth/logout` |
| Visitors hub (today list + form hints) | `/watchman/visitors` | `GET /api/v1/watchman/visitors` |
| Refresh today | Today table | `GET /api/v1/watchman/visitors/today` |
| Autocomplete | Name / phone search | `GET /api/v1/watchman/visitors/search?q=` |
| Save visitors | Save | `POST /api/v1/watchman/visitors` |
| Sign in | Sign In | `POST /api/v1/watchman/visitors/sign-in` |
| Sign out | Sign Out | `POST /api/v1/watchman/visitors/sign-out` |

---

## Base URL

```
{APP_URL}/api/v1
```

Every call (except login):

```
Accept: application/json
Authorization: Bearer {token}
```

`APP_URL` must be reachable from the phone (e.g. `http://192.168.100.105:5000`).

---

## Authentication

### Login

`POST /api/v1/auth/login`

Username = watchman **phone number** (`users.name`). Default password on registration = **first name** (admin may change it). Phone variants `255…`, `0…`, and 9 digits are accepted.

```json
{
  "username": "255622731325",
  "password": "watchman123",
  "device_name": "Watchman Android"
}
```

Success `200`:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { "id": 595, "name": "255622731325", "email": "…", "user_type": "Watchman" },
    "schoolID": 3,
    "watchmanID": 6,
    "school": { "schoolID": 3, "school_name": "…" },
    "watchman": {
      "id": 6,
      "first_name": "Hamid ally",
      "last_name": "Bihundwa",
      "full_name": "Hamid ally Bihundwa",
      "phone_number": "255622731325",
      "email": "…",
      "status": "Active",
      "schoolID": 3
    },
    "token": "64-character-token",
    "token_type": "Bearer",
    "expires_at": "2027-02-18T…"
  }
}
```

Errors: `401` wrong credentials, `403` inactive watchman / school suspended, `404` watchman record missing, `429` rate limit.

Token lasts 90 days (max 8 devices). Do not send `schoolID` / `watchmanID` on later calls — the server reads them from the token. `/api/v1/watchman/*` rejects non-watchman tokens with `403`.

### Me / profile / password / logout

| Endpoint | Notes |
|---|---|
| `GET /api/v1/auth/me` | `user` + `watchman` + `school` |
| `GET /api/v1/watchman/profile` | Same watchman/school payload |
| `POST /api/v1/auth/password` | `current_password`, `password`, `password_confirmation` (min 6) |
| `POST /api/v1/auth/logout` | Revokes this Bearer token |

---

## Screens (match website)

1. Header — watchman name + school; Logout  
2. Today table — visitors already saved today (readonly) with Sign In / Sign Out  
3. New row(s) — Name (required), Contact (default `255`), Occupation, Reason, Office, Signature (PNG data-URL)  
4. Autocomplete — type name (≥2) or phone digits (≥4 on web; API accepts `q` ≥ 2)  
5. Save — one or many new visitors; each is auto signed-in for today  
6. Sign out when the guest leaves; Sign in only if a row somehow has no sign-in yet  

Admin-only extras (date-range manage, edit/delete, SMS blast) are **not** on this API.

---

## Endpoints

### Hub — `GET /watchman/visitors`

```json
{
  "success": true,
  "data": {
    "title": "Usajili wa Wageni wa Shule",
    "screen": "visitors",
    "date": "2026-11-18",
    "watchman": { "id": 6, "full_name": "…", "phone_number": "…" },
    "summary": { "total_today": 3, "on_site": 1, "completed": 2 },
    "visitors": [
      {
        "visitorID": 101,
        "visitDate": "2026-11-18",
        "name": "Jane Guest",
        "contact": "2557…",
        "occupation": "Parent",
        "reason": "Meeting",
        "officeVisited": "Headmaster",
        "signedInAt": "2026-11-18 08:15",
        "signedOutAt": null,
        "signature": "data:image/png;base64,…"
      }
    ],
    "fields": [ /* form field hints */ ],
    "actions": { /* endpoint hints */ }
  }
}
```

Status pills: **on site** = `signedInAt` set and `signedOutAt` null; **completed** = both set.

### Today — `GET /watchman/visitors/today`

Same visitor objects under `{ "success": true, "data": [ … ] }`.

### Search — `GET /watchman/visitors/search?q=`

`q` shorter than 2 → empty `data`. Returns up to 10 unique past guests:

```json
{
  "success": true,
  "data": [
    { "name": "Jane Guest", "contact": "2557…", "occupation": "Parent", "officeVisited": "Headmaster" }
  ]
}
```

Prefill new-row fields from a pick.

### Save — `POST /watchman/visitors`

**Preferred (mobile):**

```json
{
  "visitors": [
    {
      "name": "Jane Guest",
      "contact": "255712345678",
      "occupation": "Parent",
      "reason": "Meeting",
      "office_visited": "Headmaster",
      "signature": "data:image/png;base64,…"
    }
  ]
}
```

**Also accepted (website FormData style):** parallel arrays `name[]`, `contact[]`, `occupation[]`, `reason[]`, `office_visited[]`, `signature[]`.

Empty names are skipped. At least one non-empty name required (`422` otherwise).  
New rows get today’s date and are auto signed-in. School SMS may run in the background; message text can mention SMS status.

Success: `{ "success": true, "message": "Visitors recorded successfully. …" }`

### Sign in — `POST /watchman/visitors/sign-in`

```json
{ "visitorID": 101, "office_visited": "Headmaster" }
```

`office_visited` optional. Errors if already signed in/out.

### Sign out — `POST /watchman/visitors/sign-out`

```json
{ "visitorID": 101 }
```

Errors if not signed in or already signed out.

Both return `{ "success": true, "message": "…", "data": { /* visitor payload */ } }`.

---

## Website UI → API map

| Website | API |
|---|---|
| Open `/watchman/visitors` | `GET /watchman/visitors` |
| Reload today table | `GET /watchman/visitors/today` |
| Autocomplete name/phone | `GET /watchman/visitors/search?q=` |
| Save new rows | `POST /watchman/visitors` |
| Sign In button | `POST /watchman/visitors/sign-in` |
| Sign Out button | `POST /watchman/visitors/sign-out` |
| Logout | `POST /auth/logout` |

---

## Notes for the app team

- One main screen; no admin manage tabs.
- Signature is optional; send a PNG data-URL string when the user draws one.
- After Save, refresh with `GET …/today` or re-open the hub.
- `401` on later calls → token invalid → return to login. `403` → wrong role / inactive / school suspended.
