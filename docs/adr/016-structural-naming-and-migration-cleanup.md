# ADR-016: Structural naming and migration cleanup

**Status:** Accepted  
**Date:** 2026-07-23  
**Relates to:** [ADR-006](006-rollback-separate-deploy.md), [ADR-010](010-user-role-enum-policies.md), Spec Phase 2 domain services

## Context

During the L13 port, some migrations and controllers reflected legacy file structure or transport-era names rather than Spec domain language:

- An idempotent `ensure_owners_emailstatus` migration duplicated `emailstatus`, which was already on `create_owners_table` in the same commit.
- `AjaxDashboardController` copied the legacy fat-controller name for thin JSON dashboard APIs.
- `SendEmailController` echoed the legacy `SendemailsController` verb-file name instead of the Spec `EmailService` domain.
- `PropertyPaymentController` duplicated HTTP surface for a domain Spec already maps to a single `PaymentService` alongside booking payments.

Spatie Permission remains removed (ADR-010). `HelloSignDetail` / `ZohoCodeDetail` and `TermsController` are left unchanged in this pass (other-team ownership / no Spec-driven rename).

## Decision

1. **Migrations:** Delete redundant `ensure_owners_emailstatus_column`. Keep `users.role` and `users.active` as separate alters (real phased evolution; staging may have applied `add_role`). Keep Spatie no-op create + drop as teardown history only — do not reintroduce Spatie.
2. **Dashboard:** Merge dashboard JSON actions into `DashboardController` (Blade page + JSON APIs). Delete `AjaxDashboardController`.
3. **Email:** Rename `SendEmailController` → `EmailController` and `StoreSendEmailRequest` → `Admin\Email\StoreEmailRequest`. All outbound sends go through `EmailService`; do not add per-audience email controllers (owner/manager/admin are methods on `EmailController` when needed).
4. **Payments:** Fold property-payment HTTP actions into `PaymentController` (`propertyIndex` / `propertyStore` / `propertyDestroy`). Delete `PropertyPaymentController`. Keep distinct URLs, Form Requests, and model binding.
5. **Physical DB names** (`emailstatus`, `hellosigndetails`, etc.) stay unchanged for dump-import fidelity.

## Alternatives considered

1. Rename to `OwnerMailController` / `BookingPaymentController` — rejected (over-narrow; Spec domain is Email / Payments).
2. Keep separate property payment controller — rejected (same thin `PaymentService` wrapper pattern as a single `EmailController`).
3. Fold `users.role` / `users.active` into `create_users` — rejected (already-migrated envs / staging history).

## Consequences

- Controllers and migrations follow Spec domain language, not legacy filenames.
- Local DBs that already ran the ensure migration may retain an orphaned `migrations` row; schema is unchanged. Prefer `migrate:fresh` for clean validation.
- Docs that inventory **legacy** fat controllers (e.g. L5.1 `AjaxDashboardController` ~4,800 lines) remain historical; L13 class references must use the new names.
