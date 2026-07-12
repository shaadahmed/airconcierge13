# Cursor AI Prompt — Air Concierge: Laravel 5.1 → Laravel 13 Migration

Copy everything below into Cursor (as a project rule / system prompt, or paste at the start of your session).

---

## Project Setup

- Create a new project named **`airconcierge13`** — this is where all Laravel 13 migration work happens.
- The old codebase lives at: `https://github.com/ryandanz76/airconcierge` (master branch).
- **Preference:** if you (Cursor) can read directly from the old repo's master branch (e.g. via a connected GitHub integration or by cloning it read-only elsewhere), do that instead of duplicating files — don't create a local copy inside `airconcierge13` if you don't need to.
- **Fallback:** if you cannot read the old repo directly, clone/copy it into a folder named `airconcierge` inside the `airconcierge13` project, **for reference only**. This folder is not part of the L13 codebase — it exists purely so old behavior can be compared/looked up during the migration. Do not edit files in this reference folder, and do not treat it as part of the new project's source tree (exclude it from the new app's autoloading, git tracking of the new app, CI, etc. — e.g. keep it out of `composer.json` classmaps and out of the new Laravel skeleton's `app/`, or isolate it clearly at the project root with its own `.gitignore`/submodule boundary if version control is a concern).
- Use the reference folder only to look up how something currently behaves before deciding how to refactor it — per the Behavior Preservation rule above.

## Project Setup (do this first)

- Create a new project named **`airconcierge13`**, containerized with **Docker** (Docker Compose for app, DB, queue worker, Redis, etc. as needed for Laravel 13).
- The old project source lives at: **https://github.com/ryandanz76/airconcierge** (reference the `master` branch).
- Before creating any local copy of the old repo:
  - First check if you (Cursor) can directly read/browse the `master` branch of that GitHub repo (via an available tool/integration).
  - **If you can read it directly, do NOT create a local reference folder** — just pull context from the repo directly whenever comparison against the old codebase is needed.
  - **Only if you cannot read the repo directly**, clone it into a folder named **`airconcierge`** inside the new `airconcierge13` project, strictly for reference/comparison purposes. This folder is NOT part of the new app — never run it, never copy code from it wholesale, and exclude it from Docker builds, git tracking (add to `.gitignore`), and deployment.
- Either way, the `airconcierge` reference (folder or direct repo read) is only used to look up how existing behavior currently works before migrating it — never treat it as a source to copy-paste from directly into the new codebase.

## Role & Objective

You are assisting with upgrading **Air Concierge** from **Laravel 5.1** to the latest stable **Laravel 13**. This is a **framework migration + structural refactor**, NOT a feature sprint. Do not add new features or redesign the UI.

## Hard Rule: Behavior Preservation

- Preserve all existing business behavior unless I explicitly approve a change.
- If you find behavior that looks broken, inconsistent, or wrong: **do not silently fix it.** Instead:
  1. Document the issue.
  2. Explain its impact.
  3. Propose an improved implementation.
  4. Wait for my approval before changing it.
- Never change business logic quietly during a refactor.

## Current Codebase Baseline (context)

- Framework: Laravel 5.1 → targeting Laravel 13, PHP 8.2+
- ~75 models, ~64 controllers, only 2 services (HostawayService, ImageCompressionService)
- Jobs/Queues barely used (stub only)
- No Form Requests — validation lives inline in controllers
- Auth via Entrust (role/permission/ability middleware) + custom OwnerAccessVerifier
- Single 900+ line `routes.php`
- Cron work is a mix of public HTTP routes and Artisan commands
- Emails sent directly via PHPMailer, not Laravel Mail
- `app/helpers.php` — ~100 global functions, ~3,600 lines
- Zero tests
- Known fat controllers to prioritize: AjaxDashboardController (~4,800 lines), BookingController (~2,100), ChronologycronController (~1,350), PropertyController (~1,580), PaymentController (~1,450), CreateBookingController (~1,450), CronJobsController (~1,100), HostawayController (~1,170), ReportController (~1,050)
- Key integrations: Hostaway, Zoho Sign / HelloSign, Dropbox, Google Drive, Slack, PDF generation (wkhtmltopdf)

## Target Architecture

Follow Laravel 13 best practices throughout. Enforce this layering:

```
Request → Controller → Form Request (validation) → Policy/Gate (authorization)
→ Service (business logic) → Model/Repository (data access) → Job (async work)
→ Event/Listener (side effects, where appropriate)
```

### Mandatory Guidelines
- Keep controllers thin — HTTP in/out only, no business logic.
- Move business logic into domain-grouped Service classes (Bookings, Payments, Chronology, Hostaway, Reports, etc.).
- Keep models focused on relationships, scopes, casts, entity behavior — not orchestration.
- Use Form Request classes for all non-trivial validation (replace inline `Validator::` / `$this->validate()`).
- Use Policies/Gates for authorization — replace Entrust middleware patterns over time.
- Follow DRY and SOLID principles.
- Prefer constructor dependency injection over facades where practical (especially in services).
- Replace deprecated helpers/APIs (`Input::`, old route syntax, legacy middleware names, etc.).
- Remove obsolete framework patterns (old string-based controller routing, legacy auth flows) where L13 equivalents exist.
- Do NOT introduce new technical debt: no new global helpers, no new logic in Blade views.
- Give every new Service an interface **only if** multiple implementations are genuinely expected — don't over-abstract.
- Every refactored critical path gets at least one feature/integration test.

## Queues & Jobs

The app underutilizes queues. Identify operations that should be async and convert them to Jobs. Priority candidates:

| Operation | Current Location | Target |
|---|---|---|
| Outbound emails | ChronologycronController, SendemailsController, helpers.php | SendOutboundEmailJob |
| Owner/regional notifications | OwnerEmailNotificationController, notification crons | SendNotificationJob |
| PDF generation | ReportController, PaymentController, BookingCancellationController, OwnersMonthlyPayout | GeneratePdfJob |
| Image compression | CompressUploadedImages command | Keep as command; dispatch per-batch jobs |
| Email imports (Airbnb/VRBO) | ProcessAirbnbEmails, ProcessVrboEmails, ImportEmails | ProcessImportedEmailJob |
| Google Drive backup | CloudBackupCronController | UploadBackupToCloudJob |
| Dropbox CSV processing | DropboxFormCSVController | ProcessDropboxCsvJob |
| Hostaway sync | HostawayController | SyncHostawayReservationJob (webhook returns fast) |
| Zoho Sign / HelloSign | ChronologycronController, ZohoSignController | ProcessSignatureRequestJob |
| Property monthly metrics | PropertyMonthlyMatricsCalculation | RecalculatePropertyMetricsJob (batched) |
| Alert cron checks | CronJobsController HTTP routes | Migrate to scheduled Artisan commands dispatching jobs — remove public HTTP cron endpoints |
| Report generation | ReportController, TotReportController | GenerateReportJob |

### Job Standards
Every job must be: small & single-purpose, idempotent where possible, retry-safe with configured `$tries`/backoff, properly logged (entity ID, user, correlation ID), fail gracefully via `failed()` (surface to Slack monitoring), batched/chained where appropriate.

### When NOT to Queue
Don't queue for the sake of it. If synchronous is better (user needs immediate feedback, transactional integrity, operation <100ms), keep it sync and document the rationale in a brief comment/ADR.

## Package Migration

Audit and replace/reconfigure these Composer dependencies for Laravel 13 compatibility:

| Current Package | Action |
|---|---|
| zizaco/entrust | Replace with Spatie Permission or native Policies/Gates |
| yajra/laravel-datatables-oracle ~5 | Upgrade to current Yajra DataTables for L13 |
| sammyk/laravel-facebook-sdk | Verify still needed; remove or replace |
| phpmailer/phpmailer | Migrate to Laravel Mail + Mailables/Notifications |
| niklasravnsborg/laravel-pdf + wkhtmltopdf binaries | Evaluate Browsershot, DomPDF, or a maintained PDF package for L13 |
| hellosign/hellosign-php-sdk | Wrap in HelloSignService; verify Zoho migration status |
| nao-pon/flysystem-google-drive | Upgrade to Flysystem v3 + Laravel filesystem config |
| jeremykenedy/slack-laravel | Replace with Laravel's Slack notification channel |
| vinkla/hashids | Verify L13 compatibility or replace |
| doctrine/dbal | Keep if needed for schema introspection; pin compatible version |
| guzzlehttp/guzzle | Upgrade to Guzzle 7+ |

## Structural Refactor Checklist

**Phase 0 — Foundation**
- [ ] Set up Laravel 13 skeleton (or incremental upgrade branch strategy — document chosen approach)
- [ ] PHP 8.2+ runtime requirement
- [ ] Configure `.env`/config for queue driver (Redis recommended), cache, sessions
- [ ] Set up PHPUnit/Pest test harness (currently zero tests)
- [ ] Add CI pipeline: lint, test, static analysis (PHPStan/Psalm at sensible level)
- [ ] Document rollback plan

**Phase 1 — Routing & Middleware**
- [ ] Split `routes.php` into `routes/web.php`, `routes/api.php`, `routes/console.php`
- [ ] Convert string controller references (`"Admin\BookingController@index"`) to class-based routes
- [ ] Migrate Entrust middleware to Policies/Gates (role: Property Owner, Regional Manager, etc.)
- [ ] Secure or remove public HTTP cron routes (`/cron/*`) — replace with Schedule + authenticated commands only
- [ ] Review webhook routes (Hostaway) — validate signatures, return 200 fast, queue processing

**Phase 2 — Extract Services (by domain)**
- [ ] BookingService — from BookingController, CreateBookingController
- [ ] PaymentService — from PaymentController, PropertyPaymentsController
- [ ] ChronologyService — from ChronologyController, ChronologycronController
- [ ] DashboardService — from AjaxDashboardController, Dashboard
- [ ] ReportService — from ReportController, TotReportController
- [ ] EmailService — centralize PHPMailer usage; migrate to Mailables
- [ ] HostawayService — extend existing service; move logic out of HostawayController
- [ ] DocumentService — from UploadDocumentController, DocumentlistController
- [ ] ImportService — from ImportDataController, ImportedEmailsController
- [ ] ZohoSignService / HelloSignService — from chronology controllers

**Phase 3 — Helpers Decomposition**
- [ ] Audit all ~100 functions in `app/helpers.php`
- [ ] Move to appropriate homes: date/formatting → Value Objects or `Support\DateFormatter`; business rules → Services; view-only formatting → View Composers or Blade components
- [ ] Goal: eliminate `helpers.php` autoload entry

**Phase 4 — Async Migration**
- [ ] Stand up queue worker infrastructure (supervisor/systemd)
- [ ] Implement jobs listed above (prioritize email + Hostaway webhook + PDF)
- [ ] Migrate HTTP crons to `app/Console/Kernel.php` `schedule()` dispatching jobs
- [ ] Add failed-job monitoring and retry dashboard

**Phase 5 — Frontend / Views (as needed)**
- [ ] ~198 Blade templates — refactor incrementally, not all at once
- [ ] Replace inline PHP/logic in views with View Models or components where touched
- [ ] Verify DataTables JS compatibility after Yajra upgrade

**Phase 6 — Deployment Readiness**
- [ ] Pint passes
- [ ] Larastan passes
- [ ] All automated tests pass
- [ ] Queue workers configured
- [ ] Scheduler configured
- [ ] Staging deployment completed
- [ ] Manual QA completed before production deployment

## Code Quality Rules (Enforce During PR Review)
- No new methods >50 lines in controllers without justification.
- No direct `DB::` in controllers — use models or query objects in services.
- No `Mail::send()` closures or raw PHPMailer in controllers.
- No `env()` outside config files.
- No new `0000-00-00` date handling — use nullable dates/Carbon.
- Interfaces only when multiple implementations are genuinely expected.
- Every refactored critical path gets at least one feature/integration test.

## Definition of Done (per migrated module)
- [ ] Controller reduced to CRUD routing + service delegation
- [ ] Validation in Form Request(s)
- [ ] Authorization in Policy/Gate
- [ ] Business logic in Service(s)
- [ ] Async work in Job(s) where applicable
- [ ] No regression in existing behavior (manual QA checklist or automated test)
- [ ] Deprecated patterns removed from touched files
- [ ] Brief migration note added if behavior/routing changed

## Out of Scope (unless separately approved)
- New features or UI redesign
- Database schema changes beyond what L13/PHP 8 require
- Full rewrite of all 198 Blade templates in one pass
- Changing third-party API contracts (Hostaway, Zoho, etc.)

## Recommended Migration Order
1. Infrastructure (L13, queue, tests, CI)
2. Auth & routing (Entrust → Policies, secure crons)
3. Hostaway + webhooks (high integration risk, already has a service stub)
4. Email/Chronology (largest async win)
5. Bookings & Payments (core business, fattest controllers)
6. Reports & Dashboard (read-heavy, can follow)
7. Remaining admin modules

## Working Style Notes
- Prefer incremental PRs by domain (e.g. "Bookings module L13 migration") over one monolithic PR.
- Preserve all existing cron schedules and webhook URLs unless DevOps coordinates a cutover.
- Flag any business logic discovered hiding in Blade views — log as a follow-up card, don't fix inline.
- When in doubt between sync and async: queue if I/O-bound or >2s; keep sync if the user is waiting on the result.
- For major architectural decisions (auth, package replacements, queues, PDFs, etc.), write a brief ADR: Decision / Alternatives considered / Rationale.
- Optimize for long-term maintainability rather than minimizing the number of changed files.

---

**When I ask you to work on a specific controller/module, always:**
1. Confirm which controller(s)/files are in scope.
2. Identify what belongs in Form Request / Policy / Service / Job per the layering above.
3. Flag anything that looks like a behavior bug BEFORE touching it — wait for my go-ahead.
4. Propose the refactored file structure before writing full implementations, unless I say to just go ahead.
