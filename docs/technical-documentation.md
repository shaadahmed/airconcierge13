# Air Concierge — Technical Documentation (Phase 0–1a)

**Audience:** Developers, ops, and agents working on `airconcierge13`  
**Status:** Phase 0 (foundation), Phase 1 (routing & auth scaffold), and Phase 1a (webhook auth + package audit) complete  
**Last updated:** 2026-07-22

This document describes the **implemented** technical surface through Phase 1a (including the ADR-009 owner-access reshape). Requirements live in [`Laravel_5.1_to_13_Modernization_Spec.md`](Laravel_5.1_to_13_Modernization_Spec.md). Sequencing, DoD, and future phases live in [`migration-plan.md`](migration-plan.md). Architecture decisions live under [`adr/`](adr/).

---

## 1. Purpose and status

`airconcierge13` is a **fresh Laravel 13 rewrite** of the Laravel 5.1 Air Concierge application. It is a **separate deploy** from the legacy XAMPP app at `/mnt/e/xampp/htdocs/airconcierge`.

| Phase | Delivered |
|-------|-----------|
| 0 | Sail runtime, MySQL + Redis, Pest/Pint/Larastan, CI, ADRs, inventory, project rules |
| 1 | Greenfield routes, `UserRole` + Policies (ADR-010), session login shell, owner-scoped middleware + `User` predicates, Schedule command stubs, Hostaway webhook path |
| 1a | Hostaway webhook Basic Auth (ADR-008), Spec package ownership audit (§10 / inventory), ADR gap check |

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
| AuthZ | `UserRole` enum + Laravel Policies (ADR-010) |
| Tests | Pest |
| Style | Laravel Pint (`pint.json` excludes `database/migrations_fresh`) |
| Static analysis | Larastan level 5 |
| Horizon | Not installed — plain `queue:work` |
| Agent tooling | Laravel Boost (dev) |

Default env drivers (see `.env.example`): `DB_CONNECTION=mysql`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`. Hostaway webhook Basic Auth: `HOSTAWAY_WEBHOOK_USERNAME`, `HOSTAWAY_WEBHOOK_PASSWORD` (required for accepted webhook traffic; fail closed when empty).

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
│   ├── Http/Middleware/      # owner.terms, owner.active (owner route group only)
│   ├── Http/Requests/        # Form Requests
│   ├── Policies/             # UserPolicy (UserRole match)
│   ├── Enums/                # UserRole
│   ├── Services/             # domain workflow services (e.g. Hostaway auth)
│   ├── Jobs/                 # e.g. SyncHostawayReservationJob stub
│   ├── Console/Commands/     # Schedule stubs (no public /cron HTTP)
│   └── Models/User.php       # users.role → UserRole; owner access predicates
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

Owner-specific terms / active-property access:

```
Owner boundary (owner route group)
  → Middleware / Policy
  → Model predicate (User::hasAgreedToTerms / hasActiveAccess)
  → Database

Service only for substantive workflows (e.g. agree + audit + notify)
```

- Controllers: HTTP in/out only.
- **Models** own state, relationships, scopes, and simple domain predicates. **Services** orchestrate workflows — they must not wrap Eloquent lookups or predicate booleans ([ADR-009](adr/009-owner-access-predicates-and-active-flags.md)).
- Roles: `App\Enums\UserRole` on `users.role`; authorization via **Policies** only ([ADR-010](adr/010-user-role-enum-policies.md)). No Spatie; no `Gate::define` for roles.
- Contracts/interfaces only for meaningful boundaries (multiple implementations, external systems) — not to abstract model access.
- Owner middleware/policies attach to **owner route groups** only — not the shared admin shell. Defensive `$user->role === UserRole::Owner` filters are secondary.
- Account eligibility (`users.active`) ≠ active property access (`hasActiveAccess()` from properties). Do not reintroduce `owners.status` as a duplicate enable flag.
- Preserve business behavior unless change is explicitly approved; document suspicious legacy behavior and wait.
- Legacy app is a **business-behavior oracle only** — no Entrust classes, route dumps, or technical patterns ported forward ([ADR-007](adr/007-phase1-greenfield-routing-auth.md)).
- No new global helpers; no `env()` outside config; no direct `DB::` in controllers.

See also [`docs/AGENTS.md`](AGENTS.md), [`.cursor/rules/architecture.mdc`](../.cursor/rules/architecture.mdc), and [`.cursor/rules/migration.mdc`](../.cursor/rules/migration.mdc).

---

## 6. Auth and authorization (Phase 1)

| Piece | Implementation |
|-------|----------------|
| Login | Session (`email` + `password`, optional remember) |
| Controllers | `App\Http\Controllers\Auth\AuthenticatedSessionController` |
| Validation | `App\Http\Requests\Auth\LoginRequest` (rate-limited) |
| Roles | `users.role` cast to `App\Enums\UserRole` |
| Business role values | `superadmin`, `admin`, `manager`, `owner`, `cleaner`, `maintenance` |
| Middleware aliases | `owner.terms`, `owner.active` |
| Policies | `UserPolicy` (`accessSuperAdminArea`, `viewOwnerStatements`, `viewOwnerTerms`) |

Owner terms and active-property access are **owner-scoped** middleware (`owner.terms`, `owner.active`) applied only to Owner–relevant routes, not the shared admin shell. Audience filter: `$user->role === UserRole::Owner`. Predicates live on `User` (`hasAgreedToTerms()`, `hasActiveAccess()`) and currently always allow until terms/property domains land. Account enablement will use canonical `users.active` only — do not reintroduce `owners.status` as a second enable flag ([ADR-009](adr/009-owner-access-predicates-and-active-flags.md)).

**Not in Phase 1:** username-or-email login, password-expiry flow, real terms/property queries.

Decisions: [ADR-007](adr/007-phase1-greenfield-routing-auth.md), [ADR-009](adr/009-owner-access-predicates-and-active-flags.md), [ADR-010](adr/010-user-role-enum-policies.md) (supersedes [ADR-002](adr/002-auth-spatie-permission.md)).

---

## 7. Routes and HTTP surface (current)

| Method + URI | Name | Notes |
|--------------|------|--------|
| `GET /` | `home` | Welcome |
| `GET/POST /login` | `login` | Guest |
| `POST /logout` | `logout` | Auth |
| `GET /admin/dashboard` | `admin.dashboard` | Auth only (shared admin shell) |
| `GET /admin/owner-statements` | `admin.owner-statements.index` | Auth + owner middleware |
| `GET /admin/terms` | `admin.terms.show` | Auth + owner middleware |
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

## 9. Hostaway webhook (Phase 1a)

- Path preserved: `POST /wh/hostaway/booking/created`
- Authenticated via Hostaway-native **HTTP Basic Auth** (`HostawayWebhookAuthenticator`; ADR-008)
- Valid credentials: controller returns **200** quickly and dispatches `SyncHostawayReservationJob`
- Invalid / missing credentials or empty config: **401**, no job dispatch
- Job `handle()` is empty pending Phase 2.1 / Phase 4

Configure matching username/password in Hostaway’s webhook integration settings and in `.env` (`HOSTAWAY_WEBHOOK_USERNAME` / `HOSTAWAY_WEBHOOK_PASSWORD`).

Do not change Hostaway API contracts when Phase 2.1 lands.

---

## 10. Quality and CI

GitHub Actions (`.github/workflows/ci.yml`) runs:

1. `vendor/bin/pint --test`
2. `vendor/bin/phpstan analyse --memory-limit=1G`
3. `vendor/bin/pest`

Pest Feature coverage for Phase 1–1a includes authentication, Policy/`can:` role checks, owner-scoped middleware (model predicates; staff vs owner boundaries), Hostaway webhook Basic Auth success/failure + dispatch behavior, and stub command registration.

---

## 11. Documentation map

| Document | Role |
|----------|------|
| [Laravel_5.1_to_13_Modernization_Spec.md](Laravel_5.1_to_13_Modernization_Spec.md) | Requirements source of truth |
| [migration-plan.md](migration-plan.md) | Execution roadmap (phases, DoD, out-of-scope) — must fully reflect the Spec |
| [migration-inventory.md](migration-inventory.md) | Checklist / cron command map from legacy inventory |
| [technical-documentation.md](technical-documentation.md) | This file — developer reference (Phase 0–1) |
| [user-documentation.md](user-documentation.md) | End-user / QA guide for the current shell |
| [adr/001](adr/001-fresh-laravel-13-skeleton.md) … [010](adr/010-user-role-enum-policies.md) | Architecture decisions |
| [AGENTS.md](AGENTS.md) | Agent/developer guidelines (mirrored into `.cursor/rules/agents.mdc`; architecture heuristics in `architecture.mdc`) |

---

## 12. Out of scope for Phase 0–1a

- Full admin UI and Blade rewrite
- Business schema (`migrations_fresh` live migrate)
- Hostaway sync business logic (Phase 2.1 service; Phase 4 job hardening) — webhook auth is done in Phase 1a
- Bookings, payments, reports, chronology, email (Phase 2+)
- Username login, password expiry, real owner terms/property data
- Horizon, production cutover
