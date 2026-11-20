# Teacher Store Requisitions - Mobile API

Web page: `/teacher/store-requisitions`  
Mobile base: `{HOST}/api/v1/teacher`  
Menu: Main → Requisitions (always available — **not** the inventory manage screen)

Request store items, track pending/approved requests, see issued items & incidents, download section PDFs.

---

## 0. Rules

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |
| `Content-Type` | `application/json` (POST) |

- Teacher tokens only. Teacher must be **Active**.
- Dates: `Y-m-d`. If any selected label has `requisition_status: "can_return"`, `expected_return_date` is required (≥ today).
- Do not render HTML fields (hub already strips them). Do not treat PDF as JSON.

---

## 1. Screens (match website)

1. **Dashboard** — stats cards (pending, active, overdue, incidents…).
2. **My Requests** — Pending tab + History (approved/rejected).
3. **My Items** — Issued requisitions (return status, expected return).
4. **Incidents** — Open/resolved incidents linked to me.
5. **Request Item** modal — category → item → label(s) → take method/options → qty → return date (if needed) → notes → Submit.
6. **PDF** per section.

---

## 2. Flow

```text
GET /store-requisitions
  → dashboard, available_items (with take_options), pending, history, issued, incidents

Request item:
  pick item → pick label(s) from available_items[].labels[]
  choose take_key from label.take_options[] (or take_structure.methods)
  POST /store-requisitions  { issues: [...], notes?, expected_return_date? }

PDF:
  GET /store-requisitions/pdf?section=dashboard|my_requests|my_items|incidents
```

---

## 3. Endpoints

### 3.1 Hub — `GET /store-requisitions`

```json
{
  "success": true,
  "data": {
    "title": "Requisitions",
    "requester": { "type": "teacher", "id": 2, "name": "DAVID JOHN", "employee_number": "T3345" },
    "dashboard": {
      "total_requests": 4,
      "pending_requests": 1,
      "total_requisitions": 3,
      "active_items": 2,
      "returned_items": 1,
      "overdue_items": 0,
      "open_incidents": 0,
      "generated_at": "18 Nov 2026, 11:50"
    },
    "sections": [
      { "key": "dashboard", "title": "Dashboard", "pdf_section": "dashboard" },
      { "key": "my_requests", "title": "My Requests", "pdf_section": "my_requests" },
      { "key": "my_items", "title": "My Items", "pdf_section": "my_items" },
      { "key": "incidents", "title": "Incidents", "pdf_section": "incidents" }
    ],
    "categories": [{ "categoryID": 1, "category_name": "Lab" }],
    "available_items": [
      {
        "itemID": 10,
        "item_name": "Beaker",
        "categoryID": 1,
        "category_name": "Lab",
        "available_labels": 2,
        "labels": [
          {
            "stock_unitID": 55,
            "label": "BK-001",
            "package_unit_display": "Box (10 piece)",
            "store_method": "package",
            "requisition_status": "can_return",
            "remaining_items": 10,
            "leaf_unit_name": "piece",
            "requires_return_date": true,
            "take_structure": {
              "show_method_select": true,
              "methods": [
                { "key": "main", "label": "By Main Package", "options": [{ "key": "full_12", "label": "Box", "max_qty": 1, "items_each": 10 }] },
                { "key": "item", "label": "By Item Inside", "options": [{ "key": "item", "label": "piece", "max_qty": 10, "items_each": 1 }] }
              ],
              "take_options": [/* flat list used for validation */]
            },
            "take_options": [
              { "key": "full_12", "level": "package", "label": "Box", "max_qty": 1, "items_each": 10 },
              { "key": "item", "level": "item", "label": "piece", "max_qty": 10, "items_each": 1 }
            ]
          }
        ]
      }
    ],
    "pending_requests": [
      {
        "requestID": 8,
        "status": "requested",
        "status_label": "Requested",
        "items_summary": "Beaker [BK-001] - 1 × Box (10 piece)",
        "notes": null,
        "expected_return_date": "25 Nov 2026",
        "requested_at": "18 Nov 2026, 09:10"
      }
    ],
    "request_history": [],
    "issued_items": [
      {
        "requisitionID": 20,
        "item_name": "Beaker",
        "label": "BK-002",
        "take_display": "1 piece",
        "status": "issued",
        "requisition_status": "can_return",
        "return_status_text": "Not Returned - Pending",
        "expected_return_date": "20 Nov 2026",
        "is_overdue": false,
        "issued_at": "10 Nov 2026, 14:00"
      }
    ],
    "incidents": [],
    "form_rules": {
      "issues_min": 1,
      "notes_max": 1000,
      "expected_return_date_required_if_can_return": true,
      "expected_return_date_min": "today"
    }
  }
}
```

| Field | Use |
|---|---|
| `categories` | Filter items in Request modal |
| `available_items[].labels[]` | Labels still in stock for that item |
| `label.take_options` / `take_structure` | Build method picker (same as website JS) |
| `label.requires_return_date` | Show expected return date field |
| `pending_requests` / `request_history` | My Requests section |
| `issued_items` | My Items section — use `return_status_text`, `is_overdue` |
| `incidents` | Incidents section — use `resolve_status`, `payment_status` |

---

### 3.2 Submit request — `POST /store-requisitions`

```json
{
  "issues": [
    {
      "stock_unitID": 55,
      "take_key": "item",
      "quantity": 2
    }
  ],
  "notes": "For practical class Form 1A",
  "expected_return_date": "2026-11-25"
}
```

| Field | Required | Notes |
|---|---|---|
| `issues` | yes | min 1 |
| `issues[].stock_unitID` | yes | from `labels[].stock_unitID` |
| `issues[].take_key` | yes | must match a key in that label’s `take_options` |
| `issues[].quantity` | yes | `1 … take_option.max_qty` |
| `notes` | no | max 1000 |
| `expected_return_date` | if any issue’s unit is `can_return` | `Y-m-d`, ≥ today |

Success:

```json
{
  "success": true,
  "message": "Ombi lako limewasilishwa…",
  "request": { "requestID": 9, "status": "requested", "items_summary": "…", "…" : "…" }
}
```

Then refresh hub or prepend `request` into `pending_requests`.

**How to choose `take_key` (mirror website)**

1. If `take_structure.show_method_select` is false → use the single `take_options[0].key` (often `full` or `item`), quantity usually 1 or leaf qty.
2. If true → user picks a method (`main` / `sub` / `item`) from `take_structure.methods[]`, then an option key from that method’s `options[]`.
3. Multiple labels can be selected → one `issues[]` entry per label (website multi-label flow uses main package qty 1 each).

---

### 3.3 PDF — `GET /store-requisitions/pdf?section=`

| `section` | Content |
|---|---|
| `dashboard` | Stats (default) |
| `my_requests` | Pending + history |
| `my_items` | Issued items |
| `incidents` | Incidents |

Response: PDF binary (`application/pdf`).  
Filename pattern: `My_Store_{Section}_{YmdHis}.pdf`.

---

## 4. Website UI → API map

| Website | API |
|---|---|
| Open Requisitions | `GET /store-requisitions` |
| Dashboard cards | `data.dashboard` |
| Nav: My Requests / My Items / Incidents | `pending_requests`, `request_history`, `issued_items`, `incidents` |
| Request Item button | Build form from `categories` + `available_items` |
| Submit | `POST /store-requisitions` |
| Download PDF | `GET /store-requisitions/pdf?section=` |

Teachers **cannot** approve/return from this screen (store operators do that on Inventory manage).

---

## 5. Related

- Inventory manage (actors only): `docs/TEACHER_MOBILE_API.md` § Inventory  
- Auth / `has_inventory_access`: `docs/TEACHER_MOBILE_API.md`
