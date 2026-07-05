# Karaji — Project Context

> Quick reference for AI assistants and developers

> **📍 Doc sync:** CLAUDE.md last synced to commit `8ef81a9` — 2026-07-05 16:33 (Sun). Convention: whenever you edit this file, refresh this line to the current commit — run `git log -1 --format="%h %cd" --date=format:"%Y-%m-%d %H:%M (%a)"` and paste the hash + date + time.

> **📌 Log the tricky stuff.** Whenever you hit an **issue, blocker, non-obvious behavior, or anything that cost real debugging time**, write it down with its **symptom → root cause → fix** — inline near the relevant section and/or a one-liner in the `> Last updated:` log. Same convention as Retab Stores / Sky Amman; the stack is shared, so a gotcha captured once saves every sibling project.

---

## Project Overview

**Working title:** Karaji (كراجي) — ⚠️ **TEMPORARY name, rename pending.** Rule from day one: the brand name lives ONLY in `APP_NAME` (and later one i18n key). **Never hardcode the name in UI strings, components, or copy** — the future rename must be a one-line change plus a folder/repo rename.
**Product:** SaaS maintenance CRM for local car maintenance shops in **Amman, Jordan**. Records customers, cars, and service visits; its real value is a **reminders engine** that brings customers back to the shop (oil changes, license renewal, seasonal checks) via calls and WhatsApp.
**Portals:**
1. **Shop portal** — the garage owner/worker. **Arabic-only, RTL, mobile/tablet-first, radically simple** (users are NOT technical).
2. **Admin portal** — us (SaaS operator). English, LTR. Manages shops, subscriptions, support (impersonation), announcements; can view/edit everything with a full audit trail.

**Stack:** Laravel 12 + Inertia.js **v3** + React 19 + TypeScript + Tailwind CSS v4 (official `laravel/react-starter-kit` **v1.0.1** — PHP 8.2 pin, same as Retab Stores). Scaffold shipped Inertia v2.0.24; **upgraded to v3 on 2026-07-05** (`inertia-laravel` v3.1.1 + `@inertiajs/react` 3.6.0). The only break, same as Retab: v3 no longer auto-unwraps the page module's default export — `app.tsx` unwraps with `.then((m) => m.default)`, `ssr.jsx` returns `pages[...].default`.
**DB:** SQLite for the scaffold/dev right now (`database/database.sqlite`). Real dev/prod DB decision pending (likely MariaDB/MySQL like the sibling projects) — decide before schema work.
**Hosting:** TBD — likely Railway (FrankenPHP) behind Cloudflare, mirroring Sky Amman/Retab.
**Repo:** `origin` → `https://github.com/mercenary19961/karaji.git` (private). Single remote for now.
**Business model:** monthly subscription per shop in JOD (price TBD, ballpark 15–25 JOD/month, free first month). Admin portal controls activation/suspension.

---

## Reference Projects — read the implementation before building

Same machine, same stack, proven patterns. **When in doubt, read the corresponding file there first:**

- **`c:\Users\sabba\Desktop\projects\retab-stores\`** — closest and most recent. Port from here: **WhatsApp Meta Cloud API** integration (templates, webhooks, campaign sender), i18n/RTL foundation (`SetLocale`, `LanguageContext`, logical properties, Tajawal font token override), security hardening (SecurityHeaders, trustProxies, Turnstile, rate limits), media layer (`App\Support\Media`), SSR wiring (`TimeoutHttpGateway`), CI workflow (`.github/workflows/ci.yml` incl. the ziggy-js devDep gotcha), `withoutVite()` in TestCase.
- **`c:\Users\sabba\Desktop\projects\hardrock-ecom-demo\`** — activity log + undo (`ActivityLogService`), roles, notifications, optimistic locking.
- **`c:\Users\sabba\Desktop\projects\sky-amman\`** — locale middleware origin, admin layout, Site Content CRUD, security headers, SSR sidecar + Railway deploy notes.

**Don't blindly copy:**
- Retab's storefront is bilingual with an instant AR⇄EN toggle. Karaji's **shop portal is Arabic-ONLY in v1** (no toggle, no `_ar`/`_en` content columns for UI chrome) — the bilingual machinery is deliberately NOT needed here at first. Admin portal is EN.
- This starter kit uses lowercase `resources/js/pages/`, modular `routes/*.php`, shadcn/Radix `components/ui/` — same as Retab, different from Sky Amman.

---

## Product Spec (v1)

**Core insight: the reminders engine IS the product.** Record-keeping is the cost the shop pays; customers coming back is what they buy. If entering a visit takes over ~20 seconds, shops revert to the paper notebook and the product dies.

### Shop portal
- **Car-centric model:** a customer (phone) owns one or more **cars (plate number)**. One big search box: lookup by plate OR phone. The counter moment = type last digits of the plate → full history + what's due.
- **Visit entry, brutally minimal:** required = plate, current **km**, services done as **tappable chips** (تغيير زيت، فلتر زيت، فلتر هواء، فلتر مكيف، فحص فرامل، بطارية، دواليب…). Oil brand dropdown remembered from last visit; price and notes optional. New customer = phone + name, done.
- **Reminders by km AND date:** interval per oil type (mineral ~3,000–5,000 km, synthetic ~10,000 km, or 3–6 months). Estimate due-date from the car's average daily km across visits (shop-level default, e.g. 40 km/day, for first-visit cars).
- **Daily "call list":** cars due/overdue, sorted by overdue-ness, each with big **call** + **WhatsApp** buttons and a "تم التواصل ✓" state.
- **WhatsApp in two phases:** **Phase 1 = `wa.me` deep links** with pre-filled Arabic text (zero cost, no Meta approval, shop taps send). **Phase 2 (later, paid tier) = Meta Cloud API** automation with approved utility templates — reuse Retab's implementation.
- **Digital windshield sticker:** after each visit, one tap sends the customer a WhatsApp summary (what was done, km, next due km/date). Flagship demo feature.
- **Jordan-specific hooks:** annual **license renewal (الترخيص/الفحص)** month per car → reminder + pre-inspection offer; **seasonal broadcasts** (winter check ~Nov, AC ~May); Eid/Ramadan greeting broadcasts.
- **Dashboard:** today's visits, due-for-contact count, monthly revenue (if prices entered), "customers you're losing" (no visit in 8+ months). Analytics = 3–4 numbers + simple charts, no more.

### Admin portal
- Shops CRUD + **subscription status** (Active / Trial / Suspended), onboarding.
- **"Login as shop" impersonation** — support IS the product for non-technical users.
- Per-shop usage analytics (visits entered, reminders sent) → churn early-warning.
- Broadcast announcements to shop dashboards; notification system.
- Admin edits fully audited + undoable (port hardrock `ActivityLogService`).

### Deferred (v2+) — do NOT build in v1
Inventory/stock, multi-branch, employee roles beyond one shared shop login, appointments/booking, customer-facing portal, invoicing/tax, automated Cloud API sending (phase 2).

---

## UX Rules (MUST FOLLOW — shop portal)

The user is a garage owner, ~35–55, not technical, on a phone or cheap Android tablet, greasy hands, noisy shop. Every screen optimizes speed + obviousness over density.

1. **Arabic-only UI**, `dir="rtl"` on the root, CSS **logical properties** only (`ms-*`/`me-*`/`ps-*`/`pe-*`, `text-start`) — never `left`/`right` utilities.
2. Mobile-first (~390px). Touch targets **≥48px**. Base font **≥16px**.
3. **Max 2 taps** from dashboard to any core action.
4. Icons **always paired with an Arabic label** — never icon-only.
5. Lists are **cards, not tables**. No horizontal scrolling, ever.
6. **No required field that can possibly be optional.** Visit entry ≤3 required fields.
7. **Undo instead of "are you sure?"** dialogs wherever feasible.
8. Numeric inputs (phone/plate/km/price) get `inputmode="numeric"` keypads.
9. **Never hardcode the brand name** (see Project Overview).

Admin portal: EN, LTR, denser UI allowed — but same component base (shadcn/Radix).

---

## Architecture Patterns (adopt from Retab/Sky Amman)

- **Inertia:** controllers `Inertia::render('page/name', [...props])`; mutations via `router.post/put/delete` with `preserveScroll`/`onSuccess`; files via `FormData` + `forceFormData`; flash via `->with('success', …)` → `usePage().props.flash`; auth via `usePage().props.auth.user`. No client-side router, no API routes for the UI.
- **Code quality:** dedicated model methods over raw `::update()`/`::create()`; one shared method per write path; **never query in a loop** (batch with `whereIn`/`pluck`); guard no-op writes; use returned values; match surrounding style.
- **Security (port at hardening time, pre-launch):** rate-limit all public POSTs; `SecurityHeaders` middleware (CSP skipped in local dev); `trustProxies` locked to Cloudflare CIDRs (never `*`); `URL::forceScheme('https')` in production; Turnstile on any public form; validate uploads by extension AND MIME. Use `Auth::id()`/`Auth::user()`, never the `auth()` helper.
- **Multi-tenancy:** single database, `shop_id` scoping (global scope or explicit) on all shop-owned tables — decide the exact mechanism at schema time and document it here.

---

## Local Development

- **Start:** `composer run dev` (serve + queue + vite) or `php artisan serve` + `npm run dev`. URL: `http://localhost:8000`.
- **DB:** SQLite at `database/database.sqlite` (scaffold default). Reset: `php artisan migrate:fresh`.
- **Tests:** `php artisan test` (PHPUnit; scaffold baseline = 26 passed / 63 assertions). **Build:** `npm run build`.
- **`.npmrc` holds `production=false` — do not delete.** The shell has a global `NODE_ENV=production` that otherwise makes `npm install` silently drop devDependencies. Fallback: `npm install --include=dev`.
- **Intelephense:** the machine-wide P1005/P1013 fix is already applied (porifa fork removed + `vendor/_laravel_ide` excluded — see retab-stores CLAUDE.md → Local Development for the forensic writeup). Per-project step once models exist: `composer require --dev barryvdh/laravel-ide-helper` + generate + gitignore the `_ide_helper*` files.

---

## Git & Commit Convention

- `origin` → `https://github.com/mercenary19961/karaji.git`. Commit/push **only when the user asks**. Branch off `main` for feature work.
- Format: `type(scope): short description` — types `init` · `feat` · `fix` · `refactor` · `style` · `doc` · `chore`; lowercase, imperative, no trailing period, ≲72 chars, specific.
- **No attribution trailer** — no `Co-Authored-By:` line, no "Generated with" line.
- After any task that touches code, end the reply with a **one-line suggested commit message** (don't run the commit).

---

## Build Progress

### Scaffold (DONE — 2026-07-05)
- [x] `laravel new karaji --react --phpunit --database=sqlite --no-interaction` — starter kit **v1.0.1** (PHP 8.2 pin), Laravel 12.62, Inertia **v2.0.24**, React 19, TS, Tailwind v4, Ziggy 2.6.3
- [x] `.npmrc` (`production=false`) added BEFORE `npm install`; deps installed with devDeps intact
- [x] Verified: `npm run build` ✓ (3.3s) + `php artisan test` ✓ (26 passed / 63 assertions)
- [x] Git init on `main`, `origin` → `mercenary19961/karaji` (private)
- [x] `APP_NAME=Karaji` in `.env` + `.env.example` (the ONLY place the working name lives)
- [x] This `CLAUDE.md` seeded (brief, UX rules, references, conventions)

### Foundation (TODO — rough order)
- [x] Init commit + push to `origin` (`d35024e`, 2026-07-05)
- [x] **Inertia v2 → v3 upgrade** (2026-07-05) — `inertia-laravel` v3.1.1 + `@inertiajs/react` 3.6.0; the default-export unwrap in `app.tsx`/`ssr.jsx` was the only change needed, exactly as Retab predicted. Verified: `npm run build:ssr` ✓ (client + SSR bundles) + `php artisan test` ✓ (26 passed / 63 assertions, scaffold baseline held)
- [x] **Arabic/RTL foundation** (2026-07-05) — `APP_LOCALE=ar` + `APP_FAKER_LOCALE=ar_JO` (Faker ships a Jordanian provider — Arabic names/addresses in seeds) in `.env`/`.env.example`, fallback stays `en`; `app.blade.php` gets Retab's dynamic `dir="{{ locale === 'ar' ? 'rtl' : 'ltr' }}"` + Tajawal in the Bunny fonts link; `app.css` gets the `html[dir='rtl']` `--font-sans` token override (Tajawal leads the stack under RTL only). **No toggle, no middleware** — locale is app-wide `ar`; when the admin portal exists, its route middleware sets `en` per-request and `lang`/`dir`/font all flip automatically. Verified: build + 26 tests green, served `/login` renders `<html lang="ar" dir="rtl">` with the Tajawal link
- [x] **HTML mockup** of the 7 core screens (2026-07-05) — designed in Claude Design, imported to `design/`. Two artifacts: `design/claude-design/mockup.dc.html` (+`support.js`) = the editable Claude Design source (round-trip future edits there), and **`design/mockup-v1.html`** = the flattened, fully self-contained static version (plain HTML + vanilla JS, zero external requests) — THE file to open/share. Shareable link (private, works on phone): https://claude.ai/code/artifact/cb5abd36-617a-46a6-8726-b91f001cadea . **Gotcha:** Claude Design exports are NOT self-contained — `support.js` loads React 18 + Babel from unpkg.com at runtime, so the `.dc.html` needs internet and dies under any strict CSP (Claude artifacts, future SecurityHeaders); flatten to static HTML before shipping a mockup anywhere. Design covers all spec beats: new-customer mini-form, post-save WhatsApp summary (windshield sticker), license-renewal + seasonal reminder cards, undo toast, wide LTR admin frame, light-only theme.
- [ ] **Validate mockup with a real shop owner** → collect reactions per screen (esp. visit-entry speed + WhatsApp summary) before any schema work — can now demo the REAL app (`/shop`) instead of the static mockup
- [x] **Shop portal demo screens** (2026-07-05) — mockup → real Inertia pages. Theme: mockup palette mapped onto the shadcn `:root` tokens + four new semantic pairs (`cta` amber action, `success`, `success-soft`, `due` badge) wired through `@theme`; `--radius` 1rem; `.dark` left stock (shop portal is light-only by design — garage sunlight). `APP_NAME="كراجي"` (brand rendered ONLY via the shared `name` prop in `shop-layout`). Backend: `routes/shop.php` behind `auth` (`/shop`, `/shop/visits/new`, `/shop/cars/demo`, `/shop/reminders`, `/shop/analytics`), `Shop\ShopScreensController` + `App\Support\ShopDemoData` — the demo arrays' shapes ARE the schema-v1 prop contract; swapping in Eloquent later touches only the controller. Frontend: `layouts/shop-layout.tsx` (header + 3-tab bottom nav, 48px+ targets), 5 pages under `pages/shop/`, types in `types/shop.ts`. **Live wa.me/tel: deep links** (phase-1 WhatsApp) with prefilled Arabic messages incl. the windshield-sticker visit summary built from actual form state. Client-side demo interactions only (no POSTs yet): chips, save→summary+undo toast, تم التواصل toggles, month picker driving the SVG chart. Tests 28 passed / 110 assertions (new `Shop\ShopPortalTest` renders all 5 screens), tsc + build clean. NOT built yet: any write path.
- [x] **Admin portal demo screens** (2026-07-05) — mockup screens 6-7 as real pages: `routes/admin.php` behind `auth` + `SetAdminLocale` (**TODO schema v1: admin-role gate — plain `auth` is demo-only**), `Admin\AdminScreensController` + `AdminDemoData`, `layouts/admin-layout.tsx` (EN/LTR, wide, denser), `admin/shops` (client-side search filter, status badges) + `admin/shop-detail` (stats, subscription card, "Login as shop →" linking to `/shop`, activity log with client-side Undo demo). **Gotcha (caught by test, would've bitten in prod):** `App::setLocale()` mutates process state — in worker-mode servers (FrankenPHP/Octane) and across requests within one test, a locale set for one request **leaks into the next**. Fix: `SetLocale` middleware appended to the `web` group pins `ar` on every request (hardcoded `'ar'`, NOT `config('app.locale')` — setLocale rewrites that key, so re-reading restores the leaked value); admin overrides to `en` afterwards via route middleware. `AdminPortalTest` asserts both portals' `lang`/`dir` in the same process. Tests 31 passed / 135 assertions.
- [ ] **Decisions:** real name/brand · dev/prod DB engine · hosting · subscription price · theme (single light theme recommended for the shop portal — garage tablets in sunlight, skip dark mode there)
- [ ] **Schema v1:** shops, users (roles: admin / shop), customers, cars, service_types, visits + visit_services, reminders, subscriptions, announcements, activity_logs — multi-tenant `shop_id` scoping decision documented here
- [ ] CI (port Retab's workflow incl. `withoutVite()` in TestCase + the ziggy-js devDep pin)

> **Last updated:** 2026-07-05 — **Admin portal demo screens built** (mockup 6-7 → real pages; locale-leak gotcha found by test and fixed with a per-request `SetLocale` web middleware — see Build Progress; 31 tests green). Earlier: **Shop portal demo screens built** (mockup → real Inertia pages, demo props as schema contract, live wa.me links, 28 tests green; see Build Progress). Earlier: **Mockup v1 imported + flattened.** 7-screen mockup built in Claude Design, imported into `design/` (dc source + self-contained `mockup-v1.html`), published as a private artifact for phone viewing. Gotcha logged: Claude Design exports depend on unpkg CDN at runtime — flatten before sharing/CSP. Also fixed the scaffold's latent type error (starter kit v1.0.1 `welcome.tsx` ships `mixBlendMode: 'plus-darker'`, absent from csstype's union — surfaces the first time anyone runs `tsc`, since Vite never type-checks; fixed with a type-only cast). Second starter-kit tsconfig nit: `"baseUrl": "."` triggers VS Code's "deprecated, removed in TS 7.0" warning — safe to delete outright, `paths` resolves relative to tsconfig since TS 4.1 (Retab has the same line; same fix applies). Next: shop-owner validation, then decisions (name/DB/hosting/price) + schema v1. Earlier: **Arabic/RTL foundation.** App-wide `ar` locale (env only, no middleware/toggle — deliberately simpler than Retab), dynamic `lang`/`dir` in `app.blade.php`, Tajawal via Bunny fonts + the `html[dir='rtl']` `--font-sans` token override ported from Retab, faker locale `ar_JO` for future Arabic seeds. Verified end-to-end: served `/login` = `<html lang="ar" dir="rtl">`, build + 26 tests green. The starter kit's EN pages now render RTL — expected, they become the Arabic shop portal screens. Earlier same day: **Inertia v3 upgrade** (`inertia-laravel` v3.1.1 + `@inertiajs/react` 3.6.0, only break was the default-export unwrap in `app.tsx`/`ssr.jsx` — exactly as Retab predicted, zero gotchas) and **project scaffolded** (starter kit v1.0.1 on PHP 8.2, init commit `d35024e`). Next: the 7-screen HTML mockup before any schema work.
