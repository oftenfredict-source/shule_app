# Teacher Goals - Mobile API

Web menus under **HR & Goals → Goals** (+ Team Goals for HOD).  
Mobile base: `{HOST}/api/v1/teacher`

| Website | Mobile hub |
|---|---|
| `/goals/hod/assigned` (Team Goals, HOD) | `GET /goals/hod/assigned` (alias `GET /goals/team`) |
| Direct Goals | `GET /goals/direct` |
| Assigned Tasks | `GET /goals/assigned` |
| My Tasks (SGPM) | `GET /sgpm/tasks` |
| Scorecard | `GET /performance/staff` |
| Module menu | `GET /goals` |

**Access:** Team Goals / `/goals/hod/assigned` is **HOD only** (`capabilities.is_hod`). Non-HOD → `403`/`422` with *"You are not assigned as HOD of any department."* Hide the menu when `is_hod` is false.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST) |

- Teacher tokens only.
- `is_hod` from `GET /capabilities` or `GET /goals` controls Team Goals visibility.
- Dates: `Y-m-d`. Do not render raw path strings as UI cards.

---

## 1. Screens (match website)

1. **Goals menu** — submenu list with visibility flags.
2. **Team Goals** (HOD) — department tasks → Delegate / Progress.
3. **Direct Goals** — tasks assigned directly to me by admin.
4. **Assigned Tasks** — department-delegated + direct tasks.
5. **My Tasks (SGPM)** — strategic tasks / subtasks (HOD can assign & review).
6. **Scorecard** — average score + recent tasks.

---

## 2. Flow

```text
GET /goals
  → submenus[].visible

Team Goals (HOD):
  GET /goals/team
  → Delegate: GET /goals/hod/department-members
               GET /goals/hod/member-tasks/{parent_task_id}
               POST /goals/hod/assign-member
  → Progress: GET /goals/team/{id}
               GET /goals/subtasks/{member_task_id}?role=hod
               POST /goals/review/approve-subtask
               POST /goals/review/reset-marks/{id}

Direct / Assigned:
  GET /goals/direct  or  GET /goals/assigned
  → GET /goals/subtasks/{id}?is_direct=0|1
  → POST /goals/subtask
  → POST /goals/step
  → POST /goals/subtask/{id}/submit

SGPM My Tasks:
  GET /sgpm/tasks
  → POST /sgpm/subtasks
  → POST /sgpm/subtasks/{id}/submit
  → (HOD) POST /sgpm/tasks , evaluate, approve/reject subtasks

Scorecard:
  GET /performance/staff  (read-only)
```

---

## 3. Endpoints

### 3.1 Menu — `GET /goals`

```json
{
  "success": true,
  "data": {
    "title": "Goals",
    "is_hod": true,
    "submenus": [
      { "key": "team_goals", "title": "Team Goals", "path": "/goals/hod/assigned", "path_alias": "/goals/team", "web_path": "/goals/hod/assigned", "visible": true },
      { "key": "direct_goals", "title": "Direct Goals", "path": "/goals/direct", "visible": true },
      { "key": "assigned_tasks", "title": "Assigned Tasks", "path": "/goals/assigned", "visible": true },
      { "key": "sgpm_tasks", "title": "My Tasks", "path": "/sgpm/tasks", "visible": true },
      { "key": "scorecard", "title": "Scorecard", "path": "/performance/staff", "visible": true }
    ]
  }
}
```

Show only rows with `visible: true`.

---

### 3.2 Team Goals — `GET /goals/hod/assigned` (alias `GET /goals/team`)

Website: `/goals/hod/assigned`. HOD only. Non-HOD → `403`/`422` with message.

```json
{
  "success": true,
  "data": {
    "title": "Team Goals",
    "department": { "departmentID": 3, "department_name": "Science" },
    "tasks": [
      {
        "id": 12,
        "task_name": "Improve practicals",
        "goal_name": "Academic excellence",
        "weight": 30,
        "deadline": "2026-12-31",
        "can_delegate": true,
        "can_view_progress": true
      }
    ]
  }
}
```

#### Delegate members

1. `GET /goals/hod/department-members` → `[{ id, name, type: "Teacher"|"Staff" }]`
2. `GET /goals/hod/member-tasks/{parent_task_id}` → existing weight used
3. `POST /goals/hod/assign-member`

```json
{
  "parent_task_id": 12,
  "tasks": [
    {
      "task_name": "Lab prep week 1",
      "member_id": 5,
      "member_type": "Teacher",
      "weight": 10,
      "description": "optional"
    }
  ]
}
```

Incoming + existing weights must not exceed parent `weight`.

#### Progress / review — `GET /goals/team/{id}`

Returns parent task + `member_tasks[]` with `progress`, `pending_review_count`, `can_review`.

Review submitted subtasks:

```http
GET /goals/subtasks/{member_task_id}?role=hod
POST /goals/review/approve-subtask
```

```json
{ "id": 3, "action": "Approve", "marks": 18 }
```

Reject: `{ "id": 3, "action": "Reject" }`  
Reset marks: `POST /goals/review/reset-marks/{subtask_id}`

---

### 3.3 Direct Goals — `GET /goals/direct`

### 3.4 Assigned Tasks — `GET /goals/assigned`

Same shape. Direct = only admin-assigned. Assigned = department-delegated **plus** direct.

```json
{
  "success": true,
  "data": {
    "title": "Assigned Tasks",
    "filter_type": "all",
    "tasks": [
      {
        "id": 9,
        "is_direct": false,
        "member_task_id": 9,
        "direct_task_id": null,
        "task_name": "Lab prep week 1",
        "goal_name": "Academic excellence",
        "weight": 10,
        "progress": 4,
        "subtask_weight_sum": 40,
        "remaining_subtask_weight": 60,
        "subtask_count": 2,
        "source": "department",
        "can_add_subtasks": true
      }
    ]
  }
}
```

| Field | Use |
|---|---|
| `is_direct` / `source` | Badge: Direct vs Via Department |
| `member_task_id` | Use when `is_direct: false` for subtask APIs |
| `direct_task_id` | Use when `is_direct: true` |
| `remaining_subtask_weight` | Max weight left for new subtasks (sum ≤ 100) |

#### Work on a task

**List subtasks**

```http
GET /goals/subtasks/{id}?is_direct=0
GET /goals/subtasks/{id}?is_direct=1
```

**Create subtasks** — `POST /goals/subtask`

```json
{
  "member_task_id": 9,
  "subtasks": [
    { "subtask_name": "Prepare reagents", "weight": 25, "description": "optional" }
  ]
}
```

Direct task: send `direct_task_id` instead of `member_task_id`.

**Update / delete**

```http
POST   /goals/subtask/{id}/update   { "subtask_name", "weight", "description?" }
DELETE /goals/subtask/{id}
```

Blocked when Submitted / Approved.

**Conduct (steps)** — `POST /goals/step`

```json
{
  "subtask_id": 3,
  "steps": [
    { "date": "2026-11-18", "step_description": "Bought chemicals" }
  ]
}
```

Update / delete step: `POST /goals/step/{id}/update`, `DELETE /goals/step/{id}`.

**Submit for review**

```http
POST /goals/subtask/{id}/submit
```

No body. Delegated → HOD; Direct → Admin.

**Subtask detail**

```http
GET /goals/subtask/{id}
```

---

### 3.5 My Tasks (SGPM) — `GET /sgpm/tasks`

```json
{
  "success": true,
  "data": {
    "title": "My Tasks",
    "is_hod": false,
    "tasks": [
      {
        "taskID": 1,
        "kpi": "Complete scheme review",
        "weight": 20,
        "due_date": "2026-12-01",
        "status": "In Progress",
        "progress": 5,
        "is_mine": true,
        "can_manage_subtasks": true,
        "can_submit_progress": true,
        "can_evaluate": false,
        "subtasks": [
          {
            "subtaskID": 4,
            "title": "Draft review",
            "weight_percentage": 5,
            "status": "Pending",
            "can_submit": true,
            "can_approve": false
          }
        ]
      }
    ],
    "action_plans": [],
    "assignees": []
  }
}
```

When `is_hod: true`, hub also returns `action_plans[]` and `assignees[]` for the Assign modal.

| Action | Method | Body |
|---|---|---|
| Assign task (HOD) | `POST /sgpm/tasks` | `action_planID`, `assigned_to` (userID), `kpi`, `weight`, `due_date` |
| Create subtask | `POST /sgpm/subtasks` | `taskID`, `title`, `weight_percentage` (1–100), `due_date`, `description?` |
| Submit subtask | `POST /sgpm/subtasks/{id}/submit` | — |
| Submit progress | `POST /sgpm/tasks/{id}/submit` | `remarks?`, optional `evidence_file` (multipart ≤5MB) |
| Approve subtask (HOD) | `POST /sgpm/subtasks/{id}/approve` | `achieved_score`, `hod_comments?` |
| Reject subtask (HOD) | `POST /sgpm/subtasks/{id}/reject` | `hod_comments?` |
| Evaluate task (HOD) | `POST /sgpm/tasks/{id}/evaluate` | `score_completion`, `score_kpi` (0–100), `status`: `Approved`\|`Rejected`, `hod_comments?` |

Subtask weights must not exceed parent task `weight`.  
`achieved_score` must not exceed subtask `weight_percentage`.

---

### 3.6 Scorecard — `GET /performance/staff`

Read-only.

```json
{
  "success": true,
  "data": {
    "title": "Scorecard",
    "avg_score": 72.5,
    "standing": "Good",
    "tasks": [
      { "taskID": 1, "kpi": "...", "status": "Approved", "total_score": 80, "due_date": "2026-11-01" }
    ],
    "open_tasks_path": "/sgpm/tasks"
  }
}
```

`standing`: `Excellent` (≥80) · `Good` (≥60) · `Needs Attention` · `No scores yet`.

---

### 3.7 Notifications

```http
GET  /goals/notifications
POST /goals/notifications/{id}/read
POST /goals/notifications/read-all
```

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Goals menu | `GET /goals` |
| Team Goals list | `GET /goals/team` |
| Delegate button | §3.2 assign-member |
| Progress button | `GET /goals/team/{id}` + review |
| Direct Goals | `GET /goals/direct` |
| Assigned Tasks | `GET /goals/assigned` |
| Add / View Subtasks | §3.3–3.4 |
| Conduct steps | `POST /goals/step` |
| Submit subtask | `POST /goals/subtask/{id}/submit` |
| My Tasks (SGPM) | `GET /sgpm/tasks` |
| Scorecard | `GET /performance/staff` |

Note: staff_nav “Assign to Members / Progress / Review” are **aliases of the same Team Goals page** — do not build three separate screens.

---

## 5. Related

- Auth / capabilities (`is_hod`): `docs/TEACHER_MOBILE_API.md`
- Management admin Goals / Goal Reports (permission `goal`): management bridges `GET /goals/list`, `GET /goals/reports`
