# Samvedna CMS — Clinical Case Management System

**Source of truth for the build.** Derived from `samvedna_cms_page_flow.html` (the
client-approved six-screen walkthrough). Every screen, lock, mandatory field and
auto-generated artefact in that flow is listed here, plus everything the working
CMS adds around it.

- **Status:** Demo build — full feature surface, seeded demo data, safe to show a client.
- **Code lives in:** `../samvedna-cms/` → `frontend/` (Next.js) + `backend/` (Core PHP).

---

## 1. What this system is

Samvedna Homeopathy treats children with autism, ADHD and related developmental
conditions over **long, multi-month care plans**. The clinical risk is not the
prescription — it is *drift*: a case that quietly stops being reviewed, a doctor
who changes a protocol without recording why, a parent who never hears whether
their child improved.

The CMS exists to make drift structurally impossible:

| Problem | The CMS answer |
| --- | --- |
| Cases activated with half a history | Baseline is **mandatory**; activation is hard-blocked until complete |
| Follow-ups slipping by weeks | Schedule is **auto-generated** from the plan; reschedule is capped at ±7 days |
| Free-text reviews of varying quality | Bi-monthly review is a **forced template**; it cannot close with an empty field |
| Silent protocol changes | Changing the protocol makes the **rationale field mandatory** |
| Stuck cases nobody escalates | **Escalation workflow** with a Senior → Founder approval chain and a 7-day SLA |
| Parents in the dark | **Progress dashboard auto-generated** every cycle and shared with the parent |
| No view of doctor quality | **CMS audit scorecard** per doctor per cycle |

---

## 2. Roles

| Role | Scope |
| --- | --- |
| **Founder** | Sees everything. Final step of the escalation chain. Quarterly review on Premium plans. |
| **Senior Doctor** | Secondary on Standard/Premium cases. **Mandatory sign-off** on every bi-monthly review. First step of the escalation chain. |
| **Case Doctor** | Primary owner. Runs intake, adherence checks, writes reviews, raises escalations. |
| **Coordinator** | Non-clinical. Registers patients, logs adherence checks, reschedules touchpoints, shares dashboards with parents. |

Role assignment happens on the care-plan screen and is **mandatory** — a case
cannot activate without a Case Doctor, and Standard/Premium additionally require
a Senior Doctor.

---

## 3. The six-screen flow

### Step 1 — Patient intake and baseline · `/patients/new`

Registers the child and captures the clinical starting point.

**Patient profile**
- Child name *(required)*, date of birth *(age auto-computed)*, gender
- Guardian name, relation, contact number, alternate number, email
- City / state / country, referral source, internal notes

**Mandatory baseline — required to activate the case**
- Condition classification *(Autism Spectrum Disorder, ADHD, Speech Delay, Global Developmental Delay, Learning Disability, Cerebral Palsy, Sensory Processing Disorder, Behavioural Disorder, Other)*
- Severity level *(Mild / Moderate / Severe)*
- Age at diagnosis, existing medical history
- Current therapy involvement *(speech, OT, behavioural, school support …)*

**Top 3 parent concerns** — exactly three, all mandatory. These are the anchors
every future review and dashboard is measured against.

**Baseline functional markers checklist** — each marker rated, none skippable:
eye contact, social reciprocity, repetitive behaviours, verbal communication,
sensory response, sleep pattern, fine motor skills, attention span, self-help skills.

**The lock:** while any baseline field is incomplete the UI shows
*"Case activation blocked — complete all baseline fields to proceed"* and the
API rejects activation with `409 baseline_incomplete`. **Save draft** is always
allowed — the patient sits in `draft` status until the baseline is finished.

---

### Step 2 — Care plan selection and role assignment · `/patients/{id}/plan`

**Plan catalogue** (seeded, editable under `/plans`):

| Plan | Price | Duration | Formal reviews | Review interval | Adherence checks | Clinical cover |
| --- | --- | --- | --- | --- | --- | --- |
| Starter | Rs 14,999 | 2 months | 1 | 60 days | every 15 days | Case Doctor only |
| **Standard** *(most preferred)* | Rs 39,999 | 6 months | 3 | 60 days | every 15 days | Case + Senior Doctor |
| Premium | Rs 54,999 | 6 months | 6 | 30 days | every 15 days | + Founder review each quarter |

**Auto-populated the moment a plan is confirmed**
- Plan start date *(editable)* → **plan end date (locked, computed)**
- Current cycle → `Cycle 1 of N`
- Plan status → `Active`

**The lock:** *"Follow-up frequency locked by plan — every 60 days. Reschedule
allowed ±7 days only."* The interval comes from the plan row, never from the user.

**Doctor role assignment (mandatory)**
- Case Doctor (Primary) — required on every plan
- Senior Doctor (Secondary) — required on Standard and Premium
- Founder — escalation only; on Premium also a scheduled quarterly reviewer

Confirming the plan **activates the case** and immediately generates the schedule.

---

### Step 3 — Auto-generated follow-up schedule · `/patients/{id}` → Schedule tab

Generated in one transaction at activation. Nothing here is typed by hand.

| Event type | Cadence | Consult? | Owner |
| --- | --- | --- | --- |
| Plan activation and baseline | Day 0 | Yes | Case Doctor |
| Adherence check | every 15 days | **No** — non-consult touchpoint | Case Doctor / Coordinator |
| Bi-monthly formal review | every 60 days (30 on Premium) | Yes | Case + Senior Doctor |
| Founder review | quarterly (Premium only) | Yes | Founder |

Each event carries: cycle number, due date, **original due date** (kept so the
±7-day reschedule window can be enforced), status
(`scheduled` → `upcoming` → `done` / `missed` / `rescheduled`), owner and notes.

**Adherence check capture** — medicine compliance (full / partial / none),
refill status, parent-concern flag, parent note. A raised concern flag surfaces
on the dashboard and pre-fills the next review.

**Cycle overview** — a progress bar per cycle showing elapsed time and completed
touchpoints, exactly as in the flow mock.

---

### Step 4 — Bi-monthly review, forced template · `/reviews/{id}`

The heart of the system. Free text is allowed *inside* fields; skipping a field is not.

**Mandatory review fields — the review cannot close without all of them**
1. Improvements observed in the last 60 days
2. Areas of stagnation / concern
3. Protocol decision — *continued as-is · continued with minor dosage adjustment · protocol changed · paused*
4. **Rationale for change — mandatory whenever the decision is anything other than "continued as-is"**
5. Next 60-day goals *(each goal is stored as a tracked row, not prose)*
6. Therapy coordination notes
7. Medicine adherence % for the cycle

**Escalation decision** — escalate to Senior / Founder, yes or no, with reason.

**Next review date** — auto-computed and locked.

**Sign-off**
- Case Doctor signs first
- **Standard / Premium: Senior Doctor sign-off is mandatory.** Until it lands the
  review sits in `awaiting_signoff` and the lock row reads *"Review cannot be
  closed until Senior Doctor signs off"*.
- Starter plans close on the Case Doctor sign-off alone.

**On close, automatically:** progress report generated → current cycle advanced →
next review event marked upcoming → activity logged.

---

### Step 5 — Escalation management · `/escalations/{id}` *(optional path)*

Raised from a review, or standalone from a case.

**Reason checklist** (multi-select, at least one required)
- No measurable movement across 2 consecutive cycles
- Mixed clinical signals — protocol innovation needed
- Multi-comorbidity complexity
- Parent anxiety unresolved despite structured counselling
- Adverse reaction / safety concern
- Parent requesting plan change or refund

**Approval chain**
1. Senior Doctor — `reviewing`
2. Founder — `standby`, activates only if the Senior does not resolve it

**Fields:** clinical notes for escalation *(mandatory)*, resolution timeframe
**locked to 7 days**, resolution status (`open` → `in_progress` → `resolved`),
resolution notes *(mandatory to resolve)*.

Overdue escalations (past the 7-day SLA) are flagged red on the dashboard and in
the audit scorecard.

---

### Step 6 — Progress dashboard · `/progress/{caseId}/{cycle}` *(auto-generated)*

Built automatically at the end of every review cycle. Two audiences, one record:
an internal view and a parent-safe view.

**Headline metrics** — overall trend (Improving / Stable / Declining),
goals progressing (`2 / 3`), medicine adherence (`100%`).

**Area-wise progress** — a rated bar per baseline marker with its trend, so the
parent sees movement against the same markers captured at intake.

**Next cycle objectives** — the goals written in the review, carried forward as
a checklist.

**CMS audit scorecard (internal)** — per doctor, per cycle:
- CMS completeness % — how much of the forced template was filled on time
- Review closure — on time / late
- Parent satisfaction — 1 to 5

**Sharing** — one action marks the report shared with the parent and records the
channel (WhatsApp / portal / email) and timestamp.

---

### Step 3b — Medicine supply cycle · `/medicine` *(runs alongside the schedule)*

Every active case ships medicine on a **15-day cycle**, independent of the
review cadence. Example: delivered 10 Aug → next due 25 Aug → delivered 25 Aug →
next due 9 Sep, and so on for the life of the plan.

**How the CMS runs it**
- Activating a plan starts the cycle: the plan start date counts as the first
  supply, so the first reminder lands 15 days later.
- Each morning, any case whose `next_due_on` is today appears under **Due today**
  on the Medicine board, the header **bell** lights up with a count, and a
  one-time **"Good morning — medicine to send: N due today"** toast greets the
  first person to sign in.
- **Mark delivered** — one button, one dialog (date defaults to today; how it
  went out; optional tracking reference; note). The dialog shows the resulting
  next-due date *before* you confirm. Confirming logs the delivery, sets
  `next_due_on = delivered_on + 15`, and closes that day's notification.
- A date that slips moves the case to **Overdue** (counted in days) until the
  delivery is marked. Overdue notices are rose; due-today notices are emerald.
- **Coming up** lists the next 7 days so a coordinator can pack ahead.
- Per case, the **Medicine tab** on the patient page shows last delivered, next
  due, the full delivery history, and lets a coordinator change the interval
  (1–90 days) or move the next due date by hand if a parent asks.

**Notifications** are generated lazily on the first API request of each day
(`Notification::generateDaily()`), keyed unique on *(type, case, due date)* so
re-running is safe. A real deployment can also hit `GET /api/notifications/summary`
from a 7 a.m. cron to pre-generate before anyone signs in — the same call feeds
an email/WhatsApp sender later. Notices can be read, dismissed, or closed by a
delivery; `/notifications` keeps the full history.

---

## 4. What the CMS adds beyond the six screens

Everything below is built and demo-seeded.

| Area | Included |
| --- | --- |
| **Auth** | Session login, four roles, role-aware navigation and permissions, demo credentials on the login screen |
| **Command dashboard** | Active cases, reviews due this week, open escalations, overdue SLAs, adherence average, upcoming touchpoints, recent activity |
| **Patient register** | Search, filter by status / condition / severity / doctor, patient code (`SAM-0001`) |
| **Case workspace** | One patient page with tabs — Overview, Baseline, Care plan, Schedule, Reviews, Escalations, Progress |
| **Review queue** | Every review across all cases with due / awaiting-sign-off / closed filters and a sign-off inbox for Senior Doctors |
| **Escalation board** | Open / in-progress / resolved columns, SLA countdown, chain position |
| **Plan catalogue** | Full CRUD on plans — price, duration, cadence, required roles, feature list |
| **Team and roles** | Staff list, role assignment, caseload per doctor |
| **Audit log** | Activity trail — who did what, to which entity, when |
| **Doctor scorecards** | Aggregated CMS completeness, on-time closure and parent satisfaction per doctor |
| **Demo mode** | A banner, demo credentials, and a fully populated Aarav Mehta case sitting exactly where the flow mock leaves it |

---

## 5. Technology

**Frontend — `samvedna-cms/frontend`**
- Next.js 15 (App Router) + React 19 + TypeScript
- Tailwind CSS v4 for the design system
- **Framer Motion** — page transitions, staggered list reveals, timeline draw-in, progress-bar fills, modal springs
- **Lenis** — smooth scrolling across every long case page
- `lucide-react` icons, `react-hook-form` + `zod` for the mandatory-field validation that mirrors the server rules

**Backend — `samvedna-cms/backend`**
- **Core PHP only** (no framework, no Composer), PDO + MySQL, prepared statements everywhere
- Single front controller (`api/index.php`) with an explicit route table, thin handlers, models
- Session cookie auth, JSON contract, role guards per route
- Services encapsulate the clinical rules: `ScheduleGenerator` (schedule build + reschedule window) and `ProgressBuilder` (progress report + scorecard)
- Migration runner + demo seeder, mirroring the conventions already used in `core-php/`

**Wiring** — Next.js rewrites `/api/*` to the PHP server in development
(`http://127.0.0.1:8001`), so the whole CMS is same-origin and the session cookie
just works. In production both halves sit under one docroot.

---

## 6. Data model

```
cms_users              staff and doctors, role, credentials
cms_plans              plan catalogue + cadence rules
cms_patients           child + guardian profile, status
cms_baselines          1:1 with patient — classification, severity, 3 concerns, markers, completeness
cms_cases              patient x plan x doctors, dates, cycle counter, status
cms_schedule_events    activation / adherence / review / founder_review, due + original_due, status
cms_adherence_checks   compliance, refill, parent-concern flag, note
cms_reviews            the forced template, protocol decision + rationale, next review date
cms_review_signoffs    per-role sign-off state
cms_review_goals       tracked goals per cycle
cms_escalations        reasons, notes, level, 7-day SLA, resolution
cms_escalation_steps   the Senior to Founder approval chain
cms_progress_reports   trend, adherence, area scores, objectives, scorecard, shared_at
cms_activity_log       audit trail
cms_medicine_supply    1:1 per case — interval, last delivered, next due
cms_medicine_deliveries append-only delivery log (date, mode, reference, next due at the time)
cms_notifications      in-app notices, unique per (type, case, due date)
```

## 7. API surface

```
POST   /api/auth/login              GET /api/auth/me        POST /api/auth/logout
GET    /api/dashboard
GET    /api/patients                POST /api/patients
GET    /api/patients/{id}           PUT  /api/patients/{id}
PUT    /api/patients/{id}/baseline
POST   /api/patients/{id}/activate      -> 409 when the baseline is incomplete
GET    /api/plans                   POST/PUT/DELETE /api/plans[/{id}]
GET    /api/cases/{id}
GET    /api/schedule                POST /api/schedule/{id}/complete
POST   /api/schedule/{id}/reschedule    -> 422 outside the +/-7-day window
GET    /api/reviews                 GET/PUT /api/reviews/{id}
POST   /api/reviews/{id}/submit         -> 422 when a mandatory field or rationale is missing
POST   /api/reviews/{id}/signoff        -> closes the review, builds the progress report
GET    /api/escalations             POST /api/escalations
POST   /api/escalations/{id}/advance    POST /api/escalations/{id}/resolve
GET    /api/progress/{caseId}/{cycle}   POST /api/progress/{id}/share
GET    /api/team                    GET /api/activity
GET    /api/medicine                GET /api/medicine/{caseId}
POST   /api/medicine/{caseId}/deliver   -> logs it, next_due = delivered + interval, closes the notice
PUT    /api/medicine/{caseId}           -> interval / next due / pause
GET    /api/notifications           GET /api/notifications/summary   (cron-safe, idempotent)
POST   /api/notifications/{id}/read     POST /api/notifications/{id}/dismiss   POST /api/notifications/read-all
```

## 8. Running it

```bash
# 1. backend  (XAMPP MySQL running)
php samvedna-cms/backend/migrations/migrate.php --seed-demo
php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php

# 2. frontend
cd samvedna-cms/frontend
npm install
npm run dev            # http://localhost:3001
```

**Demo logins** — password `samvedna123` for all four:
`founder` · `senior` · `casedoctor` · `coordinator`
