# Lead code review — inspection & implementation

Working branch: `review/lead-feedback` (items 1–3, 5–8).  
Staging branch: `staging` fast-forwarded to `nuxtjs` @ `072db68` (item 4).  
Date: 2026-07-25

---

## Pre-implementation inspection summary

### 1) Controllers calling `$this->authorize(...)`

| Controller | Shape | Auth pattern today |
|---|---|---|
| `BookingController` | Near-resourceful + custom `cancel` | Per-action `$this->authorize`; store/update via FormRequest |
| `ChronologyController` | Resourceful CRUD + custom actions | Per-action; store/update/orders via FormRequest |
| `PaymentController` | Dual resource (`BookingPayment` + `PropertyPayment`) | Per-action; stores via FormRequest |
| `DocumentController` / `PropertyController` | CRUD-ish but authorize against `Booking::class` | Proxy auth |
| Dashboard / Report / Email / Zoho / Import | Custom | Repeated `viewAny` |
| `FailedJobController` | Custom | `accessSuperAdminArea` |
| Owner statements / terms | Custom | `viewOwnerStatements` / `viewOwnerTerms` |

Policies already had conventional `viewAny` / `view` / `create` / `update` / `delete`.

### 2) Inline validation

Only remaining: `ChronologyController::storeOwnerEmails` (`owner_ids` / `owner_ids.*`).  
Document FormRequests already matched the lead’s example.

### 3) Root file `Null`

**Not present** on disk or in git. No references. Nothing to delete.

### 4) Nuxt / staging / Blade

- `nuxtjs` held the SPA under `frontend/`.
- `staging` was behind; now fast-forwarded to include Nuxt.
- Remaining Blade under `resources/views` is email/PDF only (untouched).

### 5) Larastan

Already installed (`^3.10`); was **level 5**. Raised to **level 8** + baseline.

### 6) Docker

Root `compose.yaml` (Sail). Assets in `docker/8.4/`. Moved compose into `docker/` with thin root include.

### 7) Gate registrations

Two `Gate::policy` calls lived in `AppServiceProvider`; no `AuthServiceProvider` yet.

### 8) `BookingService`

`collect($attributes)->except('guest_ids')->all()` inside an existing `DB::transaction`.

---

## Implementation summary

### Item 1 — Thin controllers (authorization)

**`authorizeResource`:**
- `BookingController` → `Booking::class` / `booking` (CRUD). Custom `cancel` uses route `can:update,booking`.
- `ChronologyController` → `Chronology::class` / `chronology` (CRUD). Custom endpoints use `can` middleware (`copy`, templates/documents/check-name, destroyOrder, previewOwners).

Base `Controller` now extends `Illuminate\Routing\Controller` so `authorizeResource` can register middleware.

**`can` middleware only:**
- Dashboard, reports, payments (dual model), documents/properties (Booking proxy), imports emails index, failed jobs, email create, zoho, owner statements index, terms.
- Mutating actions that already authorize in FormRequests keep that path (no duplicate `$this->authorize` in controllers).

### Item 2 — Form Requests

Created `StoreChronologyOwnerEmailsRequest` with the exact previous rules and `can('update', $chronology)`. Wired into `storeOwnerEmails`. No other inline `$request->validate()` remained in controllers.

### Item 3 — `Null` file

Confirmed absent; no delete needed; no references found.

### Item 4 — Nuxt → staging

`git checkout staging && git merge nuxtjs` → fast-forward to `072db68`. `frontend/` present on `staging`. Working branch `review/lead-feedback` unchanged by that content beyond what was already merged earlier into documentation-planning.

### Item 5 — Larastan level 8

- `phpstan.neon.dist`: level **8**, includes Larastan + Carbon + `phpstan-baseline.neon`.
- Baseline: **86** path entries (mostly `argument.type` from `validated()` arrays, union property access, etc.).
- `vendor/bin/phpstan analyse --memory-limit=1G` → **0 errors** with baseline.
- Documented in `CONTRIBUTING.md` / `README.md`; CI label updated to level 8.

### Item 6 — Docker reorganization

- Source of truth: `docker/compose.yaml` (relative paths fixed: `./8.4`, `..:/var/www/html`, `../vendor/laravel/sail/...`).
- Root `compose.yaml` only `include`s `docker/compose.yaml`.
- `docker compose config` resolves correctly (services: laravel.test, queue, scheduler, mysql, redis, mailpit).
- **End-to-end `docker compose up` not run:** Docker Desktop daemon reported “unable to start” on this machine. Config validation passed; live bring-up needs a working Docker engine.

### Item 7 — AuthServiceProvider

- Created `App\Providers\AuthServiceProvider` with the two `Gate::policy` registrations.
- Cleared them from `AppServiceProvider`.
- Registered in `bootstrap/providers.php`.

### Item 8 — BookingService + DTO

- `BookingData` DTO under `app/DataTransferObjects/` (reference pattern documented).
- `create()` uses `BookingData::fromArray` → `Arr::except` + guest ids, `DB::transaction`, returns booking with `property` + `guests`.
- `update()` also switched to `Arr::except` (same quality fix, no new DTO).

### Docs

- `docs/adr/019-authorize-resource-and-form-requests.md`
- `CONTRIBUTING.md`, `README.md` (Docker + Larastan)
- `docs/AGENTS.md` / `.cursor/rules/agents.mdc` / technical docs: Larastan level 8 + compose layout

### Tests

- Full suite: **99 passed**.
- Adjusted three placeholder Example stubs that asserted `200` on `/` (home now redirects to frontend URL — intentional).

### API compatibility

No intentional changes to routes, payloads, status codes, validation keys, or policy outcomes — only where authorization/validation is *declared*.
