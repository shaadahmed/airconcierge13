# Air Concierge — Laravel 5.1 → Laravel 13 Migration Plan

**Status:** Implementation-ready execution roadmap (all phases)  
**Last updated:** 2026-07-19  
**New project root:** `/home/pc/projects/airconcierge13`  
**Reference (old) app:** `/mnt/e/xampp/htdocs/airconcierge` (Windows: `E:\xampp\htdocs\airconcierge`)

This document is the **execution plan** for the Laravel 5.1 → Laravel 13 migration. It must fully reflect `docs/Laravel_5.1_to_13_Modernization_Spec.md`, which is the **requirements source of truth**. Agents and humans follow this plan for sequencing, tasks, deliverables, dependencies, and exit criteria.

Where this plan records **implementation choices** that satisfy the spec without contradicting it (e.g. PHP 8.3+ vs the spec’s 8.2+ floor, Spatie Permission as the Entrust replacement, Laravel Sail for local runtime, greenfield Phase 1 routing per ADR-007), those notes are preserved. If any plan detail would **omit or weaken** a spec requirement, the specification wins and this plan must be updated.

Do **not** begin Phase 2+ until Phase 0 DoD is met, Phase 1 is complete, and Phase 1a exit criteria are met (or Phase 1a items are explicitly waived with documented approval).

---

## 1. Overall approach

This is a **framework migration + structural refactor**, not a feature sprint and not an in-place upgrade.

**Chosen strategy: fresh Laravel 13 skeleton + domain-by-domain port.**

- Create a new Laravel 13 application in `airconcierge13`.
- Port behavior module by module from the Laravel 5.1 app.
- Do **not** attempt an incremental tree upgrade (5.1 → 5.2 → … → 13). That jump is not practical on the old codebase; the new app becomes the source of truth.
- Do **not** add new features or redesign the UI unless separately approved.
- Optimize for long-term maintainability, not for minimizing the number of changed files.
- Follow **DRY** and **SOLID** throughout.
- Prefer Laravel Events, Listeners, Notifications, Jobs, Bus Batching, and Scheduling where they improve separation of concerns. Do not queue work simply for the sake of using queues.
- Controllers should remain thin orchestration layers. If a controller action grows beyond simple request handling and service coordination, extract the logic into Services, Actions, Jobs, or Events as appropriate.

---

## 2. Reference codebase — location and rules

| Item | Value |
|------|--------|
| Windows path | `E:\xampp\htdocs\airconcierge` |
| WSL path | `/mnt/e/xampp/htdocs/airconcierge` |
| GitHub (private / not readable from this environment) | `https://github.com/ryandanz76/airconcierge` |

### Rules

1. **Read-only.** Never edit files in the reference app as part of this migration.
2. **Do not copy** the old tree into `airconcierge13` (no `airconcierge/` reference folder inside the new project). The local path above is already readable from WSL.
3. **Do not commit** the old app, its `vendor/`, or its `.env` into the new repo. Old `.env` contains secrets (DB, Mailgun, etc.) — never copy those credentials into git.
4. Use the reference **only** to look up how something currently behaves before deciding how to implement it in L13.
5. Never wholesale copy-paste fat controllers or `helpers.php` into the new app — re-implement through the target layering below.

---

## 3. Runtime requirements

| Requirement | Target |
|-------------|--------|
| PHP | **8.3+** (Laravel 13 minimum; satisfies the modernization spec’s 8.2+ floor) |
| Framework | Laravel 13 (latest stable) |
| Database | MySQL 8, database name **`airconcierge`** (aligned with the old app) |
| Schema source | Fresh baseline in the reference app: **`/mnt/e/xampp/htdocs/airconcierge/database/migrations_fresh/`** (~101 files, dated `2026_07_11_*`, ending with `add_foreign_keys_to_fresh_schema`). This replaces the historical ~51 incremental files under `database/migrations/` as the intended L13 schema source. |
| Schema timing | Phase 0 creates the empty MySQL DB only. **Do not copy or run** `migrations_fresh` into `airconcierge13` until explicitly approved (later phase / separate go-ahead). |
| Cache / queue / sessions | Redis |
| Local runtime | **Laravel Sail** (`vendor/bin/sail`) — app + MySQL + Redis; queue worker and scheduler via Sail/Compose services as configured in Phase 0 |
| Production queue/scheduler | Sail/Compose where used; otherwise **supervisor** or **systemd** (as appropriate for the host) — Spec Phase 4 / Phase 6 |
| Host tooling | Docker Desktop must be available in WSL for Sail; if Docker is unavailable, fall back to local PHP 8.3 + Composer and still keep Sail Compose files for parity |

Default new `.env.example` drivers (no real secrets):

- `DB_CONNECTION=mysql`
- `DB_DATABASE=airconcierge`
- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`

---

## 4. Hard rule: behavior preservation

- Preserve all existing business behavior unless explicitly approved to change it.
- If behavior looks broken, inconsistent, or wrong: **do not silently fix it.**
  1. Document the issue.
  2. Explain the impact.
  3. Propose an improved implementation.
  4. Wait for approval before intentionally changing behavior.
- Never change business logic quietly during a refactor.
- Flag business logic found in Blade views as follow-up cards; do not “fix” inline while migrating another concern.

---

## 5. Confirmed baseline of the old app

Gathered by inspecting `/mnt/e/xampp/htdocs/airconcierge` (composer.json, tree, `.env` keys only).

### Framework and environment

| Item | Current state |
|------|----------------|
| Framework | `laravel/framework` **5.1.\*** |
| PHP constraint | `>=5.5.9` |
| App name (env) | Flashlight / Air Concierge |
| DB | MySQL, database `airconcierge`, host `localhost` |
| Cache | `file` |
| Queue | `sync` (queues underused) |
| Mail | Mailgun SMTP configured via env; app also sends via **PHPMailer** directly |

### Composer packages (production)

| Package | Version / note |
|---------|----------------|
| `zizaco/entrust` | `^1.5` (roles/permissions) |
| `yajra/laravel-datatables-oracle` | `~5.0` |
| `sammyk/laravel-facebook-sdk` | `^3.0` |
| `guzzlehttp/guzzle` | `~6.0` |
| `vinkla/hashids` | `2.0.0` |
| `niklasravnsborg/laravel-pdf` | `^4.0` + wkhtmltopdf binaries (`h4cc/*`, Windows package) |
| `phpmailer/phpmailer` | `^6.3` |
| `nao-pon/flysystem-google-drive` | `~1.1` |
| `hellosign/hellosign-php-sdk` | `^3.7` |
| `jeremykenedy/slack-laravel` | `^2.6` |
| `doctrine/dbal` | `^3.3` |
| `flynsarmy/csv-seeder` | `1.*` |
| `filp/whoops` | `~1.0` |

### Structure and scale

| Area | Baseline |
|------|----------|
| Models | ~75 (per project prompt) |
| Controllers | Controllers under `app/Http/Controllers/` including `Admin/`, `Api/`, `Auth/`, plus root controllers (e.g. `HostawayController`, `OwnerRegistrationController`). Fat controllers called out in the prompt: AjaxDashboardController (~4,800 lines), BookingController (~2,100), ChronologycronController (~1,350), PropertyController (~1,580), PaymentController (~1,450), CreateBookingController (~1,450), CronJobsController (~1,100), HostawayController (~1,170), ReportController (~1,050) |
| Routes | Single ~**978-line** `app/Http/routes.php` (string-based controller references) |
| Services | Only **2**: `HostawayService`, `ImageCompressionService` |
| Helpers | `app/helpers.php` autoloaded via Composer `files` — ~100 global functions, ~3,600 lines (per prompt) |
| Form Requests | None — validation inline in controllers |
| Auth | Entrust + custom `OwnerAccessVerifier` |
| Jobs / queues | Barely used (stubs); most work is sync or HTTP cron |
| Artisan commands | Many already exist (alerts, imports, payouts, image compression, cloud upload, etc.) |
| Cron | Mix of **public HTTP routes** (`/cron/*` style) and Artisan commands |
| Email | PHPMailer directly, not Laravel Mail |
| Migrations (legacy) | ~**51** incremental files under `database/migrations/` — historical; not the L13 baseline |
| Migrations (fresh) | **`database/migrations_fresh/`** — ~**101** create/FK migrations (`2026_07_11_000001` … `000101`). **Source of truth for the schema** when porting to L13. Still live only in the reference app; not copied/run into `airconcierge13` yet |
| Blade views | ~198 templates (per prompt) |
| Tests | Effectively **zero** meaningful coverage (legacy phpunit/phpspec scaffolding only) |

### Key integrations

Hostaway, Zoho Sign / HelloSign, Dropbox, Google Drive, Slack, PDF generation (wkhtmltopdf).

### Known fat controllers (priority refactor targets)

| Controller | Approx. lines |
|------------|---------------|
| AjaxDashboardController | ~4,800 |
| BookingController | ~2,100 |
| ChronologycronController | ~1,350 |
| PropertyController | ~1,580 |
| PaymentController | ~1,450 |
| CreateBookingController | ~1,450 |
| CronJobsController | ~1,100 |
| HostawayController | ~1,170 |
| ReportController | ~1,050 |

---

## 6. Target folder / project layout

```
/home/pc/projects/airconcierge13/          # NEW git root (Laravel 13 + Sail)
├── .gitignore                             # currently ignores docs/ (temporary — un-ignore in Phase 0)
├── docs/                                  # project docs (tracked in git)
│   ├── migration-plan.md                  # this file — execution roadmap for all phases
│   ├── migration-inventory.md             # legacy inventory / cron command map
│   ├── technical-documentation.md         # developer reference (Phase 0–1+)
│   ├── user-documentation.md              # end-user / QA guide for the current shell
│   ├── Laravel_5.1_to_13_Modernization_Spec.md  # requirements source of truth
│   └── adr/                               # architecture decision records
├── app/
│   ├── Http/Controllers/                  # thin HTTP only
│   ├── Http/Requests/                     # Form Requests
│   ├── Policies/
│   ├── Services/                          # domain services (Bookings, Payments, …)
│   ├── Jobs/
│   ├── Models/
│   └── …
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── compose.yaml / docker-compose.yml      # Sail-managed
└── …

/mnt/e/xampp/htdocs/airconcierge/          # OLD app — OUTSIDE new git, read-only
└── database/
    ├── migrations/                        # ~51 legacy incremental (not L13 baseline)
    └── migrations_fresh/                  # ~101 fresh baseline — schema source of truth
```

The old app is **not** nested inside `airconcierge13`.

---

## 7. Layering standard (every migrated module)

Enforce this flow throughout (spec Target Layering):

```
Request
  → Controller          (HTTP in/out only — no business logic)
  → Form Request        (validation)
  → Policy / Gate       (authorization; scope to the smallest appropriate boundary)
  → Service             (business workflows — multi-step orchestration)
  → Model               (data access; relations, scopes, casts, simple domain predicates)
  → Job                 (async work where appropriate)
  → Event / Listener    (side effects where appropriate)
```

Repositories are optional — use them when query complexity warrants it; do not invent a repository for every model.

Owner-specific auth/onboarding (terms, active property access) follows:

```
Owner boundary (owner route group or equivalent Policy)
  → Middleware / Policy
  → Model predicate
  → Database
```

A Service participates only when the operation is a substantive workflow. See [ADR-009](adr/009-owner-access-predicates-and-active-flags.md).

### Mandatory guidelines (from spec + ADR-009)

- Keep controllers thin — HTTP in/out only; no business logic.
- Move **workflow** business logic into dedicated Service classes (domain-grouped: Bookings, Payments, Chronology, Hostaway, Reports, etc.).
- Keep models focused on relationships, scopes, casts, entity behavior, and **simple domain predicates** (`hasX()` / `isY()`) — not multi-step orchestration.
- **Do not** create services that only wrap Eloquent queries or model predicates. Prefer extending the model first.
- **Do not** add contracts/interfaces solely to abstract model access — only when multiple substantive implementations or external boundaries justify them.
- Use Form Request classes for all non-trivial validation (replace inline `Validator::` / `$this->validate()` in controllers).
- Use Policies / Gates for authorization — replace Entrust middleware patterns over time. Attach role-specific enforcement to the **smallest** route/authorization boundary (e.g. owner middleware on owner routes only — not the entire admin shell). **Role identity** is `UserRole` + `users.role`; **role authorization** is Policies with `match` on the enum (ADR-010). Do not use Spatie or `Gate::define` for roles.
- Follow DRY and SOLID principles; **avoid duplicate sources of truth** (especially enable/active flags — ADR-009).
- Prefer constructor dependency injection over facades where practical (especially in services).
- Replace deprecated helpers and APIs (`Input::`, old route syntax, legacy middleware names, etc.).
- Remove obsolete framework patterns (e.g. old controller routing strings, legacy auth flows where Laravel 13 equivalents exist).
- Do not introduce new technical debt — no new global helpers; no new logic in Blade views.
- Interfaces only when multiple implementations are genuinely expected (do not over-abstract).
- Every refactored critical path gets at least one feature/integration test.

### Code quality rules (enforce during PR review — from spec + ADR-009)

- No new methods >50 lines in controllers without justification.
- No direct `DB::` in controllers — use models or query objects in services.
- No `Mail::send()` closures or raw PHPMailer in controllers.
- No `env()` outside config files.
- No new `0000-00-00` date handling — use nullable dates / Carbon.
- Every new Service gets an interface only if multiple implementations are expected.
- Every refactored critical path gets at least one feature/integration test.
- Before adding a service: *Could this method belong on the model? Is this orchestrating a workflow or only wrapping a query?*
- Before attaching middleware: *Is this applied more broadly than necessary?*
- Schema: **`users.active` is the sole account enable flag**; do **not** reintroduce `owners.status` as a duplicate enable toggle; **active access** is computed from properties (never a stored owner-level active flag).

### Sync vs async (from spec)

- Queue if I/O-bound or expected runtime > ~2s.
- Keep sync when the user needs immediate feedback, transactional integrity requires it, or the operation is trivial (<~100ms) — document the rationale in a brief code comment or ADR note.
- Do not queue solely for the sake of using queues.

### Job standards (from spec — apply to every job)

Each job must be:

- Small and single-purpose
- Idempotent where possible (safe to retry)
- Retry-safe with configured `$tries` / backoff
- Properly logged (structured context: entity ID, user, correlation ID)
- Fail gracefully — use `failed()` handler; surface to monitoring (Slack already in stack)
- Batched or chained where appropriate (e.g. bulk email sends, metric recalculations)

---

## 8. Full phase breakdown

Phases follow the **Structural Refactor Checklist** order in the modernization specification. Within Phase 2 (Extract Services), domain extraction order follows the spec’s **Recommended Migration Order**.

```text
Phase 0  Foundation                                          [IMPLEMENTED]
    → Phase 1  Routing & Middleware                          [IMPLEMENTED]
    → Phase 1a Early-stage gap closure (pre–Phase 2)       [IMPLEMENTED]
    → Phase 2  Extract Services (by domain)                  [NEXT]
         2.1 Hostaway + webhooks
         2.2 Email / Chronology
         2.3 Bookings & Payments
         2.4 Reports & Dashboard
         2.5 Remaining admin modules
    → Phase 3  Helpers Decomposition
    → Phase 4  Async Migration
    → Phase 5  Frontend / Views (as needed)
    → Phase 6  Deployment Readiness
```

Spec Recommended Migration Order (maps onto Phase 0 → 1 → 2.1 → 2.2 → 2.3 → 2.4 → 2.5):

1. Infrastructure (L13, queue, tests, CI) — Phase 0  
2. Auth & routing (Entrust → Policies, secure crons) — Phase 1 (+ Phase 1a gaps)  
3. Hostaway + webhooks — Phase 2.1  
4. Email/Chronology — Phase 2.2  
5. Bookings & Payments — Phase 2.3  
6. Reports & Dashboard — Phase 2.4  
7. Remaining admin modules — Phase 2.5  

---

### Phase 0 — Foundation (no business port yet)

**Status:** Implemented.

**Goal:** Bootable Laravel 13 project with Sail, Redis queues, Pest, CI, and documented decisions. Old app untouched. Business schema not imported yet.

| Work item | Detail |
|-----------|--------|
| Scaffold | `laravel new` / `composer create-project` → Laravel 13 inside `airconcierge13` (only when Phase 0 execution is approved) |
| PHP | 8.3+ (Sail image) |
| Runtime | **Laravel Sail** — MySQL, Redis, app; queue worker (`queue:work`) and scheduler (`schedule:work` in dev) via Sail/Compose |
| Env | Redis for queue/cache/session; MySQL DB **`airconcierge`** (empty of business schema); `.env.example` without secrets |
| Schema | Fresh baseline exists at reference `database/migrations_fresh/` (~101 files). **Phase 0 does not copy or run them.** Framework default migrations only if needed to boot. Port/adapt `migrations_fresh` into L13 `database/migrations/` only when explicitly approved later. |
| Tests | **Pest** — at least one smoke test |
| Quality | Laravel Pint + **Larastan level 5** |
| Horizon | **Not** in Phase 0 — plain Redis `queue:work` only |
| CI | Lint (`pint --test`), static analysis, tests |
| Agent tooling | Laravel Boost (`composer require laravel/boost --dev`) |
| Project rules | Encode behavior-preservation + layering in `.cursor/rules` and `docs/AGENTS.md` |
| Docs in git | Un-ignore `docs/` so this plan, ADRs, and inventory are tracked |
| ADRs | See list below |
| Inventory | `docs/migration-inventory.md` — controllers, cron HTTP routes, packages, helpers — checklist only, no ports |

**Spec Phase 0 checklist (mapped):**

- [x] Set up Laravel 13 skeleton (or incremental upgrade branch strategy — document chosen approach) → fresh skeleton, ADR-001
- [x] PHP 8.2+ runtime requirement → satisfied by PHP 8.3+ / Sail 8.4
- [x] Configure `.env` / config for queue driver (Redis recommended), cache, sessions
- [x] Set up PHPUnit/Pest test harness (currently zero tests)
- [x] Add CI pipeline: lint, test, static analysis (PHPStan/Psalm at sensible level) → Pint + Larastan level 5 + Pest in CI
- [x] Document rollback plan → ADR-006

**Rollback (Phase 0 / pre-cutover):**

- Keep the old XAMPP app at `/mnt/e/xampp/htdocs/airconcierge` live and untouched as the production system of record until an explicit staging/production cutover is approved.
- The new app is a **separate deploy**. Until cutover, “rollback” means: leave the new deploy unused / take it down; the old app continues to serve traffic.
- No production cutover and no shared live-DB cutover happen in Phase 0.

**ADRs to write under `docs/adr/`:**

1. Fresh L13 skeleton vs in-place incremental upgrade → **fresh skeleton**.
2. Auth: Entrust → **Spatie Permission** + Policies/Gates (including replacing `OwnerAccessVerifier` — see Phase 1).
3. Queues: **Redis** via Sail (Horizon later if needed — not Phase 0).
4. Mail: **Laravel Mail / Mailables** (retire PHPMailer).
5. PDF: spike DomPDF / Browsershot / maintained wkhtmltopdf wrapper — decide after spike.
6. Rollback: old XAMPP app stays live; new app is a separate deploy (see rollback subsection above).

**Prerequisite note:** Docker was previously flaky in this WSL environment (`Input/output error`). Phase 0 must verify Docker Desktop is healthy before relying on Sail.

---

### Phase 1 — Routing & authorization (greenfield)

**Status:** Implemented (scaffold). See ADR-007.

Phase 1 is a **rewrite scaffold**, not a dump of the L5.1 `routes.php`. Legacy app is a **business-behavior oracle only** — no Entrust classes, helpers, or technical patterns are ported.

- Scaffold `routes/web.php`, `routes/api.php`, `routes/console.php` with class-based routes; add domain routes as modules land in later phases.
- Install **UserRole** enum + `users.role` + Policies (ADR-010; Spatie removed). Role values: superadmin, admin, manager, owner, cleaner, maintenance.
- Session auth shell (login/logout). Owner terms / active-property access as **owner-scoped** middleware on Owner–relevant routes only, calling `User` model predicates (Phase 1 always-allow stubs until terms/properties domains land; ADR-009) — do **not** port `OwnerAccessVerifier`, checker-service wrappers, or dual `users.active` / `owners.status` enable flags.
- **No public `/cron/*` HTTP routes.** Artisan command stubs + Laravel Schedule only (frequencies aligned to known business schedules).
- Hostaway webhook path preserved (`POST /wh/hostaway/booking/created`): return 200 fast, dispatch job stub; **webhook Basic Auth verification completed in Phase 1a** (ADR-008).
- Schema: Spatie + default Laravel `users` only — do **not** copy `migrations_fresh` into live migrations in this phase.

**Spec Phase 1 checklist (mapped):**

- [x] Split `routes.php` into `routes/web.php`, `routes/api.php`, `routes/console.php` (greenfield split files; domain routes added as modules land)
- [x] Convert string controller references (`"Admin\BookingController@index"`) to class-based routes (for all routes registered in the scaffold; remaining domain routes remain class-based as they land)
- [x] Migrate Entrust middleware to Policies (UserRole enum + UserPolicy; Spatie removed per ADR-010)
- [x] Secure or remove public HTTP cron routes (`/cron/*`) — replace with Schedule + authenticated commands only
- [x] Review webhook routes (Hostaway) — **validate signatures** (Hostaway Basic Auth per ADR-008), return 200 fast, queue processing → **completed in Phase 1a**

---

### Phase 1a — Early-stage gap closure (pre–Phase 2)

**Status:** Implemented (2026-07-19).  
**Purpose:** Capture every specification requirement that belongs **before Phase 2 (Extract Services)** but was not completed in Phase 0 or Phase 1. Do not duplicate completed Phase 0/1 work.

#### Objectives

- Close the Spec Phase 1 Hostaway webhook gap: **validate signatures** (Phase 1 already returns 200 fast and dispatches a queue job stub).
- Complete a formal Package & Integration Migration audit status for every Spec-listed Composer dependency so Phase 2+ replacements have an approved home phase.
- Confirm Spec Phase 1 “Policies/Gates” scaffold is sufficient to start domain extraction (no new Entrust port; domain Policies created when modules land in Phase 2).

#### Tasks

1. **Hostaway webhook signature validation (Spec Phase 1 remainder)** — **done**
   - Implemented as Hostaway-native **Basic Auth** verification on `POST /wh/hostaway/booking/created` via `HostawayWebhookAuthenticator` (ADR-008).
   - Valid requests return **200** and dispatch `SyncHostawayReservationJob` (stub until Phase 2.1 / Phase 4).
   - Invalid/missing credentials (or empty config) return **401** with no job dispatch.
   - Removed Phase 2 signature TODO from controller/routes; docs/ADRs updated.
   - Pest feature tests cover valid auth, invalid auth, missing auth, and empty config.

2. **Package & Integration Migration audit (Spec package table — audit only)** — **done**
   - Status + ownership recorded in §10 and `docs/migration-inventory.md`.
   - No bulk package replacements performed in Phase 1a.

3. **Architecture Decisions (ADR) gap check** — **done**
   - ADR-001–007 reviewed; ADR-004 phase wording aligned to Phase 2.2; ADR-008 added for Hostaway Basic Auth.
   - Policies/Gates + Spatie scaffold confirmed sufficient to start Phase 2 domain extraction (domain Policies land with modules).

#### Deliverables

- Hostaway webhook Basic Auth validation implemented and tested.
- Package audit matrix updated in this plan (§10) and `docs/migration-inventory.md` with phase ownership for every Spec package.
- Docs/ADRs updated so Phase 1’s “signature TODO → Phase 2” language no longer applies.

#### Dependencies

- Phase 0 DoD met; Phase 1 scaffold complete.
- Hostaway webhook credentials available via config (not `env()` outside config files).

#### Exit criteria

- [x] Spec Phase 1 Hostaway item fully satisfied: authenticate webhook **and** return 200 fast **and** queue processing for accepted webhooks
- [x] Pest coverage for auth success/failure paths
- [x] Spec package table has an explicit ownership phase for every listed dependency
- [x] No Spec Phase 0 or Phase 1 checklist item remains open except intentionally deferred items that now have an explicit later-phase owner

#### Deviations from original Phase 1a wording

- Spec/plan said “signature” / “signing secret”; Hostaway provides Basic Auth only. Implemented Basic Auth per ADR-008 with stakeholder approval during planning (not a custom HMAC).

---

### Phase 2 — Extract Services (by domain)

**Status:** Not started.  
**Spec alignment:** Structural Refactor Checklist — Phase 2.

Extract **workflow** business logic from fat controllers into domain-grouped Service classes. Controllers become thin HTTP orchestration. Validation moves to Form Requests; authorization to Policies/Gates. Simple domain predicates stay on models (ADR-009) — do **not** invent checker/query-wrapper services. Do **not** change third-party API contracts. Prefer incremental PRs by domain.

**Domain extraction order** follows the Spec Recommended Migration Order (subsections below). Spec Phase 2 checklist items appear in the subsection where they are implemented.

#### Objectives

- Move business logic into dedicated Services (Bookings, Payments, Chronology, Hostaway, Reports, Email, Documents, Import, Zoho/HelloSign, and remaining admin as touched).
- Keep models focused on relationships, scopes, casts, and entity behavior — not orchestration.
- Apply Target Layering and per-module Definition of Done for every extracted domain.
- Prefer Events, Listeners, Notifications, Jobs, Bus Batching, and Scheduling where they improve separation of concerns (full job infrastructure and job catalog completion is Phase 4).

#### Dependencies

- Phase 1a exit criteria met.
- Relevant `migrations_fresh` tables copied into live `database/migrations/` only when a domain needs them (explicit approval per domain).
- Behavior preservation rules in force; suspected bugs documented and held for approval.

#### Exit criteria (phase-level)

- [ ] Every Spec Phase 2 service checklist item below is implemented or explicitly deferred with approval
- [ ] Touched controllers reduced per Definition of Done (§11)
- [ ] Critical paths for each extracted domain have at least one feature/integration test
- [ ] No new global helpers; no new business logic in Blade introduced by this phase

---

#### Phase 2.1 — Hostaway + webhooks

**Why first:** High integration risk; already has a service stub; Spec Recommended Migration Order.

##### Objectives

- Extend/port `HostawayService`; move logic out of `HostawayController`.
- Keep webhook path and contracts stable; processing remains fast at the HTTP boundary (signature validation already in Phase 1a).
- Prepare Hostaway sync for full async completion in Phase 4 (`SyncHostawayReservationJob`).

##### Tasks

- [ ] `HostawayService` — extend existing service; move logic out of `HostawayController`
- [ ] Thin `HostawayController` / webhook controller to HTTP + service/job delegation only
- [ ] Form Requests + Policies/Gates for non-trivial Hostaway admin endpoints as they are ported
- [ ] Wire accepted webhooks to `SyncHostawayReservationJob` calling `HostawayService` (job hardening / retries / monitoring completed in Phase 4)
- [ ] Prioritize tests around webhook ingest and sync idempotency
- [ ] Upgrade `guzzlehttp/guzzle` to Guzzle 7+ if required for Hostaway HTTP client work
- [ ] Do not change Hostaway API contracts

##### Deliverables

- `HostawayService` owning Hostaway business logic
- Thin Hostaway HTTP layer; webhook remains fast
- Feature/integration tests for webhook ingest / sync idempotency

##### Dependencies

- Phase 1a Hostaway webhook Basic Auth complete (ADR-008)
- Schema tables for Hostaway/reservation domain available when needed

##### Known fat controller

- `HostawayController` (~1,170 lines)

---

#### Phase 2.2 — Email / Chronology

**Why next:** Largest async win per Spec Recommended Migration Order; feeds Phase 4 email jobs.

##### Objectives

- Extract chronology and email domain logic into services.
- Centralize PHPMailer usage and migrate to Laravel Mail / Mailables / Notifications.
- Wrap signature SDKs; verify Zoho vs HelloSign migration status before deep investment.

##### Tasks

- [ ] `ChronologyService` — from `ChronologyController`, `ChronologycronController`
- [ ] `EmailService` — centralize PHPMailer usage; migrate to Mailables / Notifications
- [ ] `ZohoSignService` / `HelloSignService` — from chronology controllers; wrap `hellosign/hellosign-php-sdk`; verify Zoho migration status
- [ ] Form Requests + Policies/Gates for non-trivial endpoints as ported
- [ ] Thin chronology/email/sign controllers to HTTP + service delegation
- [ ] Replace `phpmailer/phpmailer` usage on touched paths with Laravel Mail
- [ ] Feature/integration tests on critical chronology/email/sign paths
- [ ] Flag any Blade-embedded chronology/email business logic as follow-up cards

##### Deliverables

- `ChronologyService`, `EmailService`, `ZohoSignService` / `HelloSignService`
- Outbound mail on migrated paths using Laravel Mail / Mailables / Notifications
- Controllers thin; critical-path tests present

##### Dependencies

- ADR-004 (Laravel Mailables) followed
- HelloSign/Zoho status verified before large HelloSign investment
- Jobs `SendOutboundEmailJob`, `SendNotificationJob`, `ProcessSignatureRequestJob` may be stubbed here; full job standards + monitoring in Phase 4

##### Known fat controllers

- `ChronologycronController` (~1,350 lines)

---

#### Phase 2.3 — Bookings & Payments

**Why next:** Core business; fattest controllers per Spec Recommended Migration Order.

##### Objectives

- Extract booking and payment domain logic into services.
- Thin Booking/Payment controllers; Form Requests + Policies for all non-trivial endpoints.

##### Tasks

- [ ] `BookingService` — from `BookingController`, `CreateBookingController`
- [ ] `PaymentService` — from `PaymentController`, `PropertyPaymentsController`
- [ ] Form Requests + Policies for all non-trivial booking/payment endpoints
- [ ] Thin controllers; feature tests on critical booking/payment paths
- [ ] Identify PDF-related payment/booking artifacts for `GeneratePdfJob` (Phase 4)
- [ ] Apply PDF package decision (ADR-005) on touched PDF paths
- [ ] No regression in existing booking/payment behavior

##### Deliverables

- `BookingService`, `PaymentService`
- Thin booking/payment controllers; Form Requests; Policies
- Critical-path feature tests

##### Dependencies

- Schema tables for bookings/payments available when needed
- PDF approach from ADR-005 when generating payment/booking PDFs

##### Known fat controllers

- `BookingController` (~2,100 lines)
- `CreateBookingController` (~1,450 lines)
- `PaymentController` (~1,450 lines)

---

#### Phase 2.4 — Reports & Dashboard

**Why next:** Read-heavy; follows core booking/payment stability per Spec Recommended Migration Order.

##### Objectives

- Extract report and dashboard domain logic into services.
- Thin Report/Dashboard controllers; prepare heavy report/PDF work for Phase 4 jobs.

##### Tasks

- [ ] `ReportService` — from `ReportController`, `TotReportController`
- [ ] `DashboardService` — from `AjaxDashboardController`, Dashboard
- [ ] Form Requests + Policies/Gates as endpoints are ported
- [ ] Thin controllers; feature/integration tests on critical report/dashboard paths
- [ ] Identify work for `GenerateReportJob` / `GeneratePdfJob` (Phase 4)
- [ ] Note Yajra DataTables dependency for Phase 5 verification (upgrade may begin here if reports require it earlier — prefer Phase 5 unless blocked)

##### Deliverables

- `ReportService`, `DashboardService`
- Thin report/dashboard controllers; critical-path tests

##### Dependencies

- Stable booking/payment data paths where reports depend on them
- PDF approach from ADR-005 for report PDFs

##### Known fat controllers

- `AjaxDashboardController` (~4,800 lines)
- `ReportController` (~1,050 lines)

---

#### Phase 2.5 — Remaining admin modules

**Why last:** Spec Recommended Migration Order — remaining admin modules after core domains.

##### Objectives

- Extract remaining Spec Phase 2 services and other admin domains as touched.
- Address remaining known fat controllers not covered above (e.g. `PropertyController`, `CronJobsController` HTTP surface already removed in Phase 1 — logic lands in services/commands).

##### Tasks

- [ ] `DocumentService` — from `UploadDocumentController`, `DocumentlistController`
- [ ] `ImportService` — from `ImportDataController`, `ImportedEmailsController`
- [ ] Property domain service(s) as `PropertyController` (~1,580 lines) is touched (priority fat-controller target; extract when module is migrated)
- [ ] When importing `owners` / properties / terms schema:
  - [ ] Implement real `User::hasAgreedToTerms()` / `User::hasActiveAccess()` against domain tables — **no** checker-service or contract wrappers for those predicates (ADR-009)
  - [ ] Canonical account enable: **`users.active` only** — do **not** port `owners.status` as a second enable flag (verify reference app; flag if a distinct lifecycle meaning exists)
  - [ ] Keep owner terms / active middleware on **owner route groups** only; extend tests for staff vs owner boundaries
  - [ ] Update AGENTS / technical docs if behavior or routes change
- [ ] Image compression: keep `CompressUploadedImages` (or L13 equivalent) as command; prepare per-batch job dispatch for Phase 4
- [ ] Cloud backup / Dropbox CSV / property metrics paths prepared for Phase 4 jobs (`UploadBackupToCloudJob`, `ProcessDropboxCsvJob`, `RecalculatePropertyMetricsJob`)
- [ ] Alert cron check logic owned by scheduled Artisan commands / services (no public HTTP cron endpoints)
- [ ] Package work as consumers land:
  - [ ] `nao-pon/flysystem-google-drive` → Flysystem v3 + Laravel filesystem config
  - [ ] `vinkla/hashids` → verify L13 compatibility or replace
  - [ ] `sammyk/laravel-facebook-sdk` → verify still needed; remove or replace
  - [ ] `doctrine/dbal` → keep/pin if still needed for schema introspection
  - [ ] Revisit `flynsarmy/csv-seeder` — likely replace with modern seeders or one-off import commands
  - [ ] `filp/whoops` — not needed on L13 (framework error handling)
- [ ] Form Requests + Policies/Gates + thin controllers + critical-path tests per module
- [ ] Flag Blade-embedded business logic as follow-up cards

##### Deliverables

- `DocumentService`, `ImportService`, and other admin services as modules land
- Property domain extraction when Property module is migrated
- Package decisions recorded for remaining Spec packages owned by this subsection

##### Dependencies

- Prior Phase 2 subsections as needed by shared entities
- Schema tables copied only when the domain needs them

##### Known fat controllers

- `PropertyController` (~1,580 lines)
- `CronJobsController` (~1,100 lines) — HTTP cron surface already removed; remaining business logic → commands/services/jobs

---

### Phase 3 — Helpers Decomposition

**Status:** Not started.  
**Spec alignment:** Structural Refactor Checklist — Phase 3.

#### Objectives

- Audit all ~100 functions in legacy `app/helpers.php`.
- Move each function to an appropriate home; eliminate the `helpers.php` Composer autoload entry.
- Do not introduce new global helpers.

#### Tasks

- [ ] Audit all ~100 functions in `app/helpers.php`
- [ ] Move to appropriate homes:
  - Date/formatting → Value Objects or `Support\DateFormatter`
  - Simple entity questions / predicates → Models
  - Multi-step business workflows → Services (not query wrappers)
  - View-only formatting → View Composers or Blade components
- [ ] In particular, do **not** recreate legacy `hasActiveProperty`-style helpers as global functions or thin services — implement as model predicates (ADR-009)
- [ ] Goal: eliminate `helpers.php` autoload entry
- [ ] Ensure no new global helpers are added during or after this work
- [ ] Add/adjust tests where helper logic moves into Services or Support classes that implement critical behavior

#### Deliverables

- Helpers inventory (can extend `docs/migration-inventory.md`) with destination for each function
- Migrated Support/Service/View Composer/Blade component homes
- Composer `files` autoload entry for `helpers.php` removed when empty/unused
- No regression on paths that previously depended on helpers

#### Dependencies

- Phase 2 domains that still call helper functions should prefer calling Services/Support during extraction; Phase 3 finishes remaining helpers and removes the autoload entry
- Behavior preservation: do not silently change helper business rules

#### Exit criteria

- [ ] All ~100 helper functions audited and relocated or explicitly deleted with approval
- [ ] `helpers.php` Composer autoload entry eliminated
- [ ] No new global helpers remain in the migration path

---

### Phase 4 — Async Migration

**Status:** Not started.  
**Spec alignment:** Structural Refactor Checklist — Phase 4; Queues & Jobs section.

#### Objectives

- Stand up production-grade queue worker infrastructure.
- Implement all high-priority jobs from the Spec Queues section (prioritize email + Hostaway webhook + PDF).
- Finish migration of HTTP cron responsibility to schedule-driven commands that dispatch jobs.
- Add failed-job monitoring and a retry dashboard.
- Enforce Job Standards and “When NOT to Queue” rules on every job.

#### Tasks

- [ ] Stand up queue worker infrastructure (supervisor/systemd in production; Sail `queue:work` already for local/dev from Phase 0)
- [ ] Implement jobs listed in the Queues section (prioritize email + Hostaway webhook + PDF):

  | Operation | Current location (old app) | Recommended approach / target job |
  |-----------|----------------------------|-------------------------------------|
  | Outbound emails / chronology mail | ChronologycronController, SendemailsController, helpers.php | `SendOutboundEmailJob` |
  | Owner/regional notifications | OwnerEmailNotificationController, cron controllers | `SendNotificationJob` |
  | PDF generation | ReportController, PaymentController, BookingCancellationController, OwnersMonthlyPayout | `GeneratePdfJob` |
  | Image compression | CompressUploadedImages command | Keep as command; dispatch per-batch jobs |
  | Email imports (Airbnb/VRBO) | ProcessAirbnbEmails, ProcessVrboEmails, ImportEmails | `ProcessImportedEmailJob` |
  | Google Drive backup | CloudBackupCronController | `UploadBackupToCloudJob` |
  | Dropbox CSV processing | DropboxFormCSVController | `ProcessDropboxCsvJob` |
  | Hostaway sync / webhooks | HostawayController | `SyncHostawayReservationJob` (webhook returns fast) |
  | Zoho Sign / HelloSign flows | ChronologycronController, ZohoSignController | `ProcessSignatureRequestJob` |
  | Property monthly metrics | PropertyMonthlyMatricsCalculation | `RecalculatePropertyMetricsJob` (batched) |
  | Alert cron checks | CronJobsController HTTP routes | Migrate to scheduled Artisan commands dispatching jobs — remove public HTTP cron endpoints |
  | Report generation | ReportController, TotReportController | `GenerateReportJob` |

- [ ] Migrate HTTP crons to Laravel Schedule dispatching jobs (L13: `routes/console.php` schedule definitions; Spec references `app/Console/Kernel.php` — use the L13 equivalent)
- [ ] Preserve all existing cron schedules and webhook URL paths unless DevOps coordinates cutover
- [ ] Add failed-job monitoring and retry dashboard
- [ ] Replace `jeremykenedy/slack-laravel` with Laravel’s Slack notification channel for failure/monitoring signals (if not done earlier)
- [ ] Apply Job Standards to every job: small & single-purpose; idempotent where possible; `$tries`/backoff; structured logging (entity ID, user, correlation ID); `failed()` → monitoring; batch/chain where appropriate
- [ ] Document sync exceptions briefly when work is intentionally not queued (user waiting, transactional integrity, or <~100ms)

#### Deliverables

- All Spec-listed high-priority jobs implemented (or documented sync-with-rationale where not queued)
- Production queue workers via supervisor/systemd (or equivalent host standard)
- Schedule-driven cron replacement fully eliminating public HTTP cron endpoints
- Failed-job monitoring + retry dashboard
- Slack (or equivalent) failure surfacing wired

#### Dependencies

- Phase 2 services that jobs call should exist for prioritized domains (email, Hostaway, PDF consumers)
- Redis queue connection from Phase 0
- Package: Slack notification channel; PDF package decision for `GeneratePdfJob`

#### Exit criteria

- [ ] Queue worker infrastructure stood up for the target deploy environment
- [ ] Spec job catalog implemented with standards applied (email + Hostaway + PDF prioritized first)
- [ ] HTTP crons fully migrated to Schedule → commands/jobs
- [ ] Failed-job monitoring and retry dashboard in place
- [ ] No queuing solely for the sake of queues; sync exceptions documented

---

### Phase 5 — Frontend / Views (as needed)

**Status:** Not started.  
**Spec alignment:** Structural Refactor Checklist — Phase 5.

#### Objectives

- Refactor Blade templates incrementally as modules are touched — not all ~198 at once.
- Replace inline PHP/logic in views with View Models or components where touched.
- Verify DataTables JS integration after Yajra upgrade.

#### Tasks

- [ ] ~198 Blade templates — refactor incrementally, not all at once
- [ ] Replace inline PHP/logic in views with View Models or components where touched
- [ ] DataTables JS integration — verify compatibility after Yajra upgrade
- [ ] Upgrade `yajra/laravel-datatables-oracle` ~5 to current Yajra DataTables for L13 (if not already completed when reports required it)
- [ ] Flag any business logic discovered in Blade during migration — log as follow-up card; do not silently “fix” while migrating another concern
- [ ] No full rewrite of all 198 Blade templates in one pass (out of scope unless separately approved)

#### Deliverables

- Touched Blade views free of newly introduced business logic; extracted View Models/components where logic was moved
- Yajra DataTables on L13 with verified JS integration
- Follow-up cards for Blade business logic found during migration

#### Dependencies

- Domain modules from Phase 2 that own the views being touched
- Yajra upgrade completed before final DataTables verification

#### Exit criteria

- [ ] Incremental Blade refactor approach followed (no big-bang rewrite)
- [ ] Inline logic replaced with View Models/components on touched templates
- [ ] DataTables JS verified after Yajra L13 upgrade
- [ ] Blade business-logic findings tracked as follow-up cards

---

### Phase 6 — Deployment Readiness

**Status:** Not started.  
**Spec alignment:** Structural Refactor Checklist — Phase 6.

#### Objectives

- Satisfy Spec Phase 6 deployment readiness checklist.
- Confirm quality gates, workers, scheduler, staging deploy, and manual QA before production.

#### Tasks

- [ ] Pint passes
- [ ] Larastan passes
- [ ] All automated tests pass
- [ ] Queue workers configured
- [ ] Scheduler configured
- [ ] Staging deployment completed
- [ ] Manual QA completed before production deployment
- [ ] Confirm remaining Spec package actions are closed or explicitly accepted as deferred with approval
- [ ] Confirm no Spec Definition of Done item is open for modules claimed “migrated”
- [ ] Prefer long-term maintainability over minimizing the number of changed files in final hardening PRs

#### Deliverables

- Green Pint, Larastan, and full automated test suite
- Configured queue workers and scheduler in target environments
- Staging deployment + completed manual QA sign-off before production

#### Dependencies

- Phases 2–5 complete for modules intended for the first production cutover (or an explicitly approved partial cutover scope)
- Phase 4 queue/scheduler infrastructure available for production-like staging

#### Exit criteria

- [ ] Pint passes
- [ ] Larastan passes
- [ ] All automated tests pass
- [ ] Queue workers configured
- [ ] Scheduler configured
- [ ] Staging deployment completed
- [ ] Manual QA completed before production deployment

---

## 9. Job candidates (cross-phase reference)

Canonical Spec catalog. Implementation ownership: **Phase 4** (services that jobs call: **Phase 2**).

| Operation | Current location (old app) | Target job |
|-----------|----------------------------|------------|
| Outbound emails | Chronologycron, Sendemails, helpers | `SendOutboundEmailJob` |
| Owner/regional notifications | OwnerEmailNotification, notification crons | `SendNotificationJob` |
| PDF generation | Report, Payment, BookingCancellation, OwnersMonthlyPayout | `GeneratePdfJob` |
| Image compression | CompressUploadedImages command | Keep command; per-batch jobs |
| Email imports (Airbnb/VRBO) | ProcessAirbnbEmails, ProcessVrboEmails, ImportEmails | `ProcessImportedEmailJob` |
| Google Drive backup | CloudBackupCronController | `UploadBackupToCloudJob` |
| Dropbox CSV | DropboxFormCSVController | `ProcessDropboxCsvJob` |
| Hostaway sync | HostawayController | `SyncHostawayReservationJob` |
| Zoho Sign / HelloSign | Chronologycron, ZohoSignController | `ProcessSignatureRequestJob` |
| Property monthly metrics | PropertyMonthlyMatricsCalculation | `RecalculatePropertyMetricsJob` |
| Alert cron checks | CronJobsController HTTP routes | Scheduled commands → jobs |
| Report generation | ReportController, TotReportController | `GenerateReportJob` |

**Job standards:** small & single-purpose; idempotent where possible; `$tries`/backoff; logged with entity ID / user / correlation ID; `failed()` surfaces to Slack (or equivalent); batch/chain where appropriate.

**When NOT to queue:** Do not queue solely for the sake of using queues. If synchronous execution is better (user needs immediate feedback, transactional integrity, or operation is <100ms), keep it sync and document the rationale in a brief code comment or ADR note.

---

## 10. Package replacement table

Audit and replace/reconfigure all Composer dependencies for Laravel 13 compatibility (Spec Package & Integration Migration). **Phase 1a audit (2026-07-19):** every Spec-listed package has an explicit status and ownership phase. Adjust ownership only with approval. No bulk Composer replacements in Phase 1a.

| Current package | Action for L13 | Status | Ownership |
|-----------------|----------------|--------|-----------|
| `zizaco/entrust` | Replace with **Spatie Permission** + Policies/Gates | **replaced** | Phase 1 (done) |
| `yajra/laravel-datatables-oracle` ~5 | Upgrade to current Yajra DataTables for L13 | deferred | Phase 5 (may start earlier if blocked) |
| `sammyk/laravel-facebook-sdk` | Verify still needed; remove or replace | verify-then-remove/replace | Phase 2.5 |
| `phpmailer/phpmailer` | Migrate to **Laravel Mail** + Mailables / Notifications | deferred | Phase 2.2 |
| `niklasravnsborg/laravel-pdf` + wkhtmltopdf binaries | Evaluate Browsershot, DomPDF, or a maintained PDF package (ADR-005) | deferred (spike) | ADR-005 → Phase 2.3/2.4 + Phase 4 |
| `hellosign/hellosign-php-sdk` | Wrap in HelloSignService; verify Zoho migration status | deferred | Phase 2.2 |
| `nao-pon/flysystem-google-drive` | Upgrade to **Flysystem v3** + Laravel filesystem config | deferred | Phase 2.5 |
| `jeremykenedy/slack-laravel` | Replace with Laravel’s **Slack notification channel** | deferred | Phase 4 (earlier if needed) |
| `vinkla/hashids` | Verify L13 compatibility or replace | verify-then-replace | Phase 2.5 |
| `doctrine/dbal` | Keep if needed for schema introspection; pin compatible version | as-needed | Schema work (when required) |
| `guzzlehttp/guzzle` ~6 | Upgrade to **Guzzle 7+** (L13 already pulls Guzzle 7 via framework; confirm consumer usage at Hostaway port) | deferred | Phase 2.1 |
| `flynsarmy/csv-seeder` | Revisit — likely replace with modern seeders or one-off import commands | deferred | Phase 2.5 |
| `filp/whoops` | Not needed on L13 (framework error handling) | not needed | N/A on L13 |

---

## 11. Definition of Done

### Phase 0 DoD

- [x] `airconcierge13` is a real Laravel 13 app (scaffolded) and boots via **Laravel Sail**
- [x] PHP 8.3+ runtime in Sail containers
- [x] MySQL database **`airconcierge`** exists (empty of business schema; `migrations_fresh` not copied or run yet)
- [x] Redis up; queue worker runs against Redis
- [x] Scheduler process configured for local/dev (Sail)
- [x] **Pest** smoke test passes
- [x] Pint + Larastan **level 5** configured and passing on the greenfield tree
- [x] CI pipeline runs lint, static analysis, and tests
- [x] Horizon **not** required for Phase 0
- [x] ADRs written under `docs/adr/`
- [x] `docs/migration-inventory.md` produced from the reference app
- [x] `docs/` tracked in git (`.gitignore` no longer ignoring the whole docs tree)
- [x] Cursor/project rules encode behavior preservation + layering
- [x] Old app at `/mnt/e/xampp/htdocs/airconcierge` remains untouched
- [x] No production secrets copied into the new repo

### Per-module DoD (Spec Definition of Done — apply to every migrated module)

- [ ] Controller reduced to CRUD routing + service delegation
- [ ] Validation in Form Request(s)
- [ ] Authorization in Policy/Gate (scoped to the smallest appropriate boundary)
- [ ] Workflow business logic in Service(s); simple domain predicates on Model(s) — no query-wrapper services
- [ ] Async work in Job(s) where applicable
- [ ] No regression in existing behavior (manual QA checklist or automated test)
- [ ] Deprecated patterns removed from touched files
- [ ] No duplicate sources of truth for enable/active (ADR-009)
- [ ] Brief migration note added if behavior/routing changed
- [ ] Suspected bugs documented and held for approval — not silently “fixed”

### Final (end of Phase 6) deployment readiness

- [ ] Pint passes
- [ ] Larastan passes
- [ ] All automated tests pass
- [ ] Queue workers configured
- [ ] Scheduler configured
- [ ] Staging deployment completed
- [ ] Manual QA completed before production deployment

---

## 12. Working style rules (Spec Notes for Developer + Additional Guidance)

1. **Incremental PRs by domain** (e.g. “Bookings module L13 migration”), not one monolithic PR.
2. **Preserve cron schedules and webhook URL paths** unless DevOps coordinates a cutover.
3. **Flag Blade-embedded business logic** as follow-up cards; don’t fix inline while migrating something else.
4. When in doubt between sync and async: queue if I/O-bound or >2s, sync if the user is waiting on the result.
5. When asked to work on a specific controller/module, always:
   1. Confirm which controller(s)/files are in scope.
   2. Identify what belongs in Form Request / Policy / Service / Model predicate / Job.
   3. Flag anything that looks like a behavior bug **before** touching it — wait for go-ahead.
   4. Propose the refactored file structure before writing full implementations, unless told to just go ahead.
6. For major architectural decisions, write a brief ADR: Decision / Alternatives considered / Rationale.
7. Prefer Laravel Events, Listeners, Notifications, Jobs, Bus Batching, and Scheduling where they improve separation of concerns. Do not queue work simply for the sake of using queues.
8. Controllers should remain thin orchestration layers. If a controller action grows beyond simple request handling and service coordination, extract the logic into Services, Actions, Jobs, or Events as appropriate. Do not extract a service solely to wrap a model lookup.
9. Prefer long-term maintainability over minimizing the number of changed files.
10. Follow ADR-009 review heuristics: model vs service, no dual active flags, middleware scoped to the smallest owner/staff boundary.

---

## 13. Out of scope (unless separately approved)

From Spec Out of Scope, plus project-specific schema constraints:

- New features or UI redesign
- Database schema changes beyond what Laravel 13 / PHP 8 require (this project uses PHP 8.3+)
- Full rewrite of all ~198 Blade templates in one pass
- Changing third-party API contracts (Hostaway, Zoho, etc.)
- Silently “fixing” questionable business logic during refactors
- Copying or running `database/migrations_fresh` into `airconcierge13` until explicitly approved
- Using the legacy `database/migrations/` (~51 files) as the L13 schema baseline (superseded by `migrations_fresh`)

---

## 14. Specification coverage map

Every major heading/requirement area from `docs/Laravel_5.1_to_13_Modernization_Spec.md` maps as follows:

| Spec section | Plan location |
|--------------|---------------|
| Objective | §1 Overall approach |
| Behavior Preservation | §4 Hard rule: behavior preservation |
| Current State (Baseline) | §5 Confirmed baseline |
| Known fat controllers | §5 Known fat controllers; Phase 2 subsections |
| Architecture Requirements / Mandatory Guidelines | §7 Layering standard |
| Target Layering | §7 |
| Queues & Jobs / High-Priority Candidates | §9; Phase 4 tasks |
| Job Standards | §7 Job standards; §9 |
| When NOT to Queue | §7 Sync vs async; §9 |
| Package & Integration Migration | §10; Phase 1a audit; Phase 2–5 ownership |
| Phase 0 — Foundation | §8 Phase 0 |
| Phase 1 — Routing & Middleware | §8 Phase 1; gaps → Phase 1a |
| Phase 2 — Extract Services | §8 Phase 2 (+ 2.1–2.5 ordered by Recommended Migration Order) |
| Phase 3 — Helpers Decomposition | §8 Phase 3 |
| Phase 4 — Async Migration | §8 Phase 4 |
| Phase 5 — Frontend / Views | §8 Phase 5 |
| Code Quality Rules | §7 Code quality rules |
| Definition of Done | §11 Per-module DoD |
| Out of Scope | §13 |
| Recommended Migration Order | §8 phase diagram + Phase 2.1–2.5 |
| Notes for Developer | §12 |
| Phase 6 — Deployment Readiness | §8 Phase 6; §11 Final DoD |
| Architecture Decisions (ADR) | §8 Phase 0 ADRs; Phase 1a ADR gap check; §12 item 6 |
| Additional Guidance | §1; §7; §12 items 7–9 |

---

## 15. Current on-disk status (as of this document)

| Path | Status |
|------|--------|
| `/home/pc/projects/airconcierge13/` | Laravel 13.19 app root (Sail PHP 8.4 slim runtime under `docker/8.4/`) |
| `/home/pc/projects/airconcierge13/docs/` | Tracked in git (ADRs, inventory, this plan) |
| `/home/pc/projects/airconcierge13/docs/migration-plan.md` | This file — execution roadmap aligned to Spec |
| Laravel 13 scaffold / Sail | **Phase 0 complete** — Sail boots with MySQL `airconcierge`, Redis queue/cache/session, queue worker + scheduler |
| Phase 1 routing & auth | **Scaffolded** — `UserRole` + Policies (ADR-010), session login, owner-scoped middleware + `User` predicates (ADR-009), Schedule command stubs, Hostaway webhook path. See ADR-007 |
| Phase 1a | **Complete** — Hostaway webhook Basic Auth (ADR-008) + Spec package ownership status matrix (§10) |
| Quality | Pest (incl. Phase 1a webhook auth tests), Pint, Larastan level 5, GitHub Actions CI, Laravel Boost |
| Fresh schema (`migrations_fresh`) | Present under `database/migrations_fresh/` as **reference only** — not run via `artisan migrate`; copy specific files into `database/migrations/` per domain phase |
| Legacy `database/migrations/` | ~51 historical files — not the L13 baseline |

---

## 16. Next step

1. Treat `docs/Laravel_5.1_to_13_Modernization_Spec.md` as the requirements source of truth and this plan as the execution roadmap.
2. For Phase 0–1a how-to and current surface area, see [`technical-documentation.md`](technical-documentation.md) and [`user-documentation.md`](user-documentation.md).
3. Phase 0 DoD is met. Phase 1 greenfield routing & auth is scaffolded (ADR-007). Phase 1a exit criteria are met (ADR-008).
4. **Execute Phase 2 next**, starting with **Phase 2.1 Hostaway + webhooks** (service extraction). Do not begin Phase 2 until Phase 1a remains complete.
5. Do **not** copy `database/migrations_fresh/` into live `database/migrations/` until the relevant domain phase needs those tables.
