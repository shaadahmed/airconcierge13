# Phase-wise Completion Report (Phase 0–5)

**Date:** 2026-07-25 (regenerated after lead-review / post-report code changes)  
**Prior report:** same path; this file replaces the earlier same-day audit.  
**Scope:** Verify Phase 0 through Phase 5 tasks against the **current** codebase (not documentation claims alone).  
**Method:** Read-only inspection of `docs/` definitions, then cross-check of source, tests, configs, migrations, CI, and ADRs.  
**Report location:** `docs/phase_wise_completion_report.md` (same convention as `docs/phase_2_completion_report.md` / `docs/phase_3_completion_report.md`).

**Material changes since prior same-day report (from `docs/review.md` + git history):** Larastan raised to level 8 + baseline; Sail compose moved under `docker/` with thin root include; controller auth via `authorizeResource` / route `can` middleware (ADR-019); `StoreChronologyOwnerEmailsRequest`; `AuthServiceProvider`; `BookingData` DTO; home smoke tests assert redirect. Phase 2 DoD gaps (Report/Dashboard Form Requests; dedicated Doc/Import/Property/Report/Dashboard Policies) remain **unchanged**.

---

## 1. How phases and tasks are defined

### Primary sources of truth

| Role | File |
|------|------|
| Requirements | `docs/Laravel_5.1_to_13_Modernization_Spec.md` — Structural Refactor Checklist (Phase 0–5), package table, Queues & Jobs catalog, per-module Definition of Done |
| Execution roadmap / task checklists | `docs/migration-plan.md` §8 (Full phase breakdown), §9 (job candidates), §10 (package table), §11 (Phase 0 DoD + per-module DoD) |
| Architecture decisions | `docs/adr/001`–`019` (phase-specific decisions and approved deferrals) |

### Supporting / status documents (consulted, not sole authority)

| File | Relevance |
|------|-----------|
| `docs/migration-inventory.md` | Controllers, packages, helpers disposition (Phase 1a / 3) |
| `docs/phase_2_completion_report.md` | Prior Phase 2 audit (2026-07-23); several gaps re-checked |
| `docs/phase_3_completion_report.md` | Prior Phase 3 audit |
| `docs/frontend-report.md` | Phase 5 / ADR-018 shipped surfaces |
| `docs/review.md` | Lead-review inspection & implementation notes (2026-07-25) |
| `docs/test-report.md` | Historical Phase 0–1 verification notes |
| `docs/technical-documentation.md`, `docs/user-documentation.md` | Implemented surface descriptions |
| `docs/deploy/*` | Phase 4 worker templates; Phase 5 SPA hosting |
| `docs/AGENTS.md`, `.cursor/rules/*` | Layering / behavior-preservation rules (Phase 0 deliverable) |

### Ambiguities noted (not unilaterally resolved)

1. **Spec vs plan checkbox lag:** Spec Phase 0, 1, 2, and 4 checklist items remain rendered as unchecked (`\[ \]`). Plan marks the same work Implemented/`[x]`. This report treats **plan + ADRs + code** as the operational task list and records Spec lag under Discrepancies.
2. **Phase 5 meaning changed:** Spec originally described Blade/Yajra; plan + ADR-018 rewrite Phase 5 as Nuxt SPA (Yajra declined, ADR-014). Tasks below use the **current plan Phase 5** wording.
3. **“Complete” ≠ legacy parity:** Plan Phase 2 services are greenfield extraction scaffolds, not ports of multi-thousand-line L5.1 controllers. Status below reflects **plan/Spec checklist depth**, not full behavioral parity.
4. **Phase 1a** is a plan-only gap-closure phase (not a separate Spec phase heading); included here because it is defined with tasks and exit criteria in the plan.
5. **Phase 0 quality bar drift:** Plan §11 Phase 0 DoD still says Larastan **level 5**; code/CI now use **level 8** (lead-review). Treated as Phase 0 quality gate **Done** at the higher bar.

---

## 2. Phase-by-phase breakdown

Status values: **Done** | **Partially Done** | **Not Done** | **Unable to Verify**

---

### Phase 0 — Foundation

**Doc claim:** Implemented (`migration-plan.md` §8 / §11).  
**Code verdict:** **Done** on repository evidence; runtime liveness of Sail/Redis/DB is **Unable to Verify** from files alone.

| # | Task (source) | Status | Evidence |
|---|---------------|--------|----------|
| 0.1 | Laravel 13 skeleton / fresh approach (Spec + plan; ADR-001) | **Done** | `composer.json` (`laravel/framework` `^13.8`); `docs/adr/001-fresh-laravel-13-skeleton.md` |
| 0.2 | PHP 8.2+ (Spec) / 8.3+ Sail (plan) | **Done** | `composer.json` `"php": "^8.3"`; Sail `8.4` image via `docker/compose.yaml`; CI PHP 8.4 |
| 0.3 | Sail: MySQL + Redis + app; queue worker + scheduler | **Done** (compose) / **Unable to Verify** (live) | Root `compose.yaml` includes `docker/compose.yaml`; services `laravel.test`, `mysql`, `redis`, `queue` (`queue:work redis`), `scheduler` (`schedule:work`). Live `docker compose up` not confirmed (`docs/review.md`: Docker Desktop engine unhealthy) |
| 0.4 | Redis for queue / cache / session; DB `airconcierge` | **Done** (config) / **Unable to Verify** (DB exists) | `.env.example` `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `DB_DATABASE=airconcierge` |
| 0.5 | Pest harness + smoke test | **Done** | `pestphp/pest`; `tests/Pest.php`; example tests updated to assert home redirect (lead-review) |
| 0.6 | Pint + Larastan | **Done** | Pint in CI; `phpstan.neon.dist` **`level: 8`** + `phpstan-baseline.neon`; CI step “Larastan (level 8)” — raised from plan’s original level-5 wording |
| 0.7 | CI: lint, analyse, tests (+ frontend job) | **Done** | `.github/workflows/ci.yml` (Pint, Larastan, Pest, frontend lint) |
| 0.8 | Horizon **not** installed (plain `queue:work`) | **Done** | No `laravel/horizon` in Composer; compose uses `queue:work` |
| 0.9 | Laravel Boost | **Done** | `laravel/boost` in `composer.json` |
| 0.10 | ADRs 001–006 | **Done** | `docs/adr/001`–`006` present |
| 0.11 | Rollback plan documented | **Done** | `docs/adr/006-rollback-separate-deploy.md` |
| 0.12 | `docs/migration-inventory.md` | **Done** | File present |
| 0.13 | `docs/` tracked in git | **Done** | `.gitignore` does not ignore `docs/` |
| 0.14 | Agent rules: behavior preservation + layering | **Done** | `docs/AGENTS.md`; `.cursor/rules/*` |
| 0.15 | No wholesale `migrations_fresh` in Phase 0 | **Done** (intent preserved) | Reference tree remains; live migrations added later per domain |
| 0.16 | Old XAMPP app untouched | **Unable to Verify** | Policy-only |
| 0.17 | No production secrets committed | **Done** (structure) / **Unable to Verify** (history) | `.env` gitignored; placeholders in `.env.example` |

---

### Phase 1 — Routing & authorization

**Doc claim:** Implemented (scaffold; ADR-007).  
**Code verdict:** **Done**.

| # | Task (source) | Status | Evidence |
|---|---------------|--------|----------|
| 1.1 | Split routes into `web` / `api` / `console` | **Done** | `routes/web.php`, `routes/api.php`, `routes/console.php`; `bootstrap/app.php` |
| 1.2 | Class-based controller routes | **Done** | Class/`::class` references in `routes/web.php` |
| 1.3 | Entrust → Policies / role auth (ADR-010) | **Done** | `app/Enums/UserRole.php`; Policies under `app/Policies/`; Spatie dropped via migration; `AuthServiceProvider` registers payment policies (`bootstrap/providers.php`) |
| 1.4 | Secure/remove public `/cron/*` | **Done** | No HTTP cron routes; schedules in `routes/console.php` |
| 1.5 | Session auth shell login/logout | **Done** | `AuthenticatedSessionController`; guest/auth routes |
| 1.6 | Owner terms / active middleware, owner-scoped only (ADR-009) | **Done** | Nested group in `routes/web.php` around owner-statements/terms only; aliases in `bootstrap/app.php` |
| 1.7 | `User` predicates `hasAgreedToTerms` / `hasActiveAccess` | **Done** | Real implementations on `app/Models/User.php` |
| 1.8 | Hostaway webhook path: 200 fast + queue | **Done** | `POST wh/hostaway/booking/created` → `HostawayWebhookController` → job (auth in Phase 1a) |
| 1.9 | Schedule entries | **Done** | `routes/console.php` |

---

### Phase 1a — Early-stage gap closure (plan-only)

**Doc claim:** Implemented (2026-07-19).  
**Code verdict:** **Done** (minor inventory status lag — docs only).

| # | Task (source) | Status | Evidence |
|---|---------------|--------|----------|
| 1a.1 | Hostaway webhook Basic Auth (ADR-008) | **Done** | `HostawayWebhookAuthenticator`; 401 vs 200+dispatch |
| 1a.2 | Pest coverage for auth success/failure | **Done** | `tests/Feature/Webhooks/HostawayWebhookTest.php` |
| 1a.3 | Package & Integration audit with ownership phase | **Done** | `migration-plan.md` §10; inventory has ownership (**status** cells partially stale) |
| 1a.4 | ADR gap check (001–008+) | **Done** | ADRs through 019 present under `docs/adr/` |

---

### Phase 2 — Extract Services (by domain)

**Doc claim:** Implemented (2.1–2.5).  
**Code verdict:** **Done** against plan exit criteria (with documented deferrals); **Partially Done** against strict Spec per-module DoD (Policies / Report–Dashboard Form Requests).

#### Spec Phase 2 service checklist

| Spec service | Status | Evidence |
|--------------|--------|----------|
| `BookingService` | **Done** | `app/Services/Bookings/BookingService.php` (+ `BookingData` DTO) |
| `PaymentService` | **Done** | `app/Services/Payments/PaymentService.php` |
| `ChronologyService` | **Done** | `app/Services/Chronology/ChronologyService.php` |
| `DashboardService` | **Done** (shallow vs AjaxDashboard) | `app/Services/Dashboard/DashboardService.php` |
| `ReportService` | **Done** (shallow) | `app/Services/Reports/ReportService.php` |
| `EmailService` | **Done** | `app/Services/Email/EmailService.php` (Laravel Mail; no `phpmailer` in Composer) |
| `HostawayService` | **Done** | `app/Services/Hostaway/HostawayService.php` + sync + authenticator |
| `DocumentService` | **Done** (shallow) | `app/Services/Documents/DocumentService.php` |
| `ImportService` | **Done** (shallow) | `app/Services/Import/ImportService.php` |
| `ZohoSignService` / `HelloSignService` | **Done** / **Done (deferred)** | Zoho present; HelloSign absent by **ADR-013** |
| Related (plan): `PropertyService`, `PdfService` | **Done** | Under `Properties/`, `Pdf/` |

#### Phase 2.1 — Hostaway + webhooks

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 2.1.1 | `HostawayService` API client | **Done** | `app/Services/Hostaway/HostawayService.php` |
| 2.1.2 | Thin webhook controller | **Done** | `HostawayWebhookController` |
| 2.1.3 | Job → `HostawayReservationSyncService` (+ BookingService wiring) | **Done** | `SyncHostawayReservationJob`; sync service |
| 2.1.4 | Tests: webhook + sync | **Done** | `tests/Feature/Webhooks/`, `Hostaway/` |
| 2.1.5 | Hostaway migrations | **Done** | `hostaway_access_tokens`, `hostaway_reservation_logs` |
| 2.1.6 | Admin Hostaway Form Requests/Policies | **Done** (deferred by design) | ADR-011 minimal slice |
| 2.1.7 | ADR-011 | **Done** | `docs/adr/011-hostaway-phase21-minimal-sync.md` |

#### Phase 2.2 — Email / Chronology

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 2.2.1 | `ChronologyService`, `EmailService`, `ZohoSignService` | **Done** | Services under domain folders |
| 2.2.2 | Form Requests + Policies | **Done** | Chronology/Email requests including **`StoreChronologyOwnerEmailsRequest`** (lead-review); `ChronologyPolicy`; `authorizeResource` on `ChronologyController` |
| 2.2.3 | Schedules `chronology:process-sends`, `zoho:poll-completions` | **Done** | `routes/console.php` |
| 2.2.4 | Critical-path tests | **Partially Done** | Real tests under `tests/Feature/Chronology/`, `Email/`, `Zoho/`; root `tests/Feature/ChronologyHttpTest.php` and `ChronologyServiceTest.php` remain placeholder `get('/')` smoke (now `assertRedirect`) |
| 2.2.5 | HelloSign deferred | **Done** (approved deferral) | ADR-013 |

#### Phase 2.3 — Bookings & Payments

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 2.3.1 | `BookingService`, `PaymentService` | **Done** | Services present; thin controllers |
| 2.3.2 | Form Requests + Policies | **Done** | Booking/Payment requests + policies; `BookingController` uses `authorizeResource` |
| 2.3.3 | Hostaway mutations via `BookingService` | **Done** | Sync service → `BookingService` |
| 2.3.4 | DomPDF + `PdfService` (ADR-005) | **Done** | `barryvdh/laravel-dompdf`; `PdfService`; `GeneratePdfJob` |
| 2.3.5 | `BookingData` DTO | **Done** | `app/DataTransferObjects/BookingData.php` used in `BookingService::create` |
| 2.3.6 | Feature tests | **Done** | `tests/Feature/Bookings/*` |
| 2.3.7 | Legacy fat-controller parity | **Not Done** (not claimed) | Scaffold depth only |

#### Phase 2.4 — Reports & Dashboard

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 2.4.1 | `ReportService`, `DashboardService` | **Done** | Services + thin controllers |
| 2.4.2 | Form Requests for report/dashboard endpoints | **Not Done** | `ReportController` / `DashboardController` still use raw `Illuminate\Http\Request`. Do not confuse with `OwnerStatementReportRequest` (owner statements only) |
| 2.4.3 | Dedicated Policies | **Not Done** | Routes use `can:viewAny,Booking` stand-in; no `ReportPolicy` / `DashboardPolicy` |
| 2.4.4 | Critical-path tests | **Partially Done** | Service coverage in `Phase225DomainTest` (and related); limited dedicated HTTP report tests |
| 2.4.5 | Identify PDF/report jobs for Phase 4 | **Done** | `GeneratePdfJob`, `GenerateReportJob` |

#### Phase 2.5 — Remaining admin modules

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 2.5.1 | `DocumentService`, `ImportService`, `PropertyService` | **Done** | Under `Documents/`, `Import/`, `Properties/` |
| 2.5.2 | Form Requests (Doc/Import/Property) | **Done** | Store/Update Document, Import Owners/Properties, StoreImportedEmail, Store/Update Property |
| 2.5.3 | Domain Policies for Doc/Import/Property | **Not Done** | Still BookingPolicy proxy via FormRequest `authorize()` and/or `can:viewAny,Booking` on routes. Policies on disk remain only: `BookingPolicy`, `PaymentPolicy`, `ChronologyPolicy`, `UserPolicy` |
| 2.5.4 | Real owner predicates; no `owners.status` | **Done** | `User` predicates; ADR-012 |
| 2.5.5 | Owner middleware on owner groups only | **Done** | Nested group in `routes/web.php` |
| 2.5.6 | `images:compress-uploads` live | **Done** | Dispatches `CompressImagesBatchJob` |
| 2.5.7 | Metrics / alert scheduled commands | **Done** | `property:monthly-metrics`, `alert:booking-conflict`, etc. |
| 2.5.8 | Package decisions ADR-014 | **Done** | `docs/adr/014-phase25-package-decisions.md` |
| 2.5.9 | Thin-controller auth pattern (ADR-019) | **Done** | `authorizeResource` / route `can` middleware; documented in `docs/adr/019-authorize-resource-and-form-requests.md` and `docs/review.md` |
| 2.5.10 | Critical-path tests | **Partially Done** | `Phase225DomainTest`; `OwnerTermsFlowTest`; `OwnerStatementsFlowTest` |

**Phase 2 exit criteria (plan):** Met with documented deferrals.  
**Spec per-module DoD (strict):** Still open for Report/Dashboard Form Requests and dedicated Policies for Report/Dashboard/Document/Import/Property.

---

### Phase 3 — Helpers Decomposition

**Doc claim:** Implemented / DONE.  
**Code verdict:** **Done** (approved deferrals recorded).

| # | Task (source) | Status | Evidence |
|---|---------------|--------|----------|
| 3.1 | Audit ~181 legacy helpers; disposition inventory | **Done** | `docs/migration-inventory.md` helpers section; `docs/phase_3_completion_report.md` |
| 3.2 | Critical pure utilities → `App\Support\*` | **Done** | `DateFormatter`, `DateMath`, `MoneyFormatter`, `StringCleaner` |
| 3.3 | Unit tests for Support | **Done** | `tests/Unit/Support/*` |
| 3.4 | Predicates on models, not global helpers | **Done** | `User` predicates; no new globals |
| 3.5 | No `helpers.php` Composer `files` autoload | **Done** | `composer.json` PSR-4 only; no `app/helpers.php` |
| 3.6 | ADR-015 | **Done** | `docs/adr/015-helpers-support-disposition.md` |
| 3.7 | Deferred buckets (fee math, `list_*`, month-close, etc.) | **Done** (approved deferral) | ADR-015 / plan Phase 3 |

---

### Phase 4 — Async Migration

**Doc claim:** Implemented (2026-07-24); ADR-017; Horizon deferred.  
**Code verdict:** **Done** for infrastructure + Spec job catalog with standards; **Partially Done** for some job **domain bodies** (acknowledged in plan).

#### Spec / plan job catalog

| Job | Status | Evidence |
|-----|--------|----------|
| `SendOutboundEmailJob` | **Done** | `app/Jobs/SendOutboundEmailJob.php` + `HandlesJobFailures` |
| `SendNotificationJob` | **Done** | `app/Jobs/SendNotificationJob.php` |
| `GeneratePdfJob` | **Done** | `app/Jobs/GeneratePdfJob.php` |
| `CompressImagesBatchJob` | **Done** | `app/Jobs/CompressImagesBatchJob.php` |
| `ProcessImportedEmailJob` | **Done** | `app/Jobs/ProcessImportedEmailJob.php` |
| `UploadBackupToCloudJob` | **Done** (local until Drive adapter) | Job present; Drive adapter deferred ADR-014 |
| `ProcessDropboxCsvJob` | **Done** (pipeline; domain stub) | Job present; `DropboxFormService` methods log-only follow-ups |
| `SyncHostawayReservationJob` | **Done** | Present |
| `ProcessSignatureRequestJob` | **Done** | Present |
| `RecalculatePropertyMetricsJob` | **Done** | Present |
| `RunScheduledAlertJob` | **Done** (partial domain) | `AlertDispatchService` implements `booking-conflict`; other keys stub-log |
| `GenerateReportJob` | **Done** | Present |

#### Other Phase 4 tasks

| # | Task | Status | Evidence / gap |
|---|------|--------|----------------|
| 4.1 | Queue worker infrastructure templates | **Done** | `docs/deploy/queue-worker.supervisor.conf.example`, `queue-worker.systemd.service.example`; Sail queue service (live host verify = Phase 6) |
| 4.2 | HTTP crons → Schedule → commands/jobs | **Done** | `routes/console.php`; no public `/cron/*` |
| 4.3 | Failed-job monitoring + retry dashboard | **Done** | `FailedJobController`; routes; `frontend/pages/admin/failed-jobs/index.vue`; ADR-017 |
| 4.4 | Slack via Laravel channel | **Done** | `laravel/slack-notification-channel`; `QueueJobFailedNotification` |
| 4.5 | Job Standards via `HandlesJobFailures` | **Done** | Trait on catalog jobs |
| 4.6 | Horizon not installed | **Done** | Absent from Composer; ADR-017 |
| 4.7 | Alert / Dropbox domain parity | **Partially Done** | Pipeline live; domain bodies stubbed except booking-conflict |
| 4.8 | Google Drive Flysystem adapter | **Not Done** (approved follow-up) | Job + disk config ready; Composer adapter deferred (ADR-014) |

---

### Phase 5 — Frontend / Views (Nuxt SPA)

**Doc claim:** Completed (ADR-018; Yajra declined ADR-014).  
**Code verdict:** **Done** for plan Phase 5 exit criteria / shipped scope; follow-up cards remain (documented).

| # | Task (plan Phase 5) | Status | Evidence |
|---|---------------------|--------|----------|
| 5.1 | Nuxt SPA owns interactive admin UI (ADR-018) | **Done** | `frontend/` Nuxt 3; admin pages under `frontend/pages/admin/*` |
| 5.2 | Shared UI shells | **Done** | `PageHeader`, `EmptyState`, `DataTableShell`, `ConfirmDialog`, `AppAlert` |
| 5.3 | Properties / documents / imports Nuxt screens | **Done** | Matching pages + stores/services |
| 5.4 | Owner terms agree/disagree + `dynamic_content` | **Done** | `DynamicContent` model + migration; seeder; `OwnerTermsService`; `terms.vue`; flow tests |
| 5.5 | Owner statements report JSON/CSV | **Done** | `OwnerStatementService`; `OwnerStatementReportRequest`; page + flow test |
| 5.6 | Dashboard revenue chart + Sneat stats | **Done** (shipped depth) | `dashboard.vue`; commission/full AjaxDashboard suite = follow-up |
| 5.7 | Yajra declined; Nuxt/Vuetify tables | **Done** | No yajra in Composer; ADR-014 |
| 5.8 | Remove unused Sneat demo cruft | **Done** | `frontend/components/UpgradeToPro.vue` and `frontend/views/` **absent on disk**; stale type refs may remain in generated `frontend/.nuxt/` until regen |
| 5.9 | Frontend CI: `npm ci` + lint | **Done** | `.github/workflows/ci.yml` job `frontend` |
| 5.10 | SPA hosting doc | **Done** | `docs/deploy/spa-hosting.md` |
| 5.11 | PDF Blade retained only | **Done** | `resources/views/pdf/{payment-receipt,report,owner-terms-agreement}.blade.php` |
| 5.12 | No wholesale L5.1 Blade port | **Done** | Interactive UI is Nuxt |
| 5.13 | Follow-up cards (commission dashboard, P&L schema, terms CMS) | **Partially Done** / tracked | Documented in plan + `frontend-report.md` |

**Spec Phase 5 (rewritten checkboxes):** Marked `[x]` in Spec for Nuxt SPA / shells / Yajra decline — aligns with code.

---

## 3. Discrepancies Found

| # | Documentation claim | Codebase reality |
|---|---------------------|------------------|
| D1 | Spec Phase 0, 1, 2, 4 checkboxes still unchecked (`\[ \]`) | Work is present in code; plan marks Implemented/`[x]`. Spec checklist is stale for those phases. |
| D2 | Spec Phase 4 still references `app/Console/Kernel.php` | Laravel 13 schedules live in `routes/console.php`. |
| D3 | Plan §10: `phpmailer` Status still **deferred**; PDF package still **deferred (spike)** | `phpmailer` absent; Laravel Mail used; `barryvdh/laravel-dompdf` installed (ADR-005 accepted). |
| D4 | `docs/migration-inventory.md` package Status still lists Yajra/Guzzle/Slack/phpmailer as **deferred** | Plan §10 and/or Composer show several as done/closed; inventory Status column is stale. Inventory Phase 5 line still mentions Blade + Yajra. |
| D5 | Plan §11 Phase 0 DoD: Larastan **level 5** | Code/CI use **level 8** + baseline (`phpstan.neon.dist`, CI label, `docs/review.md`). |
| D6 | Plan / technical docs: “Phase 0–5 complete” | Accurate for **plan exit criteria**; inaccurate if read as full Spec per-module DoD or full legacy parity (Policies/Form Requests gaps; alert/Dropbox stubs; Drive adapter). |
| D7 | ADR-002 (Spatie) vs ADR-010 (`UserRole` + Policies) | Current code follows ADR-010; Spatie removed. |
| D8 | Spec “validate signatures” on Hostaway | Implemented as Hostaway Basic Auth (ADR-008), not HMAC. |
| D9 | Spec Phase 5 originally Blade/Yajra | Superseded by ADR-018 + ADR-014; Spec Phase 5 bullets updated to Nuxt. |
| D10 | Prior `phase_2_completion_report.md` (2026-07-23) gaps on Doc/Import/Property Form Requests and compress stub | Those gaps were already closed before this regen; **Report/Dashboard Form Requests** and **dedicated domain Policies** remain open (confirmed again after lead-review). |
| D11 | `docs/review.md` claims 99 tests passed | Test **count/pass** not re-run in this verification session — cited as prior session evidence only (**Unable to Verify** live). |

---

## 4. Overall completion summary

| Phase | Plan claim | Code verdict | Notes |
|-------|------------|--------------|-------|
| **0** Foundation | Implemented | **Done** (runtime caveats) | Compose under `docker/`; Larastan **8**; live Sail/DB Unable to Verify |
| **1** Routing & auth | Implemented | **Done** | Owner middleware scoped; `AuthServiceProvider` present |
| **1a** Gap closure | Implemented | **Done** | Hostaway Basic Auth + package ownership matrix |
| **2** Services (2.1–2.5) | Implemented | **Done** (plan) / **Partial** (strict DoD) | Lead-review improved auth/validation wiring; Report/Dashboard FRs + dedicated Policies still missing |
| **3** Helpers | Done | **Done** | Support + inventory + ADR-015 |
| **4** Async | Implemented | **Done** (w/ known stubs) | Full job catalog + monitoring; alert/Dropbox bodies + Drive adapter follow-ups |
| **5** Frontend (Nuxt) | Completed | **Done** (shipped scope) | Follow-up cards remain |

### Approximate task-row counts (this regen)

| Classification | Approx. count |
|----------------|---------------|
| Done | ~95 |
| Partially Done | ~11 |
| Not Done | ~5 (strict DoD items + Drive adapter follow-up) |
| Unable to Verify | ~4 (runtime / external / live test suite) |

### What changed vs prior same-day report

| Area | Prior report | This regen |
|------|--------------|------------|
| Larastan | Level 5 Done | **Level 8 Done** |
| Sail compose | Assumed root `compose.yaml` | **Root include → `docker/compose.yaml`** |
| Chronology inline validation | Noted residual inline validate | **Closed** via `StoreChronologyOwnerEmailsRequest` |
| Controller auth | Mixed `$this->authorize` | **`authorizeResource` / route `can` (ADR-019)** |
| `BookingData` / `AuthServiceProvider` | Not listed | **Done** |
| Report/Dashboard Form Requests | Not Done | **Still Not Done** |
| Dedicated Doc/Import/Property/Report/Dashboard Policies | Not Done | **Still Not Done** |
| Root Chronology placeholder tests | Partially Done | **Still Partially Done** |
| UpgradeToPro / `frontend/views` | Done (WT) | **Done** (absent on disk) |

### Bottom line

- **Phases 0, 1, 1a, and 3** remain complete on repository evidence (with higher Larastan bar and Docker layout update).  
- **Phase 2** still meets the migration-plan bar; lead-review closed auth/Form Request polish items but **did not** close Report/Dashboard Form Requests or dedicated domain Policies.  
- **Phase 4** catalog/infra/monitoring remain Done; alert/Dropbox domain bodies and Google Drive adapter remain approved follow-ups.  
- **Phase 5** Nuxt SPA shipped scope remains Done.  
- **Largest documentation drift:** Spec Phase 0/1/2/4 unchecked boxes; stale package Status rows; plan Phase 0 DoD still saying Larastan level 5.

**Next phase per plan:** Phase 6 — Deployment readiness (not in scope of this report).

---

## 5. Verification notes

- No source, test, config, or existing docs files were modified for this report (only this file overwritten).  
- Automated test suite was **not** re-executed in this session; test **existence** is cited as evidence. `docs/review.md` reports 99 passed from a prior run.  
- Live Docker/Sail process health was not confirmed; compose/config presence is cited (`docs/review.md` notes Docker Desktop failure).  
- Where plan and Spec disagree on checkbox state, both are recorded; implementation evidence decides status.
