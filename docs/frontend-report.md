# Frontend transition report — Nuxt SPA cutover

**Date:** 2026-07-24  
**Status:** Initial cutover implemented  
**Canonical ADR:** [`docs/adr/018-nuxt-spa-frontend.md`](adr/018-nuxt-spa-frontend.md)  
**Not Phase 5:** Spec / migration-plan Phase 5 remains the separate Blade incremental / Yajra checklist. This work is the **Nuxt SPA frontend** track.

---

## 1. Executive summary

Air Concierge’s browser UI moved from Laravel Blade to a **Nuxt 3 SPA** under [`frontend/`](../frontend/). Laravel remains the sole backend for auth, authorization, validation, and domain logic. The SPA displays data and submits forms via existing **`/admin/*`** JSON endpoints (not moved to `/api`). Visual language follows the free Sneat theme in `nuxtjs-theme/` as a **reference only** (no runtime imports).

**PDF generation** still uses DomPDF + Blade under `resources/views/pdf/` — intentionally not ported to Nuxt.

---

## 2. Goals and locked decisions

| Decision | Choice |
|----------|--------|
| App location | `frontend/` (Nuxt Node app), not under `resources/` |
| Theme | `nuxtjs-theme/` = UI/UX style reference + one-time copy source; gitignored; never product code |
| Visual target | Sneat / Vuetify restyle (not Blade HTML/CSS parity) |
| Routing | History-mode SPA; URL parity with former Blade paths |
| Links | `BaseLink` defaults to **same tab**; `newTab` only when explicit |
| API location | Keep `/admin/*` on `web.php` — **no** `/api` migration for SPA |
| Auth | Session cookies + Laravel Sanctum CSRF |
| Data layer | `services/` + Pinia `stores/`; no business calculations in the frontend |
| Styling | Vuetify (Sneat) + Tailwind (`preflight: false`) |
| PDF | DomPDF Blade retained |

---

## 3. Before → after

### Before

- ~17 standalone Blade UI documents under `resources/views/` (no shared Blade layouts / `@extends`)
- Session login via Blade form + CSRF
- Many `/admin/*` handlers dual HTML / `wantsJson()`
- `nuxtjs-theme/` present as unused ThemeForest Sneat demo (gitignored)
- No Sanctum; almost empty `routes/api.php` (`/api/health` only)

### After

- Nuxt 3.14 SPA in `frontend/` owns all former UI pages
- Laravel admin controllers return **JSON only** for cut-over surfaces
- Login UI on Nuxt; `POST /login` / `POST /logout` / `GET /sanctum/csrf-cookie` on Laravel
- UI Blade files removed; only PDF templates remain in `resources/views/`
- Sanctum, CORS config, `FRONTEND_URL`, stateful domains documented

---

## 4. Repository layout

```
frontend/                 # Tracked Nuxt app (Air Concierge UI)
  pages/                  # File-based routes (URL parity)
  layouts/                # Sneat default (admin) + blank (auth)
  components/ui/          # Base* primitives
  components/chronologies/# ChronologyForm (page-adjacent)
  services/               # HTTP wrappers only
  stores/                 # Pinia display state
  middleware/             # auth, guest
  docs/ui-ux.md           # Standing style rule
  README.md               # Dev setup
nuxtjs-theme/             # Gitignored free theme — reference only
resources/views/pdf/      # DomPDF only
app/ + routes/web.php     # Laravel backend + /admin JSON
docs/adr/018-*.md         # Architecture decision
```

**Hard rule:** Nothing under `frontend/` may import from `nuxtjs-theme/` (no aliases, no `file:` deps). Bootstrap copied Sneat shell/styles/plugins **into** `frontend/` once.

---

## 5. Converted views (Blade → Nuxt)

| Former Blade | Nuxt route | Notes |
|--------------|------------|--------|
| `welcome` | `/` | Landing; auth-aware CTA |
| `auth/login` | `/login` | Real Laravel auth via services |
| `admin/dashboard` | `/admin/dashboard` | Stats from `/admin/dashboard/stats` (+ chart available) |
| `admin/bookings/index` | `/admin/bookings` | |
| `admin/bookings/show` | `/admin/bookings/:id` | |
| `admin/payments/index` | `/admin/payments` | |
| `admin/property-payments/index` | `/admin/property-payments` | |
| `admin/chronologies/index` | `/admin/chronologies` | |
| `admin/chronologies/create` | `/admin/chronologies/create` | |
| `admin/chronologies/show` | `/admin/chronologies/:id` | |
| `admin/chronologies/edit` | `/admin/chronologies/:id/edit` | |
| `admin/send-emails/create` | `/admin/send-emails/create` | Owners/templates/documents from JSON |
| `admin/reports/index` | `/admin/reports` | |
| `admin/zoho/index` | `/admin/zoho` | Token status JSON |
| `admin/failed-jobs/index` | `/admin/failed-jobs` | Retry / forget / retry-all |
| `admin/terms` | `/admin/terms` | Placeholder |
| `admin/owner-statements` | `/admin/owner-statements` | Placeholder |

### Kept as Blade (not Nuxt)

| View | Reason |
|------|--------|
| `resources/views/pdf/payment-receipt.blade.php` | DomPDF receipt generation (jobs/services) |
| `resources/views/pdf/report.blade.php` | DomPDF report generation |

---

## 6. Architecture

```text
Browser (Nuxt :3000)
  → services (ofetch, credentials, X-XSRF-TOKEN)
  → GET /sanctum/csrf-cookie
  → POST /login | POST /logout | GET/POST/PUT/DELETE /admin/*
  → Laravel (Sail :8080)
       → Policies / Form Requests / Services / Models
       → DomPDF jobs → pdf/*.blade.php
```

### Auth flow

1. `GET /sanctum/csrf-cookie` (sets `XSRF-TOKEN`)
2. `POST /login` with email/password/remember (`Accept: application/json`)
3. Session cookie for subsequent `/admin/*` calls
4. Profile via `GET /admin/dashboard/profile`
5. `POST /logout` clears session

Nuxt middleware: `auth` (fetch user or redirect `/login`), `guest` (redirect authenticated users to dashboard).

### Dev vs production networking

| Environment | Approach |
|-------------|----------|
| **Dev** | Nuxt proxies `/sanctum`, `/login`, `/logout`, `/admin` → `NUXT_LARAVEL_URL` (default `http://localhost:8080`). Prefer first-party cookies via proxy. `SANCTUM_STATEFUL_DOMAINS` includes `localhost:3000`, `localhost:8080`, etc. |
| **Prod** | Same-origin recommended (reverse proxy or SPA assets + Laravel API paths). Stateful domains = production host only. Avoid cross-site cookies. |

Env keys: `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, `APP_URL`, frontend `NUXT_LARAVEL_URL`.

---

## 7. Frontend data layer

### Services (`frontend/services/`)

Thin HTTP only — no calculations:

- `http.js` — shared ofetch client (`credentials: 'include'`, CSRF header)
- `authService`, `dashboardService`, `bookingService`, `paymentService`, `chronologyService`, `reportService`, `failedJobService`, `zohoService`, `emailService`

### Stores (`frontend/stores/`)

Pinia: hold list/detail/loading/errors; call services. Domain math stays in Laravel.

### Components

**Primitives** (`frontend/components/ui/`):

- `BaseButton`, `BaseInput`, `BaseTextarea`, `BaseSelect`, `BaseCheckbox`, `BaseRadio`, `BaseLink`

Conventions: `modelValue` / `v-model`, `label`, `error`, `hint`, `size`, `variant`, attribute/class fallthrough. Wrap Vuetify for Sneat look.

**Other:**

- `components/chronologies/ChronologyForm.vue` — shared create/edit form fields
- Theme leftovers such as `UpgradeToPro.vue` may still exist from the Sneat copy; prefer not using them in product pages

**Componentize-on-second-use:** first occurrence inline; extract on second use into categorized folders (`ui`, `forms`, `layout`, `navigation`, `feedback`, `data-display`, `charts`, `features`) as needed.

---

## 8. Backend changes (minimal, flagged)

Allowed for this cutover:

| Change | Detail |
|--------|--------|
| `laravel/sanctum` ^4.3 | SPA CSRF cookie support |
| `config/sanctum.php`, `config/cors.php` | Stateful domains + credentialed CORS |
| `config/app.php` `frontend_url` | Redirects from legacy Blade entrypoints |
| `bootstrap/app.php` | `statefulApi()`; JSON when `expectsJson()` |
| Auth controller | JSON login/logout; GET login redirects to SPA |
| Admin controllers | JSON-only for former Blade surfaces (dashboard, bookings, payments, chronologies, emails, reports, zoho, failed-jobs, terms, owner-statements) |
| `routes/web.php` | `GET /` redirects to SPA home |
| `.env.example` | `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, Sail-oriented `APP_URL` |

**Not done (by design):**

- Moving SPA routes to `/api` or `/api/v1`
- Changing domain models / business service logic (beyond JSON response shaping)
- Client-side PDF generation
- Porting legacy ~198 L5.1 templates
- Editing `nuxtjs-theme/` for product features

---

## 9. Standing UI/UX style rule

Documented in:

- [`frontend/docs/ui-ux.md`](../frontend/docs/ui-ux.md)
- ADR-018
- Migration plan “Nuxt SPA frontend” section

Rules:

1. Match the equivalent page/pattern in `nuxtjs-theme/` when one exists.
2. If none exists, still use the same tokens, spacing, conventions, and visual language.
3. No page/component may visually diverge from that Sneat style going forward.

Review every new or converted page against this rule.

---

## 10. Local developer setup

```bash
# Laravel (Sail) — typically http://localhost:8080
./vendor/bin/sail up -d

# Nuxt SPA — http://localhost:3000
cd frontend
cp .env.example .env    # NUXT_LARAVEL_URL=http://localhost:8080
npm install             # or pnpm install
npm run dev
```

Open `http://localhost:3000/login`. Ensure `.env` has `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, and `APP_URL` aligned with Sail (e.g. `http://localhost:8080`).

More detail: [`frontend/README.md`](../frontend/README.md).

---

## 11. Testing / verification done

- Pest: Authentication, Phase 4 failed-jobs JSON, OwnerAccess middleware suites adjusted and passing after cutover
- Sanctum route registered: `GET sanctum/csrf-cookie`
- `nuxt prepare` succeeds; Tailwind + Pinia modules present
- Grep: no runtime `nuxtjs-theme` imports under `frontend/` (docs-only mentions OK)

**Known non-blockers:**

- Full `vue-tsc` typecheck not wired (JS-first app; install `vue-tsc` only if desired)
- One early `composer require` / `npm install` shell run exited uncleanly during long Docker/npm sessions; packages ended up installed and usable afterward — re-run `npm install` in `frontend/` if `node_modules` looks incomplete on another machine

---

## 12. Documentation updated

| Doc | Update |
|-----|--------|
| `docs/adr/018-nuxt-spa-frontend.md` | Accepted ADR |
| `docs/migration-plan.md` | Nuxt SPA section; Phase 5 clarified as separate |
| `docs/technical-documentation.md` | Stack + local Nuxt setup |
| `frontend/README.md` | App setup / architecture |
| `frontend/docs/ui-ux.md` | Standing style rule |
| `docs/frontend-report.md` | This report |

---

## 13. Follow-ups (recommended)

1. **Prod hosting:** Same-origin reverse proxy or `nuxt generate` / build into Laravel `public` + history fallback; lock `SANCTUM_STATEFUL_DOMAINS` to the real host.
2. **Visual polish:** Pass every page against Sneat demos (tables, forms, dashboard cards) for spacing/typography fidelity.
3. **Second-use extractions:** Page header, empty state, data table shell, confirm dialogs as patterns repeat.
4. **Remove theme cruft:** Drop unused Sneat demo assets (`UpgradeToPro`, unused views) when safe.
5. **CI:** Optional job to `npm ci` + lint in `frontend/`.
6. **Phase 5:** Unrelated Blade/Yajra checklist remains open if still needed for any residual Blade or DataTables work.
7. **Owner UX:** Terms / owner-statements are placeholders — wire real domain flows when product-ready.
8. **Properties / documents / imports:** Already JSON-only on Laravel with no Blade pages; add Nuxt admin screens when required.

---

## 14. Inventory checklist (acceptance)

- [x] 17 UI Blade views have Nuxt equivalents
- [x] PDF Blade retained
- [x] URL parity for converted routes
- [x] `/admin/*` kept (no `/api` move)
- [x] Sanctum CSRF + session auth path
- [x] Services + stores; logic in Laravel
- [x] Base* primitives + BaseLink (same-tab default)
- [x] Standing UI/UX rule documented
- [x] Zero runtime imports from `nuxtjs-theme/`
- [x] Migration plan / ADR / technical docs updated

---

*Report generated for the 2026-07-24 Nuxt SPA frontend transition.*
