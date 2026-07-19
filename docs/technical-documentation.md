# Air Concierge — Technical Documentation (Phase 0–1)

**Audience:** Developers, ops, and agents working on `airconcierge13`  
**Status:** Phase 0 (foundation) and Phase 1 (routing & auth scaffold) complete  
**Last updated:** 2026-07-16

This document describes the **implemented** technical surface through Phase 1. Sequencing, DoD, and future phases live in [`migration-plan.md`](migration-plan.md). Architecture decisions live under [`adr/`](adr/).

---

## 1. Purpose and status

`airconcierge13` is a **fresh Laravel 13 rewrite** of the Laravel 5.1 Air Concierge application. It is a **separate deploy** from the legacy XAMPP app at `/mnt/e/xampp/htdocs/airconcierge`.

| Phase | Delivered |
|-------|-----------|
| 0 | Sail runtime, MySQL + Redis, Pest/Pint/Larastan, CI, ADRs, inventory, project rules |
| 1 | Greenfield routes, Spatie Permission, session login shell, owner middleware stubs, Schedule command stubs, Hostaway webhook stub |

There is **no production cutover**. Until cutover is approved, the legacy app remains the system of record.

---

## 2. Stack

| Concern | Choice |
|---------|--------|
| PHP | 8.3+ (Sail image commonly 8.4) |
| Framework | Laravel 13 |
| Local runtime | Laravel Sail (`./vendor/bin/sail`) |
| Database | MySQL 8, database name `airconcierge` |
| Cache / queue / session | Redis |
| AuthZ | Spatie Laravel Permission + Policies/Gates |
| Tests | Pest |
| Style | Laravel Pint (`pint.json` excludes `database/migrations_fresh`) |
| Static analysis | Larastan level 5 |
| Horizon | Not installed — plain `queue:work` |
| Agent tooling | Laravel Boost (dev) |

Default env drivers (see `.env.example`): `DB_CONNECTION=mysql`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`.

---

## 3. Local runtime

Docker Desktop must be healthy in WSL before relying on Sail.

```bash
# From the project root
cp .env.example .env   # if needed
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

Sail Compose runs the app plus MySQL, Redis, a **queue** worker (`queue:work` on Redis), and a **scheduler** (`schedule:work`).

Quality checks (also run in CI):

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/pest
```

Do not copy production secrets or the legacy `.env` into this repo.

---

## 4. Project layout (relevant bits)

```
airconcierge13/
├── app/
│   ├── Http/Controllers/     # thin HTTP (Auth, Admin shell, Webhooks)
│   ├── Http/Middleware/      # owner.terms, owner.active
│   ├── Http/Requests/        # Form Requests
│   ├── Contracts/            # owner terms / active-access interfaces
│   ├── Services/             # stub checkers (Phase 1); domain services later
│   ├── Jobs/                 # e.g. SyncHostawayReservationJob stub
│   ├── Console/Commands/     # Schedule stubs (no public /cron HTTP)
│   └── Models/User.php       # HasRoles (Spatie)
├── bootstrap/app.php         # routing + middleware aliases + CSRF except
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php           # Schedule registrations
├── database/
│   ├── migrations/           # live migrations (users + Spatie + framework)
│   └── migrations_fresh/     # REFERENCE ONLY — do not run wholesale
├── docs/                     # plan, ADRs, inventory, this file
└── compose.yaml              # Sail-managed
```

Copy specific files from `migrations_fresh/` into `database/migrations/` only when a later domain phase needs those tables.

---

## 5. Architecture rules

Layering for all application code:

```
Request → Controller → Form Request → Policy/Gate → Service → Model → Job / Event
```

- Controllers: HTTP in/out only.
- Preserve business behavior unless change is explicitly approved; document suspicious legacy behavior and wait.
- Legacy app is a **business-behavior oracle only** — no Entrust classes, route dumps, or technical patterns ported forward ([ADR-007](adr/007-phase1-greenfield-routing-auth.md)).
- No new global helpers; no `env()` outside config; no direct `DB::` in controllers.

See also [`AGENTS.md`](../AGENTS.md) and [`.cursor/rules/migration.mdc`](../.cursor/rules/migration.mdc).

---

## 6. Auth and authorization (Phase 1)

| Piece | Implementation |
|-------|----------------|
| Login | Session (`email` + `password`, optional remember) |
| Controllers | `App\Http\Controllers\Auth\AuthenticatedSessionController` |
| Validation | `App\Http\Requests\Auth\LoginRequest` (rate-limited) |
| Roles | Spatie; seeded by `Database\Seeders\RoleSeeder` |
| Business role names | `superadmin`, `admin`, `Regional Manager`, `Property Owner`, `cleaner`, `maintenance` |
| Middleware aliases | `role`, `permission`, `role_or_permission`, `owner.terms`, `owner.active` |

Owner terms and active-property access are **new** middleware wired on the `admin` group. Domain checks use injectable contracts with **stub implementations** that currently always allow; replace when owner/property models exist. Tests bind fakes to assert redirect wiring.

**Not in Phase 1:** username-or-email login, password-expiry flow, Entrust/`resources` ACL, real terms/property queries.

Decisions: [ADR-002](adr/002-auth-spatie-permission.md), [ADR-007](adr/007-phase1-greenfield-routing-auth.md).

---

## 7. Routes and HTTP surface (current)

| Method + URI | Name | Notes |
|--------------|------|--------|
| `GET /` | `home` | Welcome |
| `GET/POST /login` | `login` | Guest |
| `POST /logout` | `logout` | Auth |
| `GET /admin/dashboard` | `admin.dashboard` | Auth + owner middleware |
| `GET /admin/owner-statements` | `admin.owner-statements.index` | Placeholder |
| `GET /admin/terms` | `admin.terms.show` | Placeholder |
| `POST /wh/hostaway/booking/created` | `webhooks.hostaway.booking.created` | CSRF-exempt |
| `GET /api/health` | `api.health` | JSON health |

Domain routes are added as later phases land — do not dump the legacy `routes.php`.

---

## 8. Cron and scheduling

There are **no public `/cron/*` HTTP routes** in this app.

- Artisan command stubs live under `app/Console/Commands/`.
- Frequencies are registered in `routes/console.php`.
- Legacy URI → command name mapping: [`migration-inventory.md`](migration-inventory.md) (Cron section).

Command bodies are stubs until the matching domain phase implements them.

---

## 9. Hostaway webhook (Phase 1 stub)

- Path preserved: `POST /wh/hostaway/booking/created`
- Controller returns **200** quickly and dispatches `SyncHostawayReservationJob`
- Job `handle()` is empty pending Phase 2
- **TODO Phase 2:** validate Hostaway webhook signature before trusting the payload

Do not change Hostaway API contracts when Phase 2 lands.

---

## 10. Quality and CI

GitHub Actions (`.github/workflows/ci.yml`) runs:

1. `vendor/bin/pint --test`
2. `vendor/bin/phpstan analyse --memory-limit=1G`
3. `vendor/bin/pest`

Pest Feature coverage for Phase 1 includes authentication, Spatie role middleware, owner middleware wiring (via fakes), Hostaway webhook dispatch, and stub command registration.

---

## 11. Documentation map

| Document | Role |
|----------|------|
| [migration-plan.md](migration-plan.md) | Source of truth for phases, DoD, out-of-scope |
| [migration-inventory.md](migration-inventory.md) | Checklist / cron command map from legacy inventory |
| [technical-documentation.md](technical-documentation.md) | This file — developer reference (Phase 0–1) |
| [user-documentation.md](user-documentation.md) | End-user / QA guide for the current shell |
| [Laravel_5.1_to_13_Modernization_Spec.md](Laravel_5.1_to_13_Modernization_Spec.md) | Historical requirements; plan wins on conflict |
| [adr/001](adr/001-fresh-laravel-13-skeleton.md) … [007](adr/007-phase1-greenfield-routing-auth.md) | Architecture decisions |
| [AGENTS.md](../AGENTS.md) | Agent/developer guidelines |

---

## 12. Out of scope for Phase 0–1

- Full admin UI and Blade rewrite
- Business schema (`migrations_fresh` live migrate)
- Hostaway sync / signature verification
- Bookings, payments, reports, chronology, email
- Username login, password expiry, real owner terms/property data
- Horizon, production cutover
