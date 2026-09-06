# Samvedna CMS

Clinical case management for Samvedna Homeopathy — the working build of the
six-screen flow in [`samvedna_cms_page_flow.html`](samvedna_cms_page_flow.html).

**The full specification — every screen, lock, mandatory field and feature — is in
[`SAMVEDNA_CMS_FLOW.md`](SAMVEDNA_CMS_FLOW.md).**
Read that first; this file is just how to run it.

**The UI follows the house style guide in [`updated style.md`](updated%20style.md);
[`DESIGN.md`](DESIGN.md) records how it is wired into this codebase** — the
tokens to import, the components to reuse, and the rules not to break.

```
samvedna-cms/
├── frontend/   Next.js 15 + React 19 + Tailwind 4 + Framer Motion + Lenis + DM Sans
└── backend/    Core PHP only (no framework, no Composer) + MySQL
```

---

## Run it

**One command** — starts MySQL, the PHP API and the frontend, waits until both
actually answer, and prints the URLs:

```
samvedna-cms\start-cms.bat            normal start
samvedna-cms\start-cms.bat -Fresh     rebuild the demo data first
samvedna-cms\start-cms.bat -Seed      migrate + seed, keeping existing data
samvedna-cms\start-cms.bat -Stop      kill anything left on the CMS ports
```

Ctrl+C stops both halves. If a port is busy the script walks to the next free
one and tells the frontend where the API actually landed, so nothing dies with
`EADDRINUSE`. `-Stop` is there for when a terminal was closed hard and the
servers leaked.

The manual route below still works if you prefer separate terminals.

---

**1. Backend** — needs MySQL running (XAMPP is fine).

```bash
# from the project root (samvedna-homeopathy/)
php samvedna-cms/backend/migrations/migrate.php --seed-demo
php -S 127.0.0.1:8001 -t samvedna-cms/backend samvedna-cms/backend/router.php
```

`--fresh --seed-demo` drops the `cms_*` tables and rebuilds the demo from scratch.
Without `--seed-demo` you get the schema, the staff accounts and the plan
catalogue, but no patients.

Check it: <http://127.0.0.1:8001/> should answer `{"status":"ready", ...}`.

**2. Frontend**

```bash
cd samvedna-cms/frontend
npm install
npm run dev          # http://localhost:3001
```

If 3001 is taken, `npx next dev -p 3002` works — only `PHP_API_URL` matters, and
that points at the backend, not at this port.

> Do not run `npm run build` while `npm run dev` is live: they share `.next` and
> the dev server will start throwing `Cannot find module './xxx.js'`. Stop dev
> first, or delete `.next` and restart it afterwards.

**Sign in** with `founder`, `senior`, `casedoctor` or `coordinator` —
password `samvedna123` for all four. The login screen lists them.

---

## How the two halves talk

`frontend/next.config.ts` rewrites `/api/*` to `http://127.0.0.1:8001`, so the
browser only ever sees one origin and the PHP session cookie works with no token
plumbing. Override the backend URL with `PHP_API_URL` in `frontend/.env.local`.

In production both halves sit under one docroot: `/api` served by PHP, everything
else by the exported Next app — the same shape as the marketing site's
`core-php/` deployment.

---

## Demo data

`--seed-demo` builds five patients, each one there to make a different screen of
the flow real. Dates are anchored to *today*, so the demo never goes stale:

| Patient | State | Shows |
| --- | --- | --- |
| **Aarav Mehta** | Standard plan, day 60 | Steps 1–4 — the cycle-1 review sits in *awaiting Senior sign-off*, exactly where the flow mock ends |
| **Ishaan Verma** | Standard plan, day 130 | Step 6 — cycle 1 closed, progress dashboard generated |
| **Diya Kulkarni** | Premium plan, day 40 | Step 5 — an open escalation inside its 7-day SLA |
| **Kabir Shah** | Draft intake | Step 1's lock — baseline 20% complete, activation blocked |
| **Aanya Nair** | Starter plan, day 20 | A simple single-cycle case |

Sign in as `senior` and open Aarav's cycle-1 review to watch the sign-off close
it and generate the dashboard — that is the whole flow in one click.

**Medicine cycle demo.** The seed also places every case at a different point in
its 15-day medicine cycle: Aarav is **due today** (the bell shows a reminder the
moment you sign in), Diya is **2 days overdue**, Ishaan is due in 3 days, Aanya
has a fresh supply. Open **Medicine supply**, hit *Mark delivered* on Aarav, and
watch the next-due date roll to +15 days and the notification close.

Reminders are generated on the first API request of each day — no cron needed
for the demo. For production, point a morning cron at
`GET /api/notifications/summary` so notices exist before anyone signs in.

---

## Where the rules live

The clinical rules are enforced on the server, not just in the forms, so they
cannot be bypassed:

| Rule | File |
| --- | --- |
| Baseline completeness / activation gate | `backend/includes/models/Patient.php` → `Baseline::evaluate()` |
| Schedule generation + the ±7-day reschedule window | `backend/includes/services/ScheduleGenerator.php` |
| The forced review template + mandatory rationale | `backend/includes/models/Review.php` → `Review::validate()` |
| Sign-off chain and review closure | `backend/api/handlers/reviews.php` |
| Escalation SLA and Senior → Founder chain | `backend/includes/models/Escalation.php` |
| Progress dashboard + audit scorecard | `backend/includes/services/ProgressBuilder.php` |

The React forms mirror each rule so the UI can grey out a blocked button, but
every one is re-checked server-side and returns per-field messages the forms
paint back onto the exact inputs that failed.

---

## Config

`backend/.env` (copy from `.env.example`):

```
DB_HOST=localhost
DB_NAME=samvedna_homeopathy
DB_USER=root
DB_PASS=
CMS_DEMO=1          # 0 hides the demo banner and the demo account list
APP_ENV=development
```

Every CMS table is prefixed `cms_`, so this can share the marketing site's
database without colliding with `blogs`, `doctors`, `inquiries` and friends.
Point `DB_NAME` somewhere else to keep them fully separate.

Schema changes are additive: `migrate.php` adds any missing columns in place
(checking `information_schema` first), so you can upgrade an existing database
without `--fresh`. `--fresh` only ever drops `cms_*` tables — the marketing
site's content is never touched.

---

## If MySQL will not start

XAMPP's MariaDB can corrupt its InnoDB tablespace if it is force-killed. The
symptom in the error log is:

```
InnoDB: Page [...] log sequence number N is in the future!
InnoDB: invalid undo header offset 0
InnoDB: Plugin initialization aborted with error Data structure corruption
```

Recovery, in order:

1. **Back up the data directory first** — `C:\xampp\mysql\data`. Never skip this.
2. **Clear stuck processes.** A half-dead `mysqld` keeps `ibdata1` locked, and
   every new instance then dies with *"ibdata1 must be writable"*. If
   `taskkill /F /PID <pid>` says *Access is denied*, it needs an **elevated**
   PowerShell — or just reboot, which clears it.
3. **Get the data out.** Add `innodb_force_recovery=5` under `[mysqld]` in
   `C:\xampp\mysql\bin\my.ini`, start MySQL, then:
   `mysqldump -u root --all-databases --force > all.sql`
4. **Rebuild.** Remove the `innodb_force_recovery` line, move `ib_logfile0` /
   `ib_logfile1` aside so InnoDB recreates them, and restart. If it still fails,
   restore from the dump into a fresh data directory.

`start-cms.bat` detects a stuck `mysqld` and prints the exact `taskkill` command
for it rather than failing silently.
