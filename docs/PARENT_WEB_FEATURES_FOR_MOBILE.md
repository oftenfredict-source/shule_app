# Parent Portal Features (Website Behaviour)

Give this to the mobile app developer together with `PARENT_MOBILE_API.md`.

This document explains **how the parent website works today**. The mobile app should copy these flows and rules, not invent new ones.

Language on the website: **Swahili (default)** or **English**. The parent switches language from the top bar. Mobile should support the same two languages.

Login on the website: phone number + password. That is the same account the mobile login uses.

---

## Menu structure on the website

### Academics and activities
1. Dashboard
2. Exam results
3. Student report (term report)
4. System attendance
5. Fingerprints
6. Subjects
7. Home package
8. Notes
9. Past papers

### Finance
10. Make payment
11. Fees summary

### Account
12. Permissions
13. Logout

Header also has:
- Language switch (EN / SW)
- Notification bell (results, home packages, notes, past papers)

---

## 1. Login

**Who:** Parent only.

**Username:** Parent phone number (same as school records).

**Password:** Password given by the school / reset by school admin.

**After login:** Parent lands on Dashboard.

**Rules:**
- One parent account can have many children in the same school.
- Parent only sees children linked to their `parentID`.
- Transferred children are generally hidden from day-to-day lists.

---

## 2. Dashboard

Home screen after login.

**Shows:**
- Welcome / parent name and school
- Result or term-report alerts if something new was released
- Stat cards:
  - Total children
  - Active children
  - Recent results count (opens Exam Results)
  - Upcoming exams count
- Children mix (boys / girls / active share)
- My children list:
  - Photo, name, admission number, class/stream, Active/Inactive badge
  - This month's attendance percentage for each child
- Quick tools: Results, Attendance, Subjects, Payments, etc.
- Recent results list
- Recent attendance alerts
- Active library book borrows (if any)
- Upcoming exams for children's classes (next 30 days)

**Mobile should:** show the same summary cards and children list. Use dashboard API for home, then open full feature screens from shortcuts.

---

## 3. Exam results

Dedicated screen for **one exam** (not the whole term).

### Website flow (wizard)
1. Choose **child**
2. Choose **academic year** (only years for that child’s level — O or A. If the site shows 2 cards, the API must also return 2 for that child)
3. Choose **term** (`first_term` / `second_term`)
4. Choose **exam** (only exams published and released to parents)
5. Tap **View results**

Load years with `GET /api/v1/parent/academic-years?student={studentID}` after the child is chosen so the list matches the website.

### What the parent sees after selecting
- Child name, class, exam name, term, year
- Subject-by-subject marks / grades
- Totals, average, division/grade, position (when school has published them)
- Download PDF button

### Rules
- Only results with school release to parents (`parent_status = enabled`)
- Only approved/allowed results
- Secondary schools may show O-level / A-level labels on children and years
- If no published exam for that term/year, website shows “no exams published”
- Year options depend on the selected child’s level (O / A)

**Mobile should:** same 4-step selection, then one results sheet. Use:
- academic years
- published exams
- results with `type=exam`

---

## 4. Student report (term report)

Same wizard idea as Exam Results, but **no exam step**.

### Website flow
1. Choose child
2. Choose academic year
3. Choose term
4. View **full term report** (all exams in that term combined)

### What the parent sees
- Term summary across subjects
- Combined marks / grades / division where applicable
- Download PDF

### Rules
- Same parent-release rules as exam results
- This is the “report” type, not a single exam

**Mobile should:** reuse results screen with `type=report` (no exam picker).

---

## 5. System attendance

Class / teacher attendance marked in the school system (not biometric).

### Screen 1 — Children for a month
Filters:
- Search child by name / admission number
- Month
- Year

Each child card shows:
- Photo, name, class
- Attendance rate % for that month
- Counts: Present, Absent, Sick, Permission, Incomplete

Tap a child → calendar.

### Screen 2 — One child month calendar
- Full month day-by-day
- Prev / next month
- Legend

### Day statuses
| Status | Meaning |
|---|---|
| Present | Marked present |
| Absent / Missed | Marked absent |
| Sick | Approved sick leave for that day |
| Permission | Approved permission / excused |
| Incomplete | School day already passed, but attendance not marked |
| Non-school | Weekend / holiday / non-working day |
| Future | School day that has not happened yet |

**Mobile should:** month overview cards → child calendar. Match these statuses and labels.

---

## 6. Fingerprints

Same layout as System Attendance, but based on **fingerprint check-in**.

### Extra rules
- School admin enables fingerprint portal **per class**
- If a child’s class is not enabled:
  - Card shows not available / disabled
  - Parent cannot open that child’s fingerprint calendar
- When enabled, calendar uses check-in presence:
  - Present if fingerprint check-in exists
  - Absent on a working day with no check-in (and no leave)
  - Sick / Permission from approved leave
  - Non-school for non-working days

**Mobile should:** same overview + calendar. Respect `fingerprint_enabled` / 403 when class is not allowed.

---

## 7. Subjects

Used to view subjects and **elect optional subjects**.

### Website flow
1. Choose child (active children only)
2. See all subjects for that child’s class/stream:
   - Subject name / code
   - Teacher
   - Required or Optional
   - Whether this child already elected it
   - How many students elected it

### Actions
- **Elect** optional subject
- **Deselect** optional subject
- Required subjects cannot be elected/deselected

### Side effects
- School sends SMS to parent when a subject is elected or deselected

**Mobile should:** child picker → subject list → elect/deselect buttons only on Optional subjects.

---

## 8. Home package

Holiday homework packages uploaded by teachers.

### Website flow
1. Open Home Package
2. See holidays that have packages for the parent’s children’s subjects
3. Tap a holiday
4. See package list:
   - Title / filename
   - Subject
   - Class
   - Teacher
   - View / Download

### Rules
- Only packages relevant to the parent’s children subjects
- Uses the school’s selected academic year(s)
- Unread badge appears in sidebar / notifications when new packages are uploaded

**Mobile should:** holidays list → packages for holiday → open/download file with auth token.

---

## 9. Notes

Teacher notes (PDF/DOC) for subjects.

### Website flow (3 levels)
1. Choose child  
   - Shows notes count + unread count
2. Choose subject for that child  
   - Shows notes count + unread count per subject
3. Open notes list  
   - Title, teacher, date, file type  
   - View / Download  
   - Opening the list marks those notes as read for that child

### Rules
- Only for the selected academic year(s)
- Only subjects belonging to that child
- Files are not public; parent must be logged in

**Mobile should:** same 3-step drill-down with `kind=note`.

---

## 10. Past papers

Same behaviour as Notes, but for **past papers**.

- Same child → subject → files drill-down
- Separate unread badge
- Same view/download rules
- Use `kind=past_paper`

Treat Notes and Past Papers as two menus that share one pattern.

---

## 11. Make payment

Finance screen for bills and control numbers.

### Top summary
- Total required
- Total paid
- Outstanding balance

### Filters
- Search child name / admission number
- Academic year
- Fee type
- Reset filters

### Table / list per bill
Typical columns:
- Child
- Fee name / type
- Amount
- Control number
- Date
- Status (Paid / Pending)

### Main action
**Request control number** for an active child / unpaid bill.

What happens:
1. Parent requests control number
2. System generates / assigns control number for the school payment process
3. Parent (and school flow) receive SMS as on website
4. Parent uses that control number to pay via the school’s bank / mobile money process outside the app

Important: this screen is **not an in-app card payment gateway**. It is bill viewing + control-number request.

**Mobile should:** filters + bills list + request control number button + show SMS/success message.

---

## 12. Fees summary

Balance overview per child for a selected academic year.

### Website flow
1. Choose academic year (the dropdown lists the school's academic-year cards once each, e.g. `2026` and `2025/2026`; it does not list calendar years)
2. See one card per child:
   - Child name, admission number, class
   - Tuition / fee groups
   - Required amount
   - Paid
   - Balance
   - Pending badge when balance > 0

**Mobile should:** year selector + child fee cards. No payment action here; payment actions live in Make Payment.

---

## 13. Permissions (leave / sick requests)

Parent asks school to excuse a child for days.

### Tabs
1. **Request** — create new request
2. **Pending** — waiting for school approval
3. **Approved**
4. **Rejected** (can be edited and resubmitted)

Unread badges appear on Pending / Approved / Rejected tabs.

### Create / resubmit form
| Field | Rule |
|---|---|
| Child | Required, active child |
| Start date | Required |
| End date | Required, on/after start date |
| Reason type | `permission` or `sick` |
| Description | Required, at least 5 characters |
| Attachment | Optional PDF/JPG/PNG, max 5MB |
| Working days | Maximum **7 working days** |

### What happens after submit
- Status becomes Pending
- Parent gets SMS confirmation
- School/admin gets SMS alert
- Approval may go through school approval chain (HOD / principal etc.)
- Parent watches Pending → Approved/Rejected

### Filters on list tabs
- All
- Date range
- Month
- Year

**Mobile should:** same 4 tabs/sections, form with attachment, filters, and unread counts.

---

## 14. Notifications (header bell)

One combined feed.

### Sources
- Exam result released
- Term report released
- New home package
- New note
- New past paper

### Each item shows
- Title
- Message
- Time (e.g. “2 hours ago”)
- Read / unread
- Tap target / screen to open
- For files: view/download links when available

### Actions
- Open item → go to the related screen
- Mark one as read
- Mark all as read

Sidebar badges also show unread counts for Results, Home Package, Notes, Past Papers.

**Mobile should:** notification list + badge count + deep link using `screen` (`results`, `reports`, `home_packages`, `notes`, `past_papers`).

---

## 15. Language

Website top bar:
- EN
- SW

Default is Swahili.

Changing language reloads labels across the portal.

**Mobile should:** app language setting mapped to `Accept-Language: en` or `sw`.

---

## 16. Logout

Website posts logout and clears session.

**Mobile should:** call logout API, delete stored token, return to login.

---

## Shared rules across all features

1. Parent sees **only their own children**.
2. Active children are used for actions (subjects, payments, permissions).
3. Results appear only after school releases them to parents.
4. Attendance calendars respect school working days and approved leave.
5. Notes / past papers / home packages are file-based (PDF/DOC). View and download both exist.
6. Many actions also send SMS (subject election, permission request, control number). Mobile should still show the success message even when SMS is server-side.
7. Academic year is school-controlled. Parent picks from years the school has configured / selected.
8. Unread badges matter: notifications, notes, past papers, home packages, permission tabs.

---

## Recommended mobile screen map

| Website menu | Mobile screen | Main user journey |
|---|---|---|
| Login | Login | Phone + password → save token |
| Dashboard | Home | Summary + children + shortcuts |
| Exam results | Results | Child → year → term → exam → sheet |
| Student report | Term report | Child → year → term → report |
| System attendance | Attendance | Month overview → child calendar |
| Fingerprints | Fingerprint attendance | Same as attendance if class enabled |
| Subjects | Subjects | Child → list → elect/deselect optional |
| Home package | Home packages | Holiday → files |
| Notes | Notes | Child → subject → files |
| Past papers | Past papers | Child → subject → files |
| Make payment | Payments | Filter → bills → request control number |
| Fees summary | Fees | Year → balances per child |
| Permissions | Permissions | Request / Pending / Approved / Rejected |
| Bell | Notifications | List + mark read + open screen |
| Language | Settings | EN / SW |
| Logout | Logout | Revoke token |

---

## What NOT to expect from the parent portal

- Parent cannot edit school data, marks, or attendance.
- Parent cannot pay fees inside the app with a card gateway.
- Parent cannot approve their own permission request.
- Parent cannot see other parents’ children.
- Fingerprint attendance is not available for every class unless school enabled it.
- Results are hidden until school publishes/releases them to parents.

---

## Developer handoff pack

1. This file: website feature behaviour
2. `docs/PARENT_MOBILE_API.md`: exact API endpoints and payloads

Build the app screens from this behaviour document, and wire each screen using the API document.
