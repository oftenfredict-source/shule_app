# ShuleXpert Board Member Mobile API

Give this document to the mobile app developer. It mirrors the website board portal (`/board-portal`) feature by feature: same data, same permission rules, same actions.

| App screen | Website page | Endpoint |
|---|---|---|
| Login | Login page | `POST /api/v1/auth/login` |
| Session / profile | (header) | `GET /api/v1/auth/me` or `GET /api/v1/board/profile` |
| Change password | - | `POST /api/v1/auth/password` |
| Logout | Logout | `POST /api/v1/auth/logout` |
| Dashboard | `/board-portal` | `GET /api/v1/board/dashboard` |
| Messages (inbox / sent) | `/board-portal/messages` | `GET /api/v1/board/messages` |
| Open message + thread | `/board-portal/messages?id=` | `GET /api/v1/board/messages/{messageID}` |
| Compose message | Messages > Compose | `POST /api/v1/board/messages/compose` |
| Reply | Messages > Reply | `POST /api/v1/board/messages/reply` |
| Mark message read | - | `POST /api/v1/board/messages/mark-read` |
| Members directory | `/board-portal/members` | `GET /api/v1/board/members` |
| SMS to members | Members > Send SMS | `POST /api/v1/board/members/message` |
| Committees | `/board-portal/committees` | `GET /api/v1/board/committees` |
| Create committee | Committees > New | `POST /api/v1/board/committees` |
| Meetings list | `/board-portal/meetings` | `GET /api/v1/board/meetings` |
| Meeting detail + comments | `/board-portal/meetings?id=` | `GET /api/v1/board/meetings/{meetingID}` |
| Schedule meeting | Meetings > Schedule | `POST /api/v1/board/meetings` |
| Comment on meeting | Meeting > Comment | `POST /api/v1/board/meetings/comment` |
| Minutes | `/board-portal/minutes` | `GET /api/v1/board/minutes` |
| Record minutes | Minutes > New | `POST /api/v1/board/minutes` |
| Resolutions | `/board-portal/resolutions` | `GET /api/v1/board/resolutions` |
| Record resolution | Resolutions > New | `POST /api/v1/board/resolutions` |
| Action items | `/board-portal/action-items` | `GET /api/v1/board/action-items` |
| Create action item | Action items > New | `POST /api/v1/board/action-items` |
| Update action item status | Action items > status | `POST /api/v1/board/action-items/status` |
| Documents | `/board-portal/documents` | `GET /api/v1/board/documents` |
| Upload document | Documents > Upload | `POST /api/v1/board/documents` |
| Download document | Documents > Download | `GET /api/v1/board/documents/{documentID}/download` |

## Base URL

```
{APP_URL}/api/v1
```

Send on every call:

```
Accept: application/json
Authorization: Bearer {token}      (everything except login)
```

`APP_URL` in the server `.env` must be the address the phone uses. Download links in the JSON are built from it.

## Authentication

### Login

`POST /api/v1/auth/login`

Username is the board member's login email. The first password is the member's last name in capital letters (the school admin can generate a new one from the School Board page, which also sends it by SMS).

```json
{
  "username": "member@example.com",
  "password": "SURNAME",
  "device_name": "Board Android"
}
```

Success `200`:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { "id": 9385, "name": "member@example.com", "email": "member@example.com", "user_type": "BoardMember" },
    "schoolID": 5,
    "boardMemberID": 2,
    "school": { "schoolID": 5, "school_name": "Example School" },
    "board_member": {
      "memberID": 2,
      "full_name": "Often Fred",
      "email": "member@example.com",
      "phone": "07XXXXXXXX",
      "position_code": "CHAIR",
      "position_name": "Chairperson"
    },
    "token": "64-character-token",
    "token_type": "Bearer",
    "expires_at": "2027-02-14T11:46:52+03:00"
  }
}
```

Errors: `401` wrong username or password, `403` member inactive or school suspended, `404` board member record missing, `429` too many attempts.

The token lasts 90 days and allows up to 8 devices per user. Do not send `schoolID` or `memberID` from the app; the server reads them from the token. `/api/v1/board/*` rejects tokens that belong to other account types with `403`.

### Profile

`GET /api/v1/board/profile` returns:

```json
{
  "success": true,
  "data": {
    "member": {
      "memberID": 2, "schoolID": 5, "full_name": "Often Fred", "email": "...", "phone": "...",
      "position_code": "CHAIR", "position_name": "Chairperson",
      "appointed_date": "2026-01-01", "term_end_date": null, "status": "Active",
      "committees": [{ "committeeID": 1, "committee_name": "Finance", "committee_role": "Chair" }]
    },
    "school": { "schoolID": 5, "school_name": "Example School" },
    "capabilities": { "can_view_messages": true, "can_manage_meetings": true, "menu": [{ "key": "dashboard", "label": "Dashboard" }] },
    "unread_messages": 0,
    "token_expires_at": "2027-02-14T11:46:52+03:00"
  }
}
```

### Permissions (important)

What a member sees depends on the board roles the admin assigned, the same as the website. Read `data.capabilities` after login and build the app menu from `capabilities.menu`. The website hides pages the member cannot use; the API returns `403` with a `message` for them.

Main flags:

| Flag | Meaning |
|---|---|
| `can_view_messages`, `can_compose_messages` | Messages screen, compose button |
| `can_view_members`, `can_message_members` | Members directory, send SMS |
| `can_view_committees`, `can_manage_committees` | Committees list, create committee |
| `can_view_meetings`, `can_manage_meetings`, `can_comment_meetings` | Meetings, schedule, comment |
| `can_view_minutes`, `can_manage_minutes` | Minutes, record minutes |
| `can_view_resolutions`, `can_manage_resolutions` | Resolutions, record resolution |
| `can_view_action_items`, `can_manage_action_items` | Action items, create |
| `can_update_own_actions`, `can_update_any_actions` | Change action status |
| `can_view_documents`, `can_download_documents`, `can_manage_documents` | Documents, download, upload |
| `see_all_meetings`, `see_all_minutes`, `see_all_resolutions`, `see_all_action_items` | Full list vs only the member's own |

List responses also carry `can_manage` / `can_upload` / `can_compose` for the current screen, plus an `options` object (only when the member can create) with the values for the create form: statuses, committees, board members, eligible meetings.

## Chairperson

A chairperson (position code `CHAIR`, or `VICE_CHAIR` for the vice chair) uses the same endpoints as every board member. Their extra access comes from the permissions on their board roles, plus the position fallback below.

How access is decided:
- If the member has board roles, the `can_*` flags come from the role permissions. `is_chair` and `is_officer` still come from the position.
- If the member has no roles, the flags come from the position code. A chairperson then gets the full set listed below.

What a chairperson can do with the same endpoints:

| Area | Access | Endpoints |
|---|---|---|
| Members | Directory, compose, SMS | `GET /board/members`, `POST /board/members/message` |
| Messages | Inbox, compose, reply | `/board/messages/*` |
| Committees | List and create | `GET` and `POST /board/committees` |
| Meetings | Sees all meetings. Schedules them, comments on any, and is the default chairperson for new meetings | `GET /board/meetings`, `POST /board/meetings`, `POST /board/meetings/comment` |
| Minutes | Sees all minutes, including drafts. Records and updates them | `GET` and `POST /board/minutes` |
| Resolutions | Sees all resolutions. Records them | `GET` and `POST /board/resolutions` |
| Action items | Sees all items, creates them, and updates any item's status | `GET` and `POST /board/action-items`, `POST /board/action-items/status` |
| Documents | Sees all documents, uploads and downloads | `GET` and `POST /board/documents`, `GET /board/documents/{id}/download` |
| Dashboard | Shows pending minutes (Draft, Submitted, Pending Approval) | `GET /board/dashboard` |

The same rule applies as for any member: minutes, resolutions and action items can be recorded only for meetings that are Ongoing or Completed.

## Response format

Success: `{ "success": true, "data": { ... } }` for reads, `{ "success": true, "message": "..." }` for actions (some add an ID, for example `messageID`).

Errors:

| Status | Body |
|---|---|
| `401` | `{ "success": false, "message": "..." }` missing / expired token or inactive member |
| `403` | `{ "success": false, "message": "You do not have permission for this action." }` |
| `404` | `{ "message": "..." }` unknown ID (reads return `success:false` too) |
| `422` | Validation: `{ "message": "...", "errors": { "field": ["..."] } }`, or `{ "success": false, "message": "..." }` for business rules |

Dates are `Y-m-d`, times `H:i`, date-times `Y-m-d H:i:s`.

## Dashboard

`GET /api/v1/board/dashboard`

```json
{
  "success": true,
  "data": {
    "title": "Board Member Dashboard",
    "subtitle": "Lead the board - ...",
    "stats": { "upcoming_meetings": 2, "open_actions": 3, "my_actions": 1, "documents": 4, "committees": 2 },
    "unread_messages": 1,
    "upcoming_meetings": [ /* meeting objects */ ],
    "my_action_items": [ /* action item objects */ ],
    "pending_minutes": [ /* minutes in Draft / Submitted / Pending Approval, officers only */ ],
    "recent_resolutions": [ /* resolution objects */ ]
  }
}
```

Meetings shown: all for officers (`see_all_meetings`), otherwise only those where the member is chair or attendee.

## Messages (in-app)

These are in-app board messages, separate from SMS.

### List

`GET /api/v1/board/messages` (needs `can_view_messages`)

```json
{
  "success": true,
  "data": {
    "can_compose": true,
    "unread": 1,
    "inbox": [
      { "messageID": 7, "parent_messageID": null, "subject": "Budget", "preview": "First 120 chars...",
        "sender": { "memberID": 2, "full_name": "Often Fred", "position_name": "Chairperson", "position_code": "CHAIR" },
        "audience": "selected", "recipient_count": 1, "created_at": "2026-11-16 10:00:00",
        "is_read": false, "read_at": null }
    ],
    "sent": [ { "messageID": 8, "subject": "...", "preview": "...", "recipients_count": 3, "created_at": "..." } ],
    "options": { "board_members": [ { "memberID": 3, "full_name": "...", "position_name": "...", "position_code": "..." } ] }
  }
}
```

Up to 100 per list, newest first. `options` appears only when `can_compose` is true.

### Open a message

`GET /api/v1/board/messages/{messageID}`

Opening an inbox message marks it read, like the website.

```json
{
  "success": true,
  "data": {
    "box": "inbox",
    "message": { "messageID": 7, "subject": "Budget", "body": "Full text", "sender": { }, "created_at": "..." },
    "can_reply": true,
    "thread": [ /* earlier / later replies in the same conversation, oldest first, each with body */ ],
    "unread": 0
  }
}
```

`box` is `inbox` or `sent`. For `sent` messages `message.recipients[]` lists each recipient with `is_read`. `404` if the message is not the member's.

### Compose

`POST /api/v1/board/messages/compose` (needs `can_compose_messages`)

| Field | Required | Notes |
|---|---|---|
| `subject` | Yes | max 200 |
| `body` | Yes | 2 - 10000 |
| `send_to` | Yes | `all` or `selected` |
| `member_ids[]` | If `selected` | member IDs from `options.board_members` |

Response: `{ "success": true, "message": "Message sent to 2 member(s).", "messageID": 9 }`. The sender is never a recipient.

### Reply

`POST /api/v1/board/messages/reply`

| Field | Required | Notes |
|---|---|---|
| `messageID` | Yes | An inbox message |
| `body` | Yes | 2 - 10000 |

The reply goes to the original sender, and the original message is marked read. `403` if the message is not in the member's inbox.

### Mark read

`POST /api/v1/board/messages/mark-read` with `messageID`.

## Members directory

`GET /api/v1/board/members` (needs `can_view_members`)

```json
{
  "success": true,
  "data": {
    "can_message_members": true,
    "can_compose_messages": true,
    "sms_enabled": true,
    "members": [
      { "memberID": 2, "full_name": "Often Fred", "position_name": "Chairperson", "position_code": "CHAIR",
        "phone": "07XXXXXXXX", "email": "member@example.com", "is_me": true }
    ]
  }
}
```

Only Active members. Show a "Message" button when `can_compose_messages` and a "Send SMS" button when `can_message_members` and the member has a phone and `sms_enabled`.

### Send SMS to members

`POST /api/v1/board/members/message` (needs `can_message_members`)

| Field | Required | Notes |
|---|---|---|
| `message` | Yes | 3 - 480 characters |
| `send_to` | Yes | `all` or `selected` |
| `member_ids[]` | If `selected` | Only members with a phone number are used |

Response:

```json
{ "success": true, "message": "SMS sent to 2 member(s).",
  "results": { "success": 2, "failed": 0, "details": [ { "name": "...", "phone": "...", "success": true, "note": null } ] } }
```

`422` if the school has SMS disabled or nobody has a phone number.

## Committees

`GET /api/v1/board/committees` (needs `can_view_committees`)

```json
{ "success": true,
  "data": { "can_manage": true,
    "committees": [ { "committeeID": 1, "committee_name": "Finance", "committee_code": "FINANCE", "description": null,
                      "status": "Active", "formed_date": "2026-01-10", "members_count": 4 } ] } }
```

Create: `POST /api/v1/board/committees` (needs `can_manage_committees`) with `committee_name` (required, max 150), `description`, `status` (`Active`/`Inactive`), `formed_date`. `422` if the name already exists.

## Meetings

### List

`GET /api/v1/board/meetings` (needs `can_view_meetings`). Every member sees every school meeting, so they can comment even if absent (same as the website).

```json
{ "success": true,
  "data": {
    "can_manage": true, "can_comment": true,
    "meetings": [
      { "meetingID": 2, "title": "Term 4 board meeting", "meeting_type": "Full Board",
        "meeting_date": "2026-11-20", "start_time": "09:00", "end_time": "11:00", "venue": "Hall",
        "agenda": "...", "status": "Scheduled", "notes": null,
        "committee": null,
        "chair": { "memberID": 2, "full_name": "Often Fred", "position_name": "Chairperson", "position_code": "CHAIR" },
        "comments_count": 3, "attendees_count": 5 } ],
    "options": { "meeting_types": ["Full Board", "Committee"], "committees": [ { "committeeID": 1, "committee_name": "Finance" } ],
                 "board_members": [ ] }
  } }
```

Status becomes `Ongoing` automatically once the start time passes. Other values: `Scheduled`, `Ongoing`, `Completed`. `options` appears only with `can_manage`.

### Detail

`GET /api/v1/board/meetings/{meetingID}`

```json
{ "success": true,
  "data": {
    "meeting": { },
    "attendees": [ { "memberID": 3, "full_name": "...", "position_name": "...", "position_code": "...", "attendance_status": "Expected" } ],
    "is_attendee": true,
    "can_comment": true,
    "comments": [ { "commentID": 1, "member": { }, "body": "...", "was_absent": false, "created_at": "..." } ]
  } }
```

`was_absent` is `true` when the commenter was not chair or attendee.

### Schedule

`POST /api/v1/board/meetings` (needs `can_manage_meetings`)

| Field | Required | Notes |
|---|---|---|
| `title` | Yes | max 200 |
| `meeting_type` | Yes | `Full Board` or `Committee` |
| `committeeID` | If `Committee` | Active committee |
| `meeting_date` | Yes | `Y-m-d` |
| `start_time`, `end_time` | No | `H:i` |
| `venue`, `agenda`, `notes` | No | |
| `chair_memberID` | No | Defaults to the logged-in member |
| `attendee_ids[]` | No | Board member IDs |

### Comment

`POST /api/v1/board/meetings/comment` (needs `can_comment_meetings`) with `meetingID` and `body` (2 - 5000). Response includes `commentID`.

## Minutes

`GET /api/v1/board/minutes` (needs `can_view_minutes`)

```json
{ "success": true,
  "data": {
    "can_manage": true,
    "minutes": [ { "minuteID": 1, "meeting": { "meetingID": 2, "title": "...", "meeting_date": "...", "status": "Completed" },
                   "title": "...", "content": "...", "decisions_summary": "...", "status": "Draft",
                   "prepared_by": { }, "approved_by": null, "approved_at": null, "notes": null, "updated_at": "..." } ],
    "options": { "statuses": ["Draft", "Submitted", "Approved"], "eligible_meetings": [ { "meetingID": 2, "title": "...", "meeting_date": "...", "status": "Ongoing" } ] }
  } }
```

Members without `see_all_minutes` see only minutes they prepared or approved, plus Approved minutes.

Record: `POST /api/v1/board/minutes` (needs `can_manage_minutes`): `meetingID` (required), `title` (required, max 200), `content`, `decisions_summary`, `status` (`Draft`/`Submitted`/`Approved`, default Draft), `notes`. Minutes can be recorded only once the meeting is Ongoing or Completed, and only once per meeting (`422` otherwise).

## Resolutions

`GET /api/v1/board/resolutions` (needs `can_view_resolutions`)

```json
{ "success": true,
  "data": {
    "can_manage": true,
    "resolutions": [ { "resolutionID": 1, "resolution_number": "RES-2026-001", "title": "...", "description": "...",
                       "meeting": { }, "proposed_by": { }, "seconded_by": null, "assigned_to": { },
                       "resolution_date": "2026-11-16", "implementation_deadline": null, "status": "Proposed", "notes": null } ],
    "options": { "statuses": ["Proposed", "Adopted", "Rejected", "Deferred", "Implemented"], "source_meetings": [ ], "board_members": [ ] }
  } }
```

Without `see_all_resolutions`, the member sees only resolutions assigned to, proposed by or seconded by them.

Record: `POST /api/v1/board/resolutions` (needs `can_manage_resolutions`): `meetingID` (required), `title` (required, max 250), `description`, `proposed_by` (defaults to the logged-in member), `seconded_by`, `assigned_to`, `resolution_date`, `implementation_deadline`, `status`, `notes`. The number (`RES-YYYY-NNN`) is generated by the server. The meeting must be Ongoing or Completed.

## Action items

`GET /api/v1/board/action-items` (needs `can_view_action_items`)

```json
{ "success": true,
  "data": {
    "can_manage": true,
    "status_options": ["Open", "In Progress", "Completed", "Cancelled"],
    "action_items": [ { "actionItemID": 1, "title": "...", "description": null, "meeting": { }, "assigned_to": { },
                        "due_date": "2026-11-23", "priority": "High", "status": "Open", "is_overdue": false,
                        "completed_at": null, "notes": null, "can_update": true } ],
    "options": { "priorities": ["Low", "Medium", "High"], "source_meetings": [ ], "board_members": [ ] }
  } }
```

Without `see_all_action_items`, only items assigned to the member are returned. Show the status control only when `can_update` is true.

Create: `POST /api/v1/board/action-items` (needs `can_manage_action_items`): `meetingID` (required), `title` (required, max 250), `description`, `assigned_to`, `due_date`, `priority` (`Low`/`Medium`/`High`), `notes`. New items start as `Open`. The meeting must be Ongoing or Completed.

Update status: `POST /api/v1/board/action-items/status` with `actionItemID` and `status` (`Open`, `Pending`, `In Progress`, `Completed`, `Cancelled`). `Pending` is stored as `Open`. A member can update their own items (`can_update_own_actions`); `can_update_any_actions` allows any. Otherwise `403`.

## Documents

`GET /api/v1/board/documents` (needs `can_view_documents`)

```json
{ "success": true,
  "data": {
    "can_upload": true,
    "documents": [ { "documentID": 1, "title": "Budget 2026", "category": "Finance", "description": null,
                     "original_filename": "budget.pdf", "mime_type": "application/pdf", "file_size": 120400,
                     "file_size_label": "117.6 KB", "uploaded_by_name": "Often Fred", "created_at": "...",
                     "download_url": "https://host/api/v1/board/documents/1/download" } ]
  } }
```

For a treasurer, finance and budget documents come first (same as the website).

Download: `GET {download_url}` with the same `Authorization` header. It returns the file (not JSON). `404` JSON if the file is missing.

Upload: `POST /api/v1/board/documents` as `multipart/form-data` (needs `can_manage_documents`): `title` (required, max 200), `category` (optional, max 50, defaults to `General`), `description`, `file` (required, max 10 MB).

## Notes for the app developer

- The website has no push notifications for board members. Use `unread_messages` (profile, dashboard, messages) for a badge and refresh it when the app opens.
- Create forms: use the `options` block from the matching list endpoint. Do not hard-code members or meetings.
- Meetings for minutes, resolutions and action items must be Ongoing or Completed; the list options already filter this.
- Change password: `POST /api/v1/auth/password` with `current_password`, `password`, `password_confirmation` (min 6). Other devices are logged out.
- Logout: `POST /api/v1/auth/logout` revokes the current token.
