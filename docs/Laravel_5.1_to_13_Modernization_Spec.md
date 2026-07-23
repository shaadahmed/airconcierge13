Laravel Upgrade --- Architecture & Refactor Requirements

Objective

Upgrade Air Concierge from Laravel 5.1 to the latest stable Laravel
release (currently Laravel 13), modernizing architecture without
changing business behavior unless explicitly approved. Treat this as a
framework migration + structural refactor, not a feature sprint.

Behavior Preservation

Unless explicitly approved, preserve existing business behavior.

When existing behavior appears incorrect or inconsistent: - Document
it. - Explain the impact. - Propose an improved implementation. - Wait
for approval before intentionally changing behavior.

Do not silently change business logic during refactoring.

Current State (Baseline)

  -----------------------------------------------------------------------
  Area                         Current
  ---------------------------- ------------------------------------------
  Framework                    Laravel 5.1

  Models                       \~75

  Controllers                  \~64

  Services                     2 (HostawayService,
                               ImageCompressionService)

  Jobs / Queues                Effectively unused (stub only; jobs table
                               exists)

  Form Requests                Base Request only --- validation lives in
                               controllers

  Authorization                Entrust (role, permission, ability
                               middleware) + custom OwnerAccessVerifier

  Routes                       Single app/Http/routes.php (\~900+ lines)

  Cron / background work       Mix of HTTP cron routes
                               (Api\\CronJobsController,
                               ChronologycronController) and Artisan
                               commands (app/Console/Commands/\*)

  Email                        Direct PHPMailer usage in
                               controllers/helpers --- not Laravel Mail

  Helpers                      app/helpers.php --- \~100 global
                               functions, \~3,600 lines

  Tests                        None present

  Key integrations             Hostaway, Zoho Sign / HelloSign, Dropbox,
                               Google Drive, Slack, PDF (wkhtmltopdf)
  -----------------------------------------------------------------------

Known fat controllers (priority refactor targets):

AjaxDashboardController (\~4,800 lines)

BookingController (\~2,100 lines)

ChronologycronController (\~1,350 lines)

PropertyController (\~1,580 lines)

PaymentController (\~1,450 lines)

CreateBookingController (\~1,450 lines)

CronJobsController (\~1,100 lines)

HostawayController (\~1,170 lines)

ReportController (\~1,050 lines)

Architecture Requirements

Follow Laravel 13 best practices throughout.

Mandatory Guidelines

Keep controllers thin --- HTTP in/out only; no business logic.

Move business logic into dedicated Service classes (domain-grouped:
Bookings, Payments, Chronology, Hostaway, Reports, etc.).

Keep models focused on relationships, scopes, casts, and entity behavior
--- not orchestration.

Use Form Request classes for all non-trivial validation (replace inline
Validator:: / \$this-\>validate() in controllers).

Use Policies / Gates for authorization --- replace Entrust middleware
patterns over time.

Follow DRY and SOLID principles.

Prefer constructor dependency injection over facades where practical
(especially in services).

Replace deprecated helpers and APIs (Input::, old route syntax, legacy
middleware names, etc.).

Remove obsolete framework patterns (e.g. old controller routing strings,
legacy auth flows where Laravel 13 equivalents exist).

Do not introduce new technical debt --- no new global helpers; no new
logic in Blade views.

Target Layering

Request → Controller → Form Request (validation) → Policy/Gate
(authorization) → Service (business logic) → Model / Repository (data
access) → Job (async work) → Event/Listener (side effects, where
appropriate)

Queues & Jobs

The application underutilizes Laravel's queue system. During migration,
identify operations that should run asynchronously and refactor them to
queued Jobs.

High-Priority Candidates (from current codebase)

  ------------------------------------------------------------------------------------
  Operation        Current Location                    Recommended Approach
  ---------------- ----------------------------------- -------------------------------
  Outbound emails  ChronologycronController,           SendOutboundEmailJob
  / chronology     SendemailsController, helpers.php   
  mail                                                 

  Owner/regional   OwnerEmailNotificationController,   SendNotificationJob
  notifications    cron controllers                    

  PDF generation   ReportController,                   GeneratePdfJob
                   PaymentController,                  
                   BookingCancellationController,      
                   OwnersMonthlyPayout                 

  Image            CompressUploadedImages command      Keep as command; dispatch
  compression                                          per-batch jobs

  Email imports    ProcessAirbnbEmails,                ProcessImportedEmailJob
  (Airbnb/VRBO)    ProcessVrboEmails, ImportEmails     

  Google Drive     CloudBackupCronController           UploadBackupToCloudJob
  backup                                               

  Dropbox CSV      DropboxFormCSVController            ProcessDropboxCsvJob
  processing                                           

  Hostaway sync /  HostawayController                  SyncHostawayReservationJob
  webhooks                                             (webhook returns fast)

  Zoho Sign /      ChronologycronController,           ProcessSignatureRequestJob
  HelloSign flows  ZohoSignController                  

  Property monthly PropertyMonthlyMatricsCalculation   RecalculatePropertyMetricsJob
  metrics                                              (batched)

  Alert cron       CronJobsController HTTP routes      Migrate to scheduled Artisan
  checks                                               commands dispatching jobs ---
                                                       remove public HTTP cron
                                                       endpoints

  Report           ReportController,                   GenerateReportJob
  generation       TotReportController                 
  ------------------------------------------------------------------------------------

Job Standards

Each job must be:

Small and single-purpose

Idempotent where possible (safe to retry)

Retry-safe with configured \$tries / backoff

Properly logged (structured context: entity ID, user, correlation ID)

Fail gracefully --- use failed() handler; surface to monitoring (Slack
already in stack)

Batched or chained where appropriate (e.g. bulk email sends, metric
recalculations)

When NOT to Queue

Do not queue solely for the sake of using queues. If synchronous
execution is better (user needs immediate feedback, transactional
integrity, or operation is \<100ms), keep it sync and document the
rationale in a brief code comment or ADR note.

Package & Integration Migration

Audit and replace/reconfigure all Composer dependencies for Laravel 13
compatibility:

  --------------------------------------------------------------------------
  Current Package                                  Action
  ------------------------------------------------ -------------------------
  zizaco/entrust                                   Replace with Spatie
                                                   Permission or native
                                                   Policies/Gates

  yajra/laravel-datatables-oracle \~5              Upgrade to current Yajra
                                                   DataTables for L13

  sammyk/laravel-facebook-sdk                      Verify still needed;
                                                   remove or replace

  phpmailer/phpmailer                              Migrate to Laravel Mail +
                                                   Mailables/Notifications

  niklasravnsborg/laravel-pdf + wkhtmltopdf        Evaluate Browsershot,
  binaries                                         DomPDF, or maintained PDF
                                                   package for L13

  hellosign/hellosign-php-sdk                      Wrap in HelloSignService;
                                                   verify Zoho migration
                                                   status

  nao-pon/flysystem-google-drive                   Upgrade to Flysystem v3 +
                                                   Laravel filesystem config

  jeremykenedy/slack-laravel                       Replace with Laravel
                                                   Slack notification
                                                   channel

  vinkla/hashids                                   Verify L13 compatibility
                                                   or replace

  doctrine/dbal                                    Keep if needed for schema
                                                   introspection; pin
                                                   compatible version

  guzzlehttp/guzzle                                Upgrade to Guzzle 7+
  --------------------------------------------------------------------------

Structural Refactor Checklist

Phase 0 --- Foundation (do first)

\[ \] Set up Laravel 13 skeleton (or incremental upgrade branch strategy
--- document chosen approach)

\[ \] PHP 8.2+ runtime requirement

\[ \] Configure .env / config for queue driver (Redis recommended),
cache, sessions

\[ \] Set up PHPUnit/Pest test harness (currently zero tests)

\[ \] Add CI pipeline: lint, test, static analysis (PHPStan/Psalm at
sensible level)

\[ \] Document rollback plan

Phase 1 --- Routing & Middleware

\[ \] Split routes.php into routes/web.php, routes/api.php,
routes/console.php

\[ \] Convert string controller references
("Admin\\BookingController@index") to class-based routes

\[ \] Migrate Entrust middleware to Policies/Gates (role: Property
Owner, Regional Manager, etc.)

\[ \] Secure or remove public HTTP cron routes (/cron/\*) --- replace
with Schedule + authenticated commands only

\[ \] Review webhook routes (Hostaway) --- validate signatures, return
200 fast, queue processing

Phase 2 --- Extract Services (by domain)

\[ \] BookingService --- from BookingController, CreateBookingController

\[ \] PaymentService --- from PaymentController,
PropertyPaymentsController

\[ \] ChronologyService --- from ChronologyController,
ChronologycronController

\[ \] DashboardService --- from AjaxDashboardController, Dashboard

\[ \] ReportService --- from ReportController, TotReportController

\[ \] EmailService --- centralize PHPMailer usage; migrate to Mailables

\[ \] HostawayService --- extend existing service; move logic out of
HostawayController

\[ \] DocumentService --- from UploadDocumentController,
DocumentlistController

\[ \] ImportService --- from ImportDataController,
ImportedEmailsController

\[ \] ZohoSignService / HelloSignService --- from chronology controllers

Phase 3 --- Helpers Decomposition

\[x\] Audit all \~181 functions in legacy app/helpers.php (disposition in
docs/migration-inventory.md; Spec “\~100” was shorthand)

\[x\] Move to appropriate homes (ADR-015; predicates → Models per ADR-009):

Date/formatting → App\\Support\\Date\\DateFormatter + DateMath

Money/string → App\\Support\\Money\\MoneyFormatter + String\\StringCleaner

Business workflows → existing Phase 2 Services; fee math deferred

View-only / list\_\* HTML → deferred to Phase 5 View Composers or Blade

\[x\] Goal: eliminate helpers.php autoload entry — confirmed absent on L13

Phase 4 --- Async Migration

\[ \] Stand up queue worker infrastructure (supervisor/systemd)

\[ \] Implement jobs listed in Queues section (prioritize email +
Hostaway webhook + PDF)

\[ \] Migrate HTTP crons to app/Console/Kernel.php schedule()
dispatching jobs

\[ \] Add failed-job monitoring and retry dashboard

Phase 5 --- Frontend / Views (as needed)

\[ \] \~198 Blade templates --- refactor incrementally, not all at once

\[ \] Replace inline PHP/logic in views with View Models or components
where touched

\[ \] DataTables JS integration --- verify compatibility after Yajra
upgrade

Code Quality Rules (Enforce During PR Review)

No new methods \>50 lines in controllers without justification.

No direct DB:: in controllers --- use models or query objects in
services.

No Mail::send() closures or raw PHPMailer in controllers.

No env() outside config files.

No new 0000-00-00 date handling --- use nullable dates / Carbon.

Every new Service gets an interface only if multiple implementations are
expected (don't over-abstract).

Every refactored critical path gets at least one feature/integration
test.

Definition of Done (per module migrated)

\[ \] Controller reduced to CRUD routing + service delegation

\[ \] Validation in Form Request(s)

\[ \] Authorization in Policy/Gate

\[ \] Business logic in Service(s)

\[ \] Async work in Job(s) where applicable

\[ \] No regression in existing behavior (manual QA checklist or
automated test)

\[ \] Deprecated patterns removed from touched files

\[ \] Brief migration note added if behavior/routing changed

Out of Scope (unless separately approved)

New features or UI redesign

Database schema changes beyond what L13/PHP 8 require

Full rewrite of all 198 Blade templates in one pass

Changing third-party API contracts (Hostaway, Zoho, etc.)

Recommended Migration Order

Infrastructure (L13, queue, tests, CI)

Auth & routing (Entrust → Policies, secure crons)

Hostaway + webhooks (high integration risk, already has a service stub)

Email/Chronology (largest async win)

Bookings & Payments (core business, fattest controllers)

Reports & Dashboard (read-heavy, can follow)

Remaining admin modules

Notes for Developer

Prefer incremental PRs by domain (e.g. "Bookings module L13 migration")
over a single monolithic PR.

Preserve all existing cron schedules and webhook URLs unless DevOps is
coordinated on cutover.

Flag any business logic discovered in Blade views during migration ---
log as follow-up card.

When in doubt between sync and async: queue if I/O-bound or \>2s, sync
if user is waiting on the result.

Phase 6 - Deployment Readiness

\[ \] Pint passes

\[ \] Larastan passes

\[ \] All automated tests pass

\[ \] Queue workers configured

\[ \] Scheduler configured

\[ \] Staging deployment completed

\[ \] Manual QA completed before production deployment

Architecture Decisions (ADR)

For major architectural decisions (authentication, package replacements,
queues, PDFs, etc.), create a brief ADR describing: - Decision -
Alternatives considered - Rationale

Additional Guidance

Prefer Laravel Events, Listeners, Notifications, Jobs, Bus Batching, and
Scheduling where they improve separation of concerns. Do not queue work
simply for the sake of using queues.

Controllers should remain thin orchestration layers. If a controller
action grows beyond simple request handling and service coordination,
extract the logic into Services, Actions, Jobs, or Events as
appropriate.

Optimize for long-term maintainability rather than minimizing the number
of changed files.
