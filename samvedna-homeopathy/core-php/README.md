# Samvedna Homeopathy — Core PHP Backend & Admin

A dependency-free **Core PHP** backend for the Samvedna Homeopathy Next.js site.
It provides an admin panel (CRUD) for **Blogs**, **Video Testimonials** and
**Doctor profiles**, plus a read-only JSON API the front end can consume. Data is
stored in MySQL/MariaDB and shared with the Next.js app (same `samvedna_homeopathy`
database, including the existing `inquiries` table).

## Requirements

- PHP 8.1+ with `pdo_mysql`, `gd`, `fileinfo` (all present in XAMPP)
- MySQL / MariaDB (XAMPP)

## 1. Configure

Defaults target XAMPP (`host=localhost`, `user=root`, empty password,
`db=samvedna_homeopathy`) and already match the Next.js app's `.env.local`.
To override on a machine, create `config/config.local.php`:

```php
<?php
return [
    'DB_USER' => 'root',
    'DB_PASS' => 'yourpassword',
];
```

## 2. Create the database + tables (and seed sample data)

From the Next.js project root (`samvedna-homeopathy/`), with MySQL running:

```bash
php core-php/migrations/migrate.php
```

This creates the database if missing, applies `migrations/schema.sql`, seeds a
default admin and imports the sample blogs / testimonials / doctors. It is safe to
re-run (it skips seeding when data already exists).

**Default admin login:** `admin` / `admin123` — change the password after first login.

## 3. Run the backend

```bash
php -S localhost:8080 -t core-php core-php/router.php
```

- Admin panel: <http://localhost:8080/admin>
- JSON API: `/api/blogs.php`, `/api/testimonials.php`, `/api/doctors.php`

### Through the Next.js dev server

`next.config.ts` already proxies these paths to the PHP server, so while
`pnpm dev` **and** the PHP server both run you can also use:

- Admin: <http://localhost:3000/admin>
- API: `/php-api/blogs.php`, `/php-api/testimonials.php`, `/php-api/doctors.php`
- Uploaded images: `/uploads/<file>`

## Structure

```
core-php/
├── config/config.php          # DB credentials, paths, settings (+ optional config.local.php)
├── includes/
│   ├── db.php                 # PDO singleton
│   ├── helpers.php            # escaping, CSRF, flash, slugs, image uploads, JSON
│   ├── auth.php               # session login/logout, require_login guard
│   └── models/                # Blog, VideoTestimonial, Doctor (prepared-statement CRUD)
├── migrations/
│   ├── schema.sql             # table definitions
│   └── migrate.php            # create DB + apply schema + seed
├── admin/
│   ├── login.php / logout.php / index.php (dashboard)
│   ├── partials/              # shared header (sidebar/topbar) + footer
│   ├── assets/admin.css
│   ├── blogs/                 # index, form (create/edit), save, delete
│   ├── testimonials/          # index, form, save, delete
│   └── doctors/               # index, form, save, delete
├── api/                       # public read-only JSON endpoints
└── uploads/                   # admin-uploaded images (gitignored)
```

## Security notes

- Passwords hashed with `password_hash()` (bcrypt); verified with `password_verify()`.
- All state-changing POSTs require a CSRF token (`verify_csrf()`).
- Every admin page calls `require_login()`; sessions are HttpOnly + SameSite=Lax.
- All DB access uses PDO prepared statements; all output escaped with `e()`.
- Uploads validated by real MIME type (`finfo`), size-capped (4 MB), and stored with
  randomized filenames.
- Change the default admin password and set `APP_ENV=production` in production.

## Data shapes

The API returns objects matching the Next.js TypeScript types in `types/index.ts`
(`BlogPost`, `VideoTestimonial`, and `TeamMember` with a nested `profile`), so the
front end can swap its static `constants/*.ts` for live data with minimal changes.
