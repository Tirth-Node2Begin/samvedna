# Samvedna Homeopathy — WordPress

A pixel-perfect WordPress conversion of the Samvedna Homeopathy Next.js site
(Autism & ADHD specialist clinic — Dr. Krunal Kosada). The design, layout,
animations, responsiveness, SEO, and the consultation form are reproduced 1:1,
running entirely on **WordPress + PHP** with **no Node.js runtime dependency**.

---

## What's inside

```
wordpress-project/
├── wp-content/
│   ├── themes/
│   │   └── samvedna-homeopathy/      Custom theme (all templates + assets)
│   ├── plugins/
│   │   └── samvedna-core/            CPTs, taxonomies, ACF fields, form handler, seeder
│   └── uploads/                      (created by WordPress)
├── database/                         Schema + import notes
├── README.md
└── installation-guide.md
```

> The original Next.js project is left untouched in the sibling `samvedna-homeopathy/` folder.

---

## Highlights

- **Single-page landing** reproduced as `front-page.php` with 14 section template
  parts (hero, doctor intro, scroll-linked avatar, trust bar, conditions, achievements,
  why-trust, treatment journey, medical team, international reach, blogs, pricing,
  final CTA + form, FAQ) plus a site-wide footer and timed popup.
- **Full animation parity** — GSAP + ScrollTrigger + Lenis smooth scroll (self-hosted,
  no CDN). Scroll reveals, word-by-word headings, the hero→doctor scroll avatar, hero
  pointer parallax, orbital world map, auto-rotating carousels, accordion, and modals
  are all ported from Framer Motion/GSAP to vanilla JS + CSS.
- **Pixel-identical styling** — the same Tailwind v4 design tokens as the original,
  compiled to a single static `assets/css/app.css`.
- **Native consultation form** — server-side validation (mirrors the original Zod
  schema), stores submissions to a custom DB table, emails the clinic, honeypot +
  rate-limiting, success message + configurable redirect, and a timed popup.
- **Editable content** — Custom Post Types (Doctors, Conditions, FAQs, Care Plans,
  Achievements, Video Testimonials) + native Posts for the blog, plus an ACF options
  page for global/section text. Everything falls back to bundled defaults so the site
  is pixel-perfect even before anything is edited.
- **SEO preserved** — meta description, Open Graph, Twitter cards, canonical, and the
  five JSON-LD schemas (MedicalBusiness, Physician, Organization, FAQPage,
  BreadcrumbList). Automatically defers to Yoast / Rank Math / AIOSEO if installed.
- **Complete template set** — `index`, `home`, `single`, `page`, `archive`, `search`,
  `searchform`, `404`, `sidebar`, `comments`, plus a `Thank You` page template.

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | 8.0+ (8.2 recommended) |
| MySQL / MariaDB | 5.7+ / 10.4+ |
| WordPress   | 6.2+ |
| PHP extensions | `mysqli`, `gd` (or `imagick`), `mbstring`, `json` |

**Recommended plugins** (the site works without them, using bundled defaults):

- **Advanced Custom Fields** (free) — edit hero/section text, contact details, social
  links, and SEO from the admin.
- **WP Mail SMTP** — reliable delivery of form notification emails.
- Optional: **Yoast SEO** or **Rank Math**, a caching plugin (LiteSpeed Cache / WP Super
  Cache), **Wordfence**, **Safe SVG**.

---

## Quick start (local, XAMPP/Laragon)

1. Install WordPress into your web root and create a database.
2. Copy `wp-content/themes/samvedna-homeopathy` and `wp-content/plugins/samvedna-core`
   into your WordPress `wp-content/` folder.
3. **Appearance → Themes** → activate **Samvedna Homeopathy**.
4. **Plugins** → activate **Samvedna Core** (this seeds the pages + demo content and
   creates the submissions table). Install **Advanced Custom Fields** + **WP Mail SMTP**
   when prompted.
5. **Settings → Permalinks** → **Post name** → Save.
6. Visit the site — the full landing page renders at the homepage.

See **[installation-guide.md](installation-guide.md)** for the detailed, step-by-step
guide (database import, SMTP, production deployment, troubleshooting).

---

## Editing content

| Content | Where to edit |
|---------|---------------|
| Hero cards, contact, social, SEO, form/popup, footer | **Samvedna Settings** (ACF options page) |
| Doctors | **Doctors** post type |
| Conditions | **Conditions** post type |
| FAQs | **FAQs** post type |
| Care plans / pricing | **Care Plans** post type |
| Achievements | **Achievements** post type |
| Parent video stories | **Testimonials** post type |
| Blog articles | **Posts** |
| Consultation submissions | **Consultations** admin menu (with CSV export) |

If a custom post type has no entries (or ACF isn't installed), the theme renders the
original content from `inc/defaults.php`.

---

## Rebuilding the CSS (only if you change markup/classes)

The compiled stylesheet `assets/css/app.css` is shipped ready to use. To rebuild after
editing templates or `assets/css/src.css`:

```bash
cd wp-content/themes/samvedna-homeopathy
npm install
npm run build:css      # or: npm run watch:css
```

Node is required **only** for this build step, never at runtime. `node_modules/` can be
deleted after building.

---

## Credits / libraries

- [GSAP + ScrollTrigger](https://gsap.com/) — scroll animations (self-hosted).
- [Lenis](https://github.com/darkroomengineering/lenis) — smooth scroll (self-hosted).
- [Tailwind CSS v4](https://tailwindcss.com/) — compiled to static CSS.
- Icons reproduced from [Lucide](https://lucide.dev/) as inline SVG.
