# ADR-012: Early owners / properties / regions foundation (pre–Phase 2.2)

**Status:** Accepted  
**Date:** 2026-07-23  
**Relates to:** [ADR-009](009-owner-access-predicates-and-active-flags.md), [ADR-010](010-user-role-enum-policies.md), [ADR-011](011-hostaway-phase21-minimal-sync.md)

## Decision

1. **Pull forward** core `regions`, `subregions`, `owners`, `properties`, `owners_properties`, `owner_terms_agreements`, and `user_owners` schema **before Phase 2.2** so Chronology/Email can use real owner–property relationships instead of stubs.
2. Implement real `User::hasAgreedToTerms()` and `User::hasActiveAccess()` against those tables — **no** checker services or contracts for those predicates (ADR-009).
3. Add **`users.active`** as the sole account enable/disable flag. Do **not** port legacy `owners.status` as an enable toggle (or as any auth gate).
4. Property admin CRUD UI and satellite property tables remain **Phase 2.5**; this foundation is schema + models + predicates only.

## Alternatives considered

1. Keep Phase 1 always-allow predicate stubs until Phase 2.5 — rejected; Chronology (2.2) and Bookings (2.3) need owners/properties earlier  
2. Early shared foundation (chosen)  
3. Port `owners.status` alongside `users.active` — rejected (ADR-009 dual-flag hazard)

## `owners.status` investigation

Legacy `owners.status` is a boolean defaulting to `1`, used as an owner-level enable dropdown overlapping `users.active`. That is **not** a distinct lifecycle field. Per ADR-009 it is **omitted** from the L13 `owners` table. Wave A SQL dumps that include `owners.status` must drop or ignore that column on import.

Active property access remains **computed** from `properties.status` (+ not deleted) via `hasActiveAccess()`.

## Consequences

- Owner middleware tests and owner route access require terms agreement rows and ≥1 live property for owners who should pass.
- Local dumps go under `storage/app/legacy-dumps/` (gitignored).
- Phase 2.5 still owns Property admin workflows and remaining satellite tables.
