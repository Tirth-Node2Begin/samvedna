# Database

## Do I need to import a database?

**No.** This conversion does not ship a fixed SQL dump because all content is
created programmatically:

- On activation, the **Samvedna Core** plugin seeds the pages (Home / Blog / Thank You),
  the custom post types (Doctors, Conditions, FAQs, Care Plans, Achievements,
  Testimonials), sample blog posts, and the consultation submissions table.
- WordPress itself creates its core tables during the normal install wizard.

So a standard WordPress install + activating the theme and plugin gives you a fully
populated site. See `../installation-guide.md`.

## Files here

- **`schema.sql`** — the `*_samvedna_inquiries` table definition (created automatically
  by the plugin; included for reference / manual setup).

## Moving an existing site between environments

If you've customised content locally and want to move it to production:

1. **Easiest:** use a migration plugin — *All-in-One WP Migration* or *Duplicator* —
   which packages the database + uploads together.
2. **Manual:**
   - Export the WordPress database from phpMyAdmin (Export → Quick → SQL).
   - Import it into the production database (Import → choose file).
   - Run a URL search-replace so links/serialized data point at the new domain:
     ```bash
     wp search-replace 'http://localhost/samvedna-homeopathy' 'https://samvednahomeopathy.com' --all-tables
     ```
   - Copy `wp-content/uploads/` to the new server.
   - Visit **Settings → Permalinks → Save** to flush rewrite rules.

> The `*_samvedna_inquiries` table holds form submissions. Include it when exporting
> if you want to keep stored leads; it is not needed for the site to function.
