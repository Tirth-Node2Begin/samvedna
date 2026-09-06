# Samvedna CMS — design system

The CMS UI is built to the house style guide in
[`updated style.md`](updated%20style.md). This file records how that guide is
wired into *this* codebase — what to import, what to copy, and what not to touch.

> **Design DNA:** indigo + violet accents on a hueless white-grey canvas, navy
> ink, DM Sans, heavy weights, generous radii, whisper-light shadows, floating
> chrome. Light mode only.

---

## Where a style comes from

1. **Tailwind utilities in JSX** — the default, ~95% of styling.
2. **Tokens in [`app/globals.css`](frontend/app/globals.css)** — CSS custom
   properties, the `.field-control` recipe, scrollbars, the reduced-motion guard.
3. **Exported class strings in [`lib/tokens.ts`](frontend/lib/tokens.ts)** —
   *reach for this whenever two files must look identical.*
4. **Module-scoped geometry constants** — `GUTTER` / `COLLAPSED_W` /
   `EXPANDED_W` / `BAR_H` in `lib/tokens.ts`, imported by the rail, the bar and
   the boot skeleton so one number lives in one place.

---

## Colour

```css
--background:        #f4f4f5;  /* the canvas — deliberately HUELESS */
--foreground:        #0f172a;  /* slate-900, default ink */
--n2b-chrome:        #f6f6fe;  /* rail + top bar (96% white / 4% indigo) */
--n2b-chrome-hover:  #efeffb;
--n2b-chrome-active: #e8e8f7;
--n2b-sb:            86px;     /* shell left margin, rewritten by Sidebar */
```

* Neutrals are **slate**. Brand is **indigo**. Success **emerald**, warning
  **amber**, destructive **rose**, secondary accent **violet**.
* `gray` / `blue` / `red` / `green` are **absent from the Tailwind config** —
  they are legacy duplicates of the four above and must not appear in new code.
* `text-navy` (`#1E3A5F`) is the heading colour. `text-slate-400` is the
  eyebrow / label colour.
* **The tint lives in the background, never the ink.** Chrome surfaces are white
  + 4% indigo; the text on them stays slate.
* Dynamic accents come from the `Record<Tone, string>` maps in `lib/tokens.ts`
  (`TONE_SOFT`, `TONE_PANEL`, `TONE_TILE`, `TONE_GLYPH`, `TONE_BAR`). Tailwind's
  JIT cannot see an interpolated class — never build one.

---

## Typography

DM Sans, loaded once in `app/layout.tsx` and self-hosted, so there are no
runtime font requests.

| Recipe | Class |
| --- | --- |
| Page H1 | `text-2xl font-black tracking-tight text-navy sm:text-3xl` (`PAGE_H1`) |
| Card / section title | `text-base font-bold text-navy` |
| Eyebrow | `text-[10px] font-black uppercase tracking-[0.2em] text-slate-400` (`EYEBROW`) |
| Body | `text-sm font-medium text-slate-500`, `text-[12.5px]` when dense |
| Numeric | always `tabular-nums`, usually `font-black text-navy` |

The app is **heavy by default** — body copy is `font-medium` at minimum, labels
are `font-bold`, numbers are `font-black`.

**The one exception is the shell.** Idle nav rows are `font-normal text-slate-500`
and the active row is only `font-medium text-slate-900`. Do not "fix" this to
bold — the calm is the design.

---

## The shell

```
<div class="flex h-screen overflow-hidden bg-background">   ← canvas
  <Sidebar/>                                                 ← fixed z-50 floating CARD
  <div class="lg:ml-[var(--n2b-sb)] flex flex-1 flex-col overflow-hidden">
    <AppHeader/>                                             ← floating rounded BAR
    <main id="app-scroll" class="flex-1 overflow-y-auto">    ← ★ THE SCROLL CONTAINER
```

Three facts that break code if you forget them:

1. **`<main>` is the real scroll container, not the document.** Anything that
   locks scroll must freeze `<main>` too — `Modal` does, and pads back the
   scrollbar gutter. Lenis is pointed at `#app-scroll` for the same reason.
2. **The left margin follows the *pinned* rail width only.** A hover-expand
   overlays the page deliberately; a cursor passing over the rail must never
   reflow the content.
3. **`--n2b-sb` has a static 86px default** so the first paint is already offset
   before the rail hydrates. `ShellBootSkeleton` mirrors the same geometry
   (18px inset, 68px rail, 54px bar) so the boot state does not jump.

The header shows a **URL-derived breadcrumb**, not a page title — every page
renders its own hero. Record ids never reach the trail.

---

## Elevation and radius

One canonical two-layer chrome shadow, four surfaces (rail, bar, hero card,
boot skeleton):

```
0 1px 2px      rgba(15,23,42,0.04)      ← tight contact
0 12px 40px -16px rgba(15,23,42,0.18)   ← wide soft ambient
```

Import it as `CHROME_SHADOW`; hover deepens the *same neutral* via
`CHROME_SHADOW_HOVER`. **Coloured shadows are reserved for the primary CTA**
(`shadow-indigo-500/25`) — an indigo bloom behind a card is exactly the effect
the style guide rejects.

| Element | Radius |
| --- | --- |
| Pills, badges, avatars, chrome controls | `rounded-full` |
| Inputs, nav rows, small buttons | `rounded-xl` |
| Cards, tables, modals | `rounded-2xl` |
| Page heroes | `rounded-3xl` |
| Floating chrome (rail) | `rounded-[26px]` |
| Floating bar | `rounded-[20px]` |

---

## Components

| Need | Use |
| --- | --- |
| Page top | `<PageHeader>` + `<PageShell>` from `components/layout/PageHeader` |
| Primary CTA in a hero | the `HERO_PRIMARY_BTN` class string |
| KPI tile | `<StatCard>` |
| Card | `<Card>` / `<CardHeader>` / `<CardBody>` |
| Status pill | `<Badge tone={toneForStatus(status)}>` |
| Any input | `<Field>` + `<Input>` / `<Select>` / `<Textarea>` / `<SearchInput>` |
| Filter rail | `<Segmented>` |
| Dialog | `<Modal>` — portals to `<body>`, `bg-black/55`, no blur, `z-50` |
| Result feedback | `useToast()` |
| Empty list | `<EmptyState>` — dashed border, `text-slate-300` glyph |
| Table | hand-rolled `<table>` with `TABLE_HEAD` / `TABLE_ROW` |

Tables drop columns by breakpoint (`hidden sm:table-cell`) rather than scrolling
sideways on mobile.

---

## Motion

| Role | Curve | Duration |
| --- | --- | --- |
| Shell / chrome | `cubic-bezier(0.32, 0.72, 0, 1)` | 260ms (`CHROME_CURVE`) |
| Entrances | `cubic-bezier(0.22, 1, 0.36, 1)` | 180–280ms |
| Overlays | `cubic-bezier(0.16, 1, 0.3, 1)` | 140–300ms |
| Exits | `cubic-bezier(0.4, 0, 1, 1)` | 140–170ms |

* **No hover lift, no scale, no glow.** Rows and cards recolour, or deepen the
  same neutral shadow. `hover:scale-[1.02]` belongs to the primary CTA alone.
* **Never `transition-all` on a moving container** — name the properties. The
  rail animates `[width,transform,box-shadow]` and nothing else.
* The sidebar spotlight writes pointer position to **CSS custom properties on
  the row**, never React state — a `setState` per mousemove would re-render the
  whole nav ~60×/s.
* Everything decorative carries `pointer-events-none` and `aria-hidden`.

---

## Don't

- `window.confirm` / `alert` / `prompt`.
- `gray-*`, `blue-*`, `red-*`, `green-*` in new code.
- Interpolated Tailwind class names.
- Hover lift, hover scale, or a coloured glow on cards and rows.
- Coloured left-border accent stripes on rows.
- A `z-index` on `AppHeader` — it is a flex sibling above `<main>`, which is
  enough, and a `z-50` header would tie with the rail.
- New rules in `globals.css` — reach for `lib/tokens.ts` instead.
