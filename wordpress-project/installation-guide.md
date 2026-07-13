# Installation Guide — Samvedna Homeopathy (WordPress)

This guide covers a fresh local install (XAMPP / Laragon on Windows), activation,
configuration, and production deployment.

---

## 1. Requirements

- **PHP** 8.0+ (8.2 recommended) with extensions: `mysqli`, `gd` (or `imagick`),
  `mbstring`, `json`, `curl`.
- **MySQL** 5.7+ or **MariaDB** 10.4+.
- **WordPress** 6.2 or newer.
- (Optional, for rebuilding CSS) **Node.js** 18+.

---

## 2. Local setup with XAMPP (Windows)

### 2.1 Install the stack
1. Install **XAMPP** (`apachefriends.org`).
2. Open the **XAMPP Control Panel** → **Start** Apache **and** MySQL.
   - Web root: `C:\xampp\htdocs`.

### 2.2 Install WordPress core
1. Download WordPress from `wordpress.org` and unzip it.
2. Put it in the web root, e.g. `C:\xampp\htdocs\samvedna-homeopathy\`.

### 2.3 Create the database
1. Open `http://localhost/phpmyadmin`.
2. **New** → database name `samvedna_homeopathy` → **Create**.

### 2.4 Run the installer
1. Visit `http://localhost/samvedna-homeopathy`.
2. Database settings:
   - Database name: `samvedna_homeopathy`
   - Username: `root`
   - Password: *(empty by default on XAMPP)*
   - Host: `localhost`
3. Finish the install and set your admin username / password.

> **Laragon** users: drop WordPress in `C:\laragon\www\samvedna`, and Laragon will give
> you a pretty URL like `http://samvedna.test`. Steps are otherwise identical.

---

## 3. Install the theme & plugin

Copy these two folders from this project into your WordPress install:

```
wp-content/themes/samvedna-homeopathy   →  <webroot>/samvedna-homeopathy/wp-content/themes/
wp-content/plugins/samvedna-core        →  <webroot>/samvedna-homeopathy/wp-content/plugins/
```

(You do **not** need to copy `node_modules/` — it's only used to rebuild CSS.)

### 3.1 Activate
1. **wp-admin → Appearance → Themes** → activate **Samvedna Homeopathy**.
2. **wp-admin → Plugins** → activate **Samvedna Core**.
   - On activation it: creates the **Home**, **Blog**, and **Thank You** pages, sets the
     Home page as the static front page, seeds demo content (doctors, conditions, FAQs,
     plans, achievements, stories, sample posts), and creates the consultation
     submissions table.

### 3.2 Recommended plugins
- **Plugins → Add New** → install & activate **Advanced Custom Fields** (lets you edit
  hero/section text, contact details, social links, SEO from the admin).
- Install & activate **WP Mail SMTP** for reliable form emails (see §6).

### 3.3 Permalinks (required)
**Settings → Permalinks** → choose **Post name** → **Save Changes**.
(This is needed for clean blog URLs and the AJAX form.)

### 3.4 Confirm the homepage
If the seeder didn't set it automatically: **Settings → Reading** →
"Your homepage displays" → **A static page** → Homepage: **Home**, Posts page: **Blog**.

Visit the site root — the full landing page should render.

---

## 4. Editing content

| What | Where |
|------|-------|
| Hero text, contact, social links, SEO, form/popup, footer text | **Samvedna Settings** (left admin menu, needs ACF) |
| Doctors, Conditions, FAQs, Care Plans, Achievements, Testimonials | their post-type menus |
| Blog posts | **Posts** |
| Logo | **Appearance → Customize → Site Identity → Logo** |
| Navigation | **Appearance → Menus** → assign to **Primary Menu** (optional; defaults to in-page section links) |
| Form submissions | **Consultations** menu (view + **Export CSV**) |

Everything falls back to the bundled defaults when empty, so the site is never blank.

---

## 5. The consultation form

- Submissions are validated server-side, stored in the table
  `wp_samvedna_inquiries`, and emailed to the site admin (or to a custom address set in
  **Samvedna Settings → Form & popup → Form notification email**).
- Behaviour after submit (redirect to Thank You page / appointment URL / WhatsApp /
  custom / none) and the popup delay are configurable on the same settings tab.
- Spam protection: nonce, honeypot field, and per-IP rate limiting.

---

## 6. Email (WP Mail SMTP)

Local servers usually can't send mail, so configure SMTP for real delivery:

1. Install **WP Mail SMTP**.
2. **WP Mail SMTP → Settings** → choose a mailer (Gmail, SMTP.com, Brevo, or custom SMTP).
3. Set the **From Email** to a domain mailbox (e.g. `noreply@samvednahomeopathy.com`).
4. Send a test email from the plugin's **Email Test** tab.

---

## 7. SEO

The theme outputs meta description, Open Graph, Twitter cards, canonical, and JSON-LD
(MedicalBusiness, Physician, Organization, FAQPage, BreadcrumbList) automatically.
If you install **Yoast SEO** or **Rank Math**, the theme detects it and steps aside so
there's no duplication — manage SEO from that plugin instead.

---

## 8. Production deployment

1. **Build assets** are already included (`assets/css/app.css`, self-hosted JS). No build
   needed on the server.
2. Upload WordPress + the theme + plugin to your host (cPanel, Cloudways, Kinsta, etc.).
3. Create a production MySQL database and user; update `wp-config.php`
   (`DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`).
4. Migrate content:
   - **Fresh start:** just activate the plugin (it seeds demo content), then edit.
   - **Move your local site:** use a migration plugin (All-in-One WP Migration / Duplicator)
     **or** export/import the database (see `database/`), then run a search-replace of the
     site URL (`https://oldurl` → `https://samvednahomeopathy.com`) with WP-CLI:
     `wp search-replace 'http://localhost/samvedna-homeopathy' 'https://samvednahomeopathy.com'`.
5. Set **Settings → General** site URLs to the production domain.
6. **Settings → Permalinks → Save** to flush rewrite rules.
7. Configure SMTP, install a caching plugin and a security plugin, and enable HTTPS.
8. Verify each page (see §9).

---

## 9. Post-install verification checklist

- [ ] Homepage renders all 14 sections; hero swan + doctor avatar animate on scroll.
- [ ] Smooth scrolling and the sticky/auto-hiding navbar work.
- [ ] Mobile menu opens/closes; layout is correct on mobile, tablet, desktop.
- [ ] Doctor cards open the profile modal; video thumbnails open the video modal.
- [ ] Treatment-journey and blog carousels auto-rotate; FAQ accordion toggles.
- [ ] Consultation form: validation errors show; a valid submission saves (check
      **Consultations**), emails the admin, shows the success message, and redirects.
- [ ] Timed popup appears once per session.
- [ ] Blog index, single posts, categories, search, and 404 all render correctly.
- [ ] View source: meta tags + JSON-LD present (or your SEO plugin's output).

---

## 10. Troubleshooting

| Symptom | Fix |
|---------|-----|
| Unstyled page | Ensure `assets/css/app.css` exists; clear caches; check the theme is active. |
| Animations missing | Confirm `assets/js/vendor/*.js` and `assets/js/main.js` load (browser console / Network tab). |
| Form returns "session expired" | Clear cache; the nonce expires after 12–24h — reload the page. |
| Form emails not arriving | Configure WP Mail SMTP (§6). Submissions still save to **Consultations**. |
| Demo content didn't appear | Deactivate/reactivate **Samvedna Core**, or visit any admin page (seeding also runs on `admin_init`). Ensure the theme folder is named `samvedna-homeopathy`. |
| "Samvedna Settings" missing | Install & activate **Advanced Custom Fields**. |
| 404s on blog/posts | **Settings → Permalinks → Save** (flush rewrite rules). |
