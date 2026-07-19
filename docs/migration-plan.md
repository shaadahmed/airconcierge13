# Air Concierge — Laravel 5.1 → Laravel 13 Migration Plan

**Status:** Project source of truth (all phases)  
**Last updated:** 2026-07-11  
**New project root:** `/home/pc/projects/airconcierge13`  
**Reference (old) app:** `/mnt/e/xampp/htdocs/airconcierge` (Windows: `E:\xampp\htdocs\airconcierge`)

This document is the **single source of truth** for the entire Laravel 5.1 → Laravel 13 migration. Agents and humans follow it for sequencing, layering, DoD, and out-of-scope rules until it is deliberately revised.

The modernization spec (`docs/Laravel_5.1_to_13_Modernization_Spec.md`) is historical / requirements background. **Where this plan and the spec disagree, this plan wins** (e.g. PHP 8.3+, Spatie Permission, Laravel Sail, domain-ordered phases).

Do **not** begin Phase 1+ until Phase 0 DoD is met and Phase 1 is explicitly approved to start.

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
| PHP | **8.3+** (Laravel 13 minimum; the modernization spec’s 8.2+ is superseded here) |
| Framework | Laravel 13 (latest stable) |
| Database | MySQL 8, database name **`airconcierge`** (aligned with the old app) |
| Schema source | Fresh baseline in the reference app: **`/mnt/e/xampp/htdocs/airconcierge/database/migrations_fresh/`** (~101 files, dated `2026_07_11_*`, ending with `add_foreign_keys_to_fresh_schema`). This replaces the historical ~51 incremental files under `database/migrations/` as the intended L13 schema source. |
| Schema timing | Phase 0 creates the empty MySQL DB only. **Do not copy or run** `migrations_fresh` into `airconcierge13` until explicitly approved (later phase / separate go-ahead). |
| Cache / queue / sessions | Redis |
| Local runtime | **Laravel Sail** (`vendor/bin/sail`) — app + MySQL + Redis; queue worker and scheduler via Sail/Compose services as configured in Phase 0 |
| Production queue/scheduler | Sail/Compose where used; otherwise **supervisor** or **systemd** (as appropriate for the host) |
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
  2. Explain impact.
  3. Propose an improved implementation.
  4. Wait for approval before changing it.
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

---

## 6. Target folder / project layout

```
/home/pc/projects/airconcierge13/          # NEW git root (Laravel 13 + Sail)
├── .gitignore                             # currently ignores docs/ (temporary — un-ignore in Phase 0)
├── docs/                                  # project docs (tracked in git)
│   ├── migration-plan.md                  # this file — source of truth for all phases
│   ├── migration-inventory.md             # legacy inventory / cron command map
│   ├── technical-documentation.md         # developer reference (Phase 0–1+)
│   ├── user-documentation.md              # end-user / QA guide for the current shell
│   ├── Laravel_5.1_to_13_Modernization_Spec.md  # background requirements
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

Enforce this flow throughout:

```
Request
  → Controller          (HTTP in/out only — no business logic)
  → Form Request        (validation)
  → Policy / Gate       (authorization)
  → Service             (business logic, domain-grouped)
  → Model / Repository  (data access; models stay focused on relations, scopes, casts)
  → Job                 (async work where appropriate)
  → Event / Listener    (side effects where appropriate)
```

Repositories are optional — use them when query complexity warrants it; do not invent a repository for every model.

### Mandatory guidelines

- Keep controllers thin.
- Move business logic into domain-grouped Service classes.
- Use Form Requests for all non-trivial validation.
- Use Policies/Gates for authorization (replace Entrust over time).
- Follow **DRY** and **SOLID**.
- Prefer constructor DI over facades in services where practical.
- Replace deprecated helpers/APIs (`Input::`, old route syntax, legacy middleware names).
- Remove obsolete framework patterns from touched files (legacy controller routing strings, legacy auth flows where L13 equivalents exist).
- No new global helpers; no new business logic in Blade.
- Interfaces only when multiple implementations are genuinely expected.
- Every refactored critical path gets at least one feature/integration test.

### Code quality rules (PR review)

- No new controller methods >50 lines without justification.
- No direct `DB::` in controllers — use models or query objects in services.
- No `Mail::send()` closures or raw PHPMailer in controllers.
- No `env()` outside config files.
- No new `0000-00-00` date handling — use nullable dates / Carbon.

### Sync vs async

- Queue if I/O-bound or expected runtime > ~2s.
- Keep sync when the user needs immediate feedback, transactional integrity requires it, or the operation is trivial (<~100ms) — document the rationale briefly.
- Do not queue for the sake of queuing.

---

## 8. Full phase breakdown

Phases below follow the **recommended domain migration order**. Structural work from the modernization spec (service extraction, helpers decomposition, async jobs, Blade touch-ups, deployment readiness) is folded into these phases where it belongs.

```text
Phase 0 Foundation
    → Phase 1 Routing & Auth
    → Phase 2 Hostaway + webhooks
    → Phase 3 Email / Chronology
    → Phase 4 Bookings & Payments
    → Phase 5 Reports & Dashboard
    → Phase 6 Remaining admin + helpers + deployment readiness
```

---

### Phase 0 — Foundation (no business port yet)

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
| Project rules | Encode behavior-preservation + layering in `.cursor/rules` or `AGENTS.md` |
| Docs in git | Un-ignore `docs/` so this plan, ADRs, and inventory are tracked |
| ADRs | See list below |
| Inventory | `docs/migration-inventory.md` — controllers, cron HTTP routes, packages, helpers — checklist only, no ports |

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

Phase 1 is a **rewrite scaffold**, not a dump of the L5.1 `routes.php`. Legacy app is a **business-behavior oracle only** — no Entrust classes, helpers, or technical patterns are ported.

- Scaffold `routes/web.php`, `routes/api.php`, `routes/console.php` with class-based routes; add domain routes as modules land in later phases.
- Install **Spatie Permission** + Policies/Gates; seed business role names (superadmin, admin, Regional Manager, Property Owner, cleaner, maintenance).
- Session auth shell (login/logout). Owner terms / active-property access as **new** middleware/Gates with stubbed domain checks until those modules exist — do **not** port `OwnerAccessVerifier`.
- **No public `/cron/*` HTTP routes.** Artisan command stubs + Laravel Schedule only (frequencies aligned to known business schedules).
- Hostaway webhook path preserved (`POST /wh/hostaway/booking/created`): return 200 fast, dispatch job stub; **signature verification deferred to Phase 2** (explicit TODO).
- Schema: Spatie + default Laravel `users` only — do **not** copy `migrations_fresh` into live migrations in this phase.

---

### Phase 2 — Hostaway + webhooks

- Extend/port `HostawayService`; move logic out of `HostawayController`.
- Implement `SyncHostawayReservationJob` (webhook returns fast).
- High integration risk — prioritize tests around webhook ingest and sync idempotency.
- Do not change Hostaway API contracts.

---

### Phase 3 — Email / Chronology (largest async win)

- `ChronologyService` from Chronology / Chronologycron controllers.
- `EmailService` — centralize outbound mail; migrate PHPMailer → Mailables / Notifications.
- Jobs: `SendOutboundEmailJob`, `SendNotificationJob`, signature-related jobs (`ProcessSignatureRequestJob`) as applicable.
- `ZohoSignService` / `HelloSignService` — wrap SDKs; verify Zoho vs HelloSign migration status before deep investment.
- Replace `jeremykenedy/slack-laravel` with Laravel’s Slack notification channel for failure/monitoring signals.

---

### Phase 4 — Bookings & Payments (core business)

- `BookingService` from `BookingController`, `CreateBookingController`.
- `PaymentService` from `PaymentController`, `PropertyPaymentsController`.
- Form Requests + Policies for all non-trivial endpoints.
- Thin controllers; feature tests on critical booking/payment paths.
- Queue PDF-related payment/booking artifacts where appropriate (`GeneratePdfJob`).

**Known fat controllers to prioritize in this phase:** BookingController, CreateBookingController, PaymentController.

---

### Phase 5 — Reports & Dashboard

- `ReportService` from `ReportController`, `TotReportController`.
- `DashboardService` from `AjaxDashboardController` / Dashboard.
- `GenerateReportJob` / `GeneratePdfJob` for heavy report/PDF work.
- Read-heavy; can follow core booking/payment stability.
- Verify DataTables JS compatibility when Yajra is upgraded (may overlap Phase 6 if reports depend on it earlier).

**Known fat controllers:** AjaxDashboardController (~4,800 lines), ReportController.

---

### Phase 6 — Remaining admin, helpers, async leftovers, deployment readiness

**Remaining domain services (as touched):**

- **`PropertyController` (~1,580 lines)** — extract property domain service(s) as this module is touched; known fat-controller target
- `DocumentService` — UploadDocument / Documentlist
- `ImportService` — ImportData / ImportedEmails; jobs like `ProcessImportedEmailJob`, Dropbox CSV job
- Property metrics: `RecalculatePropertyMetricsJob` (batched)
- Cloud backup: `UploadBackupToCloudJob`
- Image compression: keep command; dispatch per-batch jobs
- Alert cron checks: migrate fully to scheduled commands dispatching jobs (`CronJobsController` HTTP routes)

**Helpers decomposition:**

- Audit all ~100 functions in `app/helpers.php`.
- Move to appropriate homes: date/formatting → Value Objects or `Support\DateFormatter`; business rules → Services; view-only formatting → View Composers / Blade components.
- Goal: eliminate the `helpers.php` Composer autoload entry.

**Frontend / views (incremental only):**

- Touch ~198 Blade templates only as needed for migrated modules.
- Replace inline PHP/logic in views with View Models or components **where touched**.
- No full Blade rewrite in one pass.

**Package finish-line:**

- Yajra DataTables → current L13-compatible release.
- Facebook SDK — verify still needed; remove or replace.
- Hashids — verify L13 compatibility or replace.
- Guzzle 7+, Flysystem v3 Google Drive adapter, doctrine/dbal pin if still needed.

**Deployment readiness:**

- Pint passes; Larastan passes; automated tests pass.
- Queue workers + scheduler configured (Sail/Compose locally; supervisor/systemd in production as appropriate).
- Staging deploy + manual QA before production.
- Failed-job monitoring (e.g. Slack on `failed()`).

---

## 9. Job candidates (cross-phase reference)

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

---

## 10. Package replacement table

| Current package | Action for L13 |
|-----------------|----------------|
| `zizaco/entrust` | Replace with **Spatie Permission** + Policies/Gates |
| `yajra/laravel-datatables-oracle` ~5 | Upgrade to current Yajra DataTables for L13 |
| `sammyk/laravel-facebook-sdk` | Verify still needed; remove or replace |
| `phpmailer/phpmailer` | Migrate to **Laravel Mail** + Mailables / Notifications |
| `niklasravnsborg/laravel-pdf` + wkhtmltopdf binaries | Evaluate Browsershot, DomPDF, or a maintained PDF package (ADR) |
| `hellosign/hellosign-php-sdk` | Wrap in HelloSignService; verify Zoho migration status |
| `nao-pon/flysystem-google-drive` | Upgrade to **Flysystem v3** + Laravel filesystem config |
| `jeremykenedy/slack-laravel` | Replace with Laravel’s **Slack notification channel** |
| `vinkla/hashids` | Verify L13 compatibility or replace |
| `doctrine/dbal` | Keep if needed for schema introspection; pin compatible version |
| `guzzlehttp/guzzle` ~6 | Upgrade to **Guzzle 7+** |
| `flynsarmy/csv-seeder` | Revisit — likely replace with modern seeders or one-off import commands |
| `filp/whoops` | Not needed on L13 (framework error handling) |

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

### Per-module DoD (Phases 1–6)

- [ ] Controllers reduced to HTTP routing + service delegation
- [ ] Validation in Form Request(s)
- [ ] Authorization in Policy/Gate
- [ ] Business logic in Service(s)
- [ ] Async work in Job(s) where applicable (with rationale if kept sync)
- [ ] No regression in existing behavior (automated test and/or manual QA checklist)
- [ ] Deprecated patterns removed from touched files
- [ ] Brief migration note if behavior or routing changed
- [ ] Suspected bugs documented and held for approval — not silently “fixed”

### Final (end of Phase 6) deployment readiness

- [ ] Pint passes
- [ ] Larastan passes
- [ ] Automated tests pass
- [ ] Queue workers configured
- [ ] Scheduler configured
- [ ] Staging deployment completed
- [ ] Manual QA completed before production

---

## 12. Working style rules

1. **Incremental PRs by domain** (e.g. “Bookings module L13 migration”), not one monolithic PR.
2. **Preserve cron schedules and webhook URL paths** unless DevOps coordinates a cutover.
3. **Flag Blade-embedded business logic** as follow-up cards; don’t fix inline while migrating something else.
4. When asked to work on a specific controller/module, always:
   1. Confirm which controller(s)/files are in scope.
   2. Identify what belongs in Form Request / Policy / Service / Job.
   3. Flag anything that looks like a behavior bug **before** touching it — wait for go-ahead.
   4. Propose the refactored file structure before writing full implementations, unless told to just go ahead.
5. For major architectural decisions, write a brief ADR: Decision / Alternatives considered / Rationale.
6. Prefer long-term maintainability over minimizing file churn.

---

## 13. Out of scope (unless separately approved)

- New features or UI redesign
- Database schema changes beyond what Laravel 13 / PHP 8.3 require
- Full rewrite of all ~198 Blade templates in one pass
- Changing third-party API contracts (Hostaway, Zoho, etc.)
- Silently “fixing” questionable business logic during refactors
- Copying or running `database/migrations_fresh` into `airconcierge13` until explicitly approved
- Using the legacy `database/migrations/` (~51 files) as the L13 schema baseline (superseded by `migrations_fresh`)

---

## 14. Current on-disk status (as of this document)

| Path | Status |
|------|--------|
| `/home/pc/projects/airconcierge13/` | Laravel 13.19 app root (Sail PHP 8.4 slim runtime under `docker/8.4/`) |
| `/home/pc/projects/airconcierge13/docs/` | Tracked in git (ADRs, inventory, this plan) |
| `/home/pc/projects/airconcierge13/docs/migration-plan.md` | This file — project source of truth |
| Laravel 13 scaffold / Sail | **Phase 0 complete** — Sail boots with MySQL `airconcierge`, Redis queue/cache/session, queue worker + scheduler |
| Phase 1 routing & auth | **Scaffolded** — Spatie Permission, session login, owner middleware stubs, Schedule command stubs, Hostaway webhook stub (signature TODO → Phase 2). See ADR-007 |
| Quality | Pest (incl. Phase 1 auth/role/webhook tests), Pint, Larastan level 5, GitHub Actions CI, Laravel Boost |
| Fresh schema (`migrations_fresh`) | Present under `database/migrations_fresh/` as **reference only** — not run via `artisan migrate`; copy specific files into `database/migrations/` per domain phase |
| Legacy `database/migrations/` | ~51 historical files — not the L13 baseline |

---

## 15. Next step

1. Treat this document as the guide for all subsequent work. For Phase 0–1 how-to and current surface area, see [`technical-documentation.md`](technical-documentation.md) and [`user-documentation.md`](user-documentation.md).
2. Phase 0 DoD is met. Phase 1 is **greenfield** routing & auth (ADR-007) — not a legacy route/Entrust port.
3. Do **not** copy `database/migrations_fresh/` into live `database/migrations/` until the relevant domain phase needs those tables.
