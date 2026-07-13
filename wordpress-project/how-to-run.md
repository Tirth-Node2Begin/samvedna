# How to Run & Connect This Project (WordPress)

A plain, practical guide to starting the site and connecting this project's code to
WordPress. For deep topics (database import, SMTP email, production deploy,
troubleshooting) see **[installation-guide.md](installation-guide.md)**.

---

## 1. First, understand the setup (important)

This project **is** a WordPress site — it is *not* a separate app that "talks to"
WordPress over an API. "Connecting it to WordPress" just means:

> putting the **theme** and **plugin** folders inside WordPress's `wp-content/`, then
> **activating** them in the admin.

There are **two copies** of the site on your machine, and they are independent:

| Copy | Location | Purpose |
|------|----------|---------|
| **Source / repo** (edit here) | `E:\EB projects\samvedna-homeopathy\wordpress-project\` | Where you and the editor change code. |
| **Live site** (runs here) | `C:\xampp\htdocs\samvedna-homeopathy\` | The actual WordPress that XAMPP serves in the browser. |

➡️ **Editing a file in the repo does NOT change the live site.** You must copy the
changed files over (see **§7 Sync changes**). This is already how the site has been set up.

---

## 2. What's being used (theme, plugin, libraries)

### Theme — `Samvedna Homeopathy` (v1.0.0)  ✅ active
- Folder: `wp-content/themes/samvedna-homeopathy/`
- The whole front-end: all page templates, the 14 landing-page sections, styles, and JS.
- Requires **PHP 8.0+**. Text domain: `samvedna`.

### Plugin — `Samvedna Core` (v1.0.0)  ⚠️ currently NOT active
- Folder: `wp-content/plugins/samvedna-core/`
- Provides: Custom Post Types (Doctors, Conditions, FAQs, Care Plans, Achievements,
  Testimonials), the **consultation form** handler (saves to DB + emails admin +
  "Consultations" admin screen), ACF field groups, and a **content seeder**.
- **Because it is inactive right now:** the booking/consultation form will not submit,
  and demo content isn't auto-seeded. The site still looks correct because the theme
  falls back to built-in defaults. **Activate it** to get the form + editable CPT content
  (see §6).

### Recommended plugins (optional — site works without them)
| Plugin | Why |
|--------|-----|
| **Advanced Custom Fields (ACF)** | Unlocks the **Samvedna Settings** page to edit hero/section text, contact info, social links, SEO from wp-admin. |
| **WP Mail SMTP** | Makes form-notification emails actually deliver (localhost can't send mail). |
| *Akismet* | Installed by default with WordPress; not required. |

### Build tool (not needed at runtime)
- **Tailwind CSS v4** compiles `assets/css/src.css` → `assets/css/app.css` via **Node.js**.
  Node is used **only** when you change styles/markup. The compiled `app.css` ships ready.

### Front-end libraries (self-hosted in the theme, no CDN)
- **GSAP + ScrollTrigger** (scroll animations), **Lenis** (smooth scroll), **Lucide** icons
  (inline SVG). All bundled in `assets/js/`.

---

## 3. The project folder structure — what each part does

This repo is **not** a full WordPress install. It only contains the two folders you copy
into a real WordPress (`themes/samvedna-homeopathy` and `plugins/samvedna-core`), plus
docs and reference SQL. WordPress core, `wp-config.php`, and `wp-content/uploads/` live in
the **live** site (`C:\xampp\htdocs\…`), never here.

### 3.1 Repo root
```
wordpress-project/
├─ wp-content/
│  ├─ themes/samvedna-homeopathy/   ← the THEME  (everything the visitor sees)
│  └─ plugins/samvedna-core/        ← the PLUGIN (data, form, admin screens)
├─ database/                        ← reference SQL + notes (no import needed — see §6)
├─ how-to-run.md                    ← this file (run + sync)
├─ installation-guide.md            ← deep install / configure / deploy guide
└─ README.md                        ← project overview
```
👉 Only the **theme** and **plugin** folders are copied to the live site (§7). Everything
else is documentation. `node_modules/` is dev-only and must **never** be copied.

### 3.2 Theme — `wp-content/themes/samvedna-homeopathy/`
The front-end. WordPress picks a template file by its normal "template hierarchy" (which
type of page is being viewed), and that file builds the page from the partials in
`template-parts/`, pulling content via `inc/`.

```
samvedna-homeopathy/
├─ style.css            theme header (name/version) — WordPress reads this to list the theme
├─ functions.php        entry point: loads every file in inc/
├─ header.php           top of every page (<head>, opening markup, navbar)
├─ footer.php           bottom of every page (footer, popups, closing scripts)
├─ front-page.php       the LANDING PAGE — assembles the 14 sections in order
├─ index.php            generic fallback template (required by WP)
├─ home.php             blog index (the "Posts" page)
├─ single.php           one blog post
├─ page.php             a generic WordPress page
├─ page-thank-you.php   the Thank-You page shown after a form submit
├─ archive.php          category / date / author listings
├─ search.php           search-results page
├─ 404.php              "not found" page
├─ searchform.php       the search box markup
├─ sidebar.php          blog sidebar
├─ comments.php         comments area for posts
├─ inc/                 PHP logic, split by concern (see 3.3)
├─ template-parts/      reusable markup partials (see 3.4)
├─ assets/              compiled CSS, JS, images, fonts (see 3.5)
├─ package.json         Tailwind build scripts (dev only)
├─ tailwind.config.js   Tailwind config (dev only)
└─ node_modules/        Tailwind toolchain (dev only — DO NOT copy to live)
```

### 3.3 `inc/` — the theme's PHP logic
| File | What it does |
|------|--------------|
| `setup.php` | Theme supports, menus, image sizes (`after_setup_theme`). |
| `enqueue.php` | Registers & loads the CSS and JS on the front-end. |
| `defaults.php` | Baked-in default content (ports the original Next.js data) so the site is **never empty** before any CPT/ACF content exists. |
| `content.php` | Content resolvers — return CPT entries when published, otherwise the `defaults.php` data. |
| `helpers.php` | Field getters that work **with or without ACF**, falling back to defaults. |
| `template-tags.php` | Markup helpers: inline SVG icons, scroll-reveal wrappers, animated headings. |
| `seo.php` | Meta description, Open Graph, Twitter cards, JSON-LD. Auto-disables when Yoast/Rank Math/AIOSEO is active. |

### 3.4 `template-parts/` — reusable markup
| Folder / file | What's inside |
|---------------|---------------|
| `sections/` | The **14 landing-page sections** (`hero`, `conditions`, `faq`, `pricing`, `treatment-journey`, …). `front-page.php` includes these in order. |
| `cards/` | Repeatable cards: `doctor-card`, `condition-card`, `blog-card`. |
| `content/` | Blog/page body partials: `content-single`, `content-page`, `content-none`, `content`. |
| `global/` | Site-wide UI used across pages: `navbar`, `consultation-form`, `form-popup`, `video-modal`. |
| `loop-grid.php` | Shared grid wrapper used by listing loops. |

### 3.5 `assets/` — static files served to the browser
| Folder | What's inside |
|--------|---------------|
| `css/` | `src.css` (Tailwind **source** you edit) → `app.css` (the **compiled** file that actually loads). |
| `js/` | `main.js` (theme behaviour) and `vendor/` (`gsap.min.js`, `ScrollTrigger.min.js`, `lenis.min.js` — self-hosted, no CDN). |
| `images/` | Logos, hero artwork, doctor photos (`.webp` / `.png` / `.jpg`). |
| `fonts/` | Self-hosted web fonts. |

### 3.6 Plugin — `wp-content/plugins/samvedna-core/`
The data + behaviour layer. It owns the custom post types, the form, and the admin
screens, so content and submissions live in WordPress's database instead of being hard-coded.

```
samvedna-core/
├─ samvedna-core.php        plugin header + bootstrap: loads includes/, registers the activation hook
├─ includes/
│  ├─ post-types.php        registers the CPTs + taxonomies (Doctors, Conditions, FAQs, Care Plans, Achievements, Testimonials)
│  ├─ acf-fields.php        ACF options page + field groups → the "Samvedna Settings" admin page
│  ├─ consultation-form.php AJAX form handler: validate → save to DB → email clinic → return JSON
│  ├─ admin-inquiries.php   the "Consultations" admin screen + CSV export
│  └─ seeder.php            one-time seeder: creates Home/Blog/Thank-You pages, demo CPT content, and the submissions table
└─ admin/                   reserved for admin-only assets (currently empty)
```
On **activation**, `seeder.php` runs once to populate the site and create the
`wp_samvedna_inquiries` table; that's what makes the consultation form work (§6).

### 3.7 `database/`
| File | What it is |
|------|------------|
| `schema.sql` | The `wp_samvedna_inquiries` table definition — created automatically by the plugin; included for reference / manual setup. |
| `README.md` | Explains why **no DB import is needed** and how to migrate an existing site between environments. |

### 3.8 How a page request actually flows
1. A visitor opens the homepage → WordPress chooses **`front-page.php`**.
2. It calls **`get_header()`** (`header.php` → navbar), then includes each
   **`template-parts/sections/*.php`** in order.
3. Each section asks **`inc/helpers.php` / `inc/content.php`** for its data, which returns
   **CPT/ACF content if present, otherwise `inc/defaults.php`** — so the page is never blank.
4. **`inc/enqueue.php`** has already loaded `assets/css/app.css` and `assets/js/*`, so
   styles, icons, and the GSAP/Lenis animations render.
5. **`get_footer()`** (`footer.php`) closes the page and prints the popup + modals from
   `template-parts/global/`.
6. Submitting the form posts via AJAX to the **plugin's** `consultation-form.php`, which
   validates, stores the row, emails the clinic, and returns JSON.

---

## 4. Start WordPress (do this every time)

1. Open the **XAMPP Control Panel**.
2. Click **Start** on **Apache** and **Start** on **MySQL** (both must be green).
3. Open the site in a browser:
   - **Website:** http://localhost/samvedna-homeopathy
   - **Admin:** http://localhost/samvedna-homeopathy/wp-admin/

That's it — the site is "running." If both services are green and the page loads, you're connected.

> If MySQL won't start, another service may be using port 3306; if Apache won't start,
> something is using port 80 (often Skype or another web server). Change the port in XAMPP
> or stop the conflicting app.

---

## 5. Your install's reference values

| Item | Value |
|------|-------|
| Site URL | `http://localhost/samvedna-homeopathy` |
| Admin URL | `http://localhost/samvedna-homeopathy/wp-admin/` |
| WordPress folder | `C:\xampp\htdocs\samvedna-homeopathy\` |
| Database name | `samvedna_homeopathy` |
| DB user / password | `root` / *(empty)* |
| DB host | `localhost` |
| Table prefix | `wp_` |
| phpMyAdmin | http://localhost/phpmyadmin |
| PHP CLI (for scripts) | `C:\xampp\php\php.exe` |

---

## 6. Connecting the project to a *fresh* WordPress (first-time only)

Skip this if WordPress is already set up (yours is). Do it only on a new machine.

1. **Install the stack:** install XAMPP, start **Apache + MySQL**.
2. **Get WordPress:** download from wordpress.org, unzip into
   `C:\xampp\htdocs\samvedna-homeopathy\`.
3. **Create the database:** open http://localhost/phpmyadmin → **New** → name it
   `samvedna_homeopathy` → **Create**.
4. **Run the WP installer:** open http://localhost/samvedna-homeopathy and fill in:
   DB name `samvedna_homeopathy`, user `root`, password *(empty)*, host `localhost`.
   Then set your admin username/password.
5. **Copy this project's theme + plugin** into the new WordPress (see §7 for the commands):
   ```
   wp-content/themes/samvedna-homeopathy   →  htdocs/samvedna-homeopathy/wp-content/themes/
   wp-content/plugins/samvedna-core        →  htdocs/samvedna-homeopathy/wp-content/plugins/
   ```
   (Do **not** copy `node_modules/`.)
6. **Activate in wp-admin:**
   - **Appearance → Themes** → activate **Samvedna Homeopathy**.
   - **Plugins** → activate **Samvedna Core**  ← this seeds the pages + demo content and
     creates the consultation table.
7. **Permalinks:** **Settings → Permalinks** → **Post name** → **Save** (needed for clean
   URLs and the AJAX form).
8. Visit the site root — the full landing page renders.

### To activate the plugin on *your current* site
**wp-admin → Plugins → Samvedna Core → Activate.** On activation it creates the
Home/Blog/Thank-You pages, seeds the demo CPT content, and creates the
`wp_samvedna_inquiries` table. (This is what makes the consultation form work.)

---

## 7. Sync your changes from the repo → live site

Whenever a file is changed in the **repo** (`E:\…`), copy it to the **live** site
(`C:\xampp\htdocs\…`) for the change to appear in the browser.

### A) If you changed styles or Tailwind classes — rebuild CSS first
```powershell
cd "E:\EB projects\samvedna-homeopathy\wordpress-project\wp-content\themes\samvedna-homeopathy"
npm install          # first time only
npm run build:css    # regenerates assets/css/app.css
```

### B) Copy the theme and plugin to the live site
Run in **PowerShell** (robocopy skips `node_modules` and `.git`):
```powershell
# Theme
robocopy "E:\EB projects\samvedna-homeopathy\wordpress-project\wp-content\themes\samvedna-homeopathy" `
         "C:\xampp\htdocs\samvedna-homeopathy\wp-content\themes\samvedna-homeopathy" `
         /E /XD node_modules .git

# Plugin
robocopy "E:\EB projects\samvedna-homeopathy\wordpress-project\wp-content\plugins\samvedna-core" `
         "C:\xampp\htdocs\samvedna-homeopathy\wp-content\plugins\samvedna-core" `
         /E /XD .git
```
> `robocopy` exit codes 0–7 mean success (it reports `1` when files were copied — that's
> normal, not an error).

### C) See the change
Refresh the browser. For CSS/JS changes do a **hard refresh** (`Ctrl + F5`) to bypass the
browser cache.

---

## 8. Where to edit content (after the plugin is active)

| Content | Where |
|---------|-------|
| Hero text, contact, social links, SEO, form/popup, footer | **Samvedna Settings** (needs ACF) |
| Doctors / Conditions / FAQs / Care Plans / Achievements / Testimonials | their own admin menus |
| Blog posts | **Posts** |
| Logo | **Appearance → Customize → Site Identity** |
| Navigation | **Appearance → Menus** (assign to **Primary Menu**) |
| Form submissions | **Consultations** (with CSV export) |

---

## 9. Quick troubleshooting

| Symptom | Fix |
|---------|-----|
| Browser can't open the site | Start **Apache + MySQL** in XAMPP (both green). |
| Changes not showing | You edited the repo but didn't copy to `htdocs` — run §7. Then hard-refresh. |
| New CSS classes have no style | Rebuild CSS (`npm run build:css`) then copy `app.css` over. |
| Consultation form does nothing | Activate **Samvedna Core** (§6). |
| "Samvedna Settings" menu missing | Install & activate **Advanced Custom Fields**. |
| Form emails never arrive | Configure **WP Mail SMTP** (submissions still save under **Consultations**). |
| 404 on blog posts | **Settings → Permalinks → Save** to flush rewrite rules. |

---

## 10. Daily routine (TL;DR)

1. Start **Apache + MySQL** in XAMPP.
2. Open http://localhost/samvedna-homeopathy (and `/wp-admin/` to manage).
3. Edit code in the repo (`E:\…`).
4. Rebuild CSS if styles changed → **copy theme/plugin to `htdocs`** (§7).
5. Hard-refresh the browser.
