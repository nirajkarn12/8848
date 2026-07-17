# Staff Assignment, Login & Commission — Phase Doc

Reusable checklist for the cleaning-agency staff system.  
Paste into a new chat: **“Implement Phase X from `docs/staff-assignment-phases.md`”**.

**Related (separate) plan:** Cleaning Service Agency Migration & Reuse (frontend redesign, bookings as services). This doc covers **staff + commission only**.

---

## Locked decisions (do not re-ask unless changing)

| Decision | Choice |
|----------|--------|
| Staff storage | `tbl_staff` (separate from `tbl_user`) |
| Assignment | `tbl_booking_assignment` (one primary staff per booking in Phases 1–3) |
| Commission base | `tbl_payment.grand_total` (fallback: order lines) |
| Commission stack | Override → Service → Staff → Global |
| On job Completed | `commission_status = approved` |
| On owner payout | `commission_status = paid` |
| Notify on assign | Email via PHPMailer (Phase 2) |

### Commission stack (first match wins)

1. Assignment override (admin custom % / fixed / custom amount)  
2. Service rule (`tbl_product.staff_commission_*`)  
3. Staff default (`tbl_staff.default_commission_*`)  
4. Global default (`tbl_settings.default_staff_commission_*`)

### Commission lifecycle

`pending` → (staff completes job) → `approved` → (owner pays) → `paid`

---

## Status overview

| Phase | Status | Goal |
|-------|--------|------|
| **1** | ✅ Done | Staff CRUD, assign jobs, staff login, job details |
| **2** | ✅ Done | Full commission rules, report, earnings, email on assign |
| **3** | ✅ Done | Owner payout, staff report, dashboard widgets, CSV |
| **4** | ✅ Done | Multi-staff, availability, GPS check-in, auto-suggest, PWA |

---

## Demo / URLs

| What | URL / credential |
|------|------------------|
| Staff login | `/staff/login.php` |
| Demo staff | `staff@demo.com` / `staff123` |
| Assign staff | Admin → Order → Assign |
| Commission report | `/admin/commission.php` |
| Staff earnings | `/staff/earnings.php` |
| Phase 1 migration | `/admin/run-staff-migration.php` |
| Phase 2 migration | `/admin/run-staff-phase2-migration.php` |

---

## Phase 1 — Foundation (Booking + Staff + Assignment) ✅

**Goal:** Admin assigns staff; staff logs in and sees where to go + client phone.

### Delivered

**Database** (`admin/staff-phase1-migration.sql`)

- [x] `tbl_staff`
- [x] `tbl_booking_assignment`
- [x] `tbl_payment`: `service_address`, `preferred_date`, `preferred_time`, `booking_status`, `assignment_status`
- [x] `tbl_settings`: `default_staff_commission_type`, `default_staff_commission_value`
- [x] Demo staff seed

**Admin**

- [x] `admin/staff.php`, `staff-add.php`, `staff-edit.php`, `staff-delete.php`
- [x] Sidebar: Staff Management
- [x] `admin/order-assign.php` — pick staff, address, commission override
- [x] `admin/order.php` — Assigned Staff, Job Status columns
- [x] `admin/order-show.php` — assignment panel
- [x] `admin/inc/commission.php` — helper (later extended in Phase 2)

**Staff portal (`/staff/`)**

- [x] `login.php`, `logout.php`, `index.php`, `job.php`, `job-status.php`
- [x] `inc/bootstrap.php`, `header.php`, `footer.php`, `assets/style.css`
- [x] Phone `tel:` link, Google Maps link
- [x] Job status updates (Assigned → En Route → In Progress → Completed)

### Verification (Phase 1)

- [ ] Create staff in admin  
- [ ] Assign order with address  
- [ ] Staff login → see job, call phone, open map  
- [ ] Mark Completed → commission becomes approved  

### Patterns to copy

- Staff CRUD → `admin/customer.php` style  
- Assign UI → AdminLTE `box box-info`  
- Staff session → `$_SESSION['staff']` only (never admin session)

---

## Phase 2 — Commission Rules & Job Workflow ✅

**Goal:** Owner controls commission at service / staff / job level; staff earnings + email.

### Delivered

**Database** (`admin/staff-phase2-migration.sql`)

- [x] `tbl_product.staff_commission_type`, `staff_commission_value`
- [x] `tbl_booking_assignment.approved_at`, `paid_at`
- [x] Settings defaults already from Phase 1

**Admin**

- [x] Product add/edit — Staff Commission fields  
- [x] Staff add/edit — default commission (Phase 1)  
- [x] Settings → **Staff Commission** tab  
- [x] Assign screen — live preview + rule source + owner override  
- [x] Full stack in `admin/inc/commission.php`  
- [x] `admin/commission.php` — filter by staff, date, status  
- [x] Sidebar: Commission Report  
- [x] Email staff on assign (`sendStaffAssignmentEmail`)

**Staff**

- [x] Status workflow already in Phase 1; Completed sets `approved` + `approved_at`  
- [x] `staff/earnings.php` — pending / approved / paid  
- [x] Nav link: Earnings  

### Verification (Phase 2)

- [ ] Set product commission 40% → assign → preview shows Service rule  
- [ ] Override on assign → preview shows Owner override  
- [ ] Staff completes job → Earnings shows Approved  
- [ ] Commission Report filters work  
- [ ] Staff receives assignment email (if SMTP OK)

### Key files

```
admin/inc/commission.php
admin/order-assign.php
admin/commission.php
admin/product-add.php, product-edit.php
admin/settings.php (tab_12 / form12)
admin/staff-phase2-migration.sql
admin/run-staff-phase2-migration.php
staff/earnings.php
```

---

## Phase 3 — Owner Payout & Reporting ✅

**Goal:** Owner sees what is owed, marks commissions Paid, exports data.

### Delivered

- [x] `admin/commission-pay.php` — bulk mark approved → paid (+ `paid_at`)
- [x] `admin/staff-report.php` — per staff jobs, earned, paid, balance due
- [x] `admin/commission-csv.php` — CSV export with same filters as report
- [x] `admin/commission.php` — Pay / Staff Report / Export CSV + Mark Paid action
- [x] `admin/header.php` — Commissions submenu
- [x] `admin/index.php` — Unassigned Bookings, Pending Commission, Approved Due widgets
- [x] No payout batch table (mark paid directly on assignment rows)

### Verification (Phase 3)

- [ ] Complete a job → appears as Approved in Commission Report  
- [ ] Bulk mark Paid on `commission-pay.php` → status = paid, `paid_at` set  
- [ ] Staff report shows balance due; staff Earnings Paid increases  
- [ ] Export CSV from Commission Report  
- [ ] Dashboard shows unassigned + commission widgets  

### Key URLs

| Page | URL |
|------|-----|
| Pay commissions | `/admin/commission-pay.php` |
| Staff report | `/admin/staff-report.php` |
| CSV export | `/admin/commission-csv.php` |

---

## Phase 4 — Advanced ✅

**Goal:** Multi-staff jobs, availability, GPS check-in, auto-suggest, staff PWA.

### Delivered

| Feature | Status | Where |
|---------|--------|--------|
| Multiple staff per job | ✅ | Assign again with **Commission Share %**; list on order + assign |
| Staff availability | ✅ | `admin/staff-availability.php` (from Staff Edit / list) |
| GPS / check-in | ✅ | Staff job → **Check In (Arrived)** + `arrived_at` / lat / lng |
| Auto-suggest | ✅ | Assign screen → **Auto-Suggest Staff** (round-robin + day availability) |
| Staff PWA | ✅ | `staff/manifest.webmanifest`, `sw.js`, icons |
| Push / SMS gateway | ⏭ Skipped | Email already in Phase 2; PWA is installable foundation |

### Database

- Migration: `admin/staff-phase4-migration.sql` / `run-staff-phase4-migration.php`
- Columns: `arrived_at`, `checkin_lat`, `checkin_lng`, `commission_share_percent`
- Tables: `tbl_staff_availability`, `tbl_staff_auto_assign`

### Verification (Phase 4)

- [ ] Assign two staff to one order with 50% / 50% shares  
- [ ] Staff taps Check In → status Arrived + GPS (if allowed)  
- [ ] Set Mon–Sat availability → Auto-Suggest prefers available staff  
- [ ] On phone: Add Staff Portal to Home Screen (PWA)  

### Key URLs

| Page | URL |
|------|-----|
| Phase 4 migration | `/admin/run-staff-phase4-migration.php` |
| Staff availability | `/admin/staff-availability.php?id={staff_id}` |
| Staff PWA | `/staff/` (install from browser) |

---

## Architecture (quick reference)

```
Client books → tbl_payment + tbl_order
     ↓
Admin assigns → tbl_booking_assignment (+ commission calc)
     ↓
Email staff (Phase 2)
     ↓
Staff portal → job detail (phone, map, status)
     ↓
Completed → commission approved
     ↓
Owner pays (Phase 3) → commission paid
```

### Security

| Area | Admin | Staff |
|------|-------|-------|
| Session | `$_SESSION['user']` | `$_SESSION['staff']` |
| Table | `tbl_user` | `tbl_staff` |
| See bookings | All | Assigned only (`staff_id = session`) |
| Change commission | Yes | No |
| Mark paid | Yes (Phase 3) | No |

---

## File index (current + planned)

### Exists (Phases 1–2)

```
admin/staff.php, staff-add.php, staff-edit.php, staff-delete.php
admin/order-assign.php
admin/commission.php
admin/inc/commission.php
admin/staff-phase1-migration.sql
admin/staff-phase2-migration.sql
admin/run-staff-migration.php
admin/run-staff-phase2-migration.php
staff/login.php, logout.php, index.php, job.php, job-status.php, earnings.php
staff/inc/bootstrap.php, header.php, footer.php
staff/assets/style.css
```

### Exists (Phase 3)

```
admin/commission-pay.php
admin/staff-report.php
admin/commission-csv.php
(+ edits: admin/index.php, admin/header.php, admin/commission.php)
```

### Exists (Phase 4)

```
admin/staff-availability.php
admin/staff-phase4-migration.sql
admin/run-staff-phase4-migration.php
staff/manifest.webmanifest, sw.js, assets/icon-192.png, assets/icon-512.png
(+ edits: order-assign, order-show, job.php, job-status, commission helpers)
```

### Cleaning-agency frontend (customer site)

Public storefront redesigned as a cleaning service agency:

- Agency hero + services language (`Shop` → `Services`, cart → booking)
- `book-service.php` — pick service, address, preferred date/time
- Checkout saves `service_address`, `preferred_date`, `preferred_time` on `tbl_payment` (for staff assign)
- Visual theme: teal/fresh agency CSS in `assets/css/style.css`

Staff + commission system is complete end-to-end.

---

## How to reuse in a new chat

1. Attach or mention: `docs/staff-assignment-phases.md`  
2. Say exactly which phase: **“Implement Phase 3”** (or a Phase 4 item)  
3. Agent should: follow checklist → copy existing admin/staff patterns → run verification → **not** redesign admin CSS  

### Suggested order

1. Phase 1 ✅  
2. Phase 2 ✅  
3. Phase 3 ✅  
4. Phase 4 ✅  

Staff + commission system is complete end-to-end. Remaining work (if any) is the separate **Cleaning Service Agency Migration & Reuse** frontend plan.

---

*Last updated: after Phase 4 (multi-staff, availability, GPS check-in, auto-suggest, PWA).*
