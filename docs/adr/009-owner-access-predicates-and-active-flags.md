# ADR-009: Owner access predicates, service boundaries, and a single active flag

**Status:** Accepted  
**Date:** 2026-07-22  
**Relates to:** [ADR-002](002-auth-spatie-permission.md), [ADR-007](007-phase1-greenfield-routing-auth.md)

## Decision

1. **Owner terms and active-property access** are enforced on an **owner route boundary** (`owner.terms` / `owner.active` middleware applied only to Property Owner–relevant routes), not across the shared admin shell.
2. **Simple domain predicates live on the model** (`User::hasAgreedToTerms()`, `User::hasActiveAccess()`). Middleware/Gates call those predicates. Services orchestrate multi-step workflows only; they must not wrap ordinary model/database lookups.
3. **Canonical account enable flag is `users.active` only.** Do not port `owners.status` as a second enable/disable toggle. **Active access** (may use the owner app surface) is **derived** from having ≥1 qualifying live property (`properties.status`), not from a stored owner-level “active” flag.

## Context

Phase 1 originally bound always-allow checker contracts in `AppServiceProvider` and applied owner middleware to the entire `admin` group. That made owner-specific behavior part of the shared admin request path and encouraged query-wrapper “services” for predicates that belong on the model.

Legacy schema also carried overlapping enable flags (`users.active` and `owners.status`), which caused operational confusion. Separately, legacy “active access” meant property liveness via `hasActiveProperty` — a different concept.

## Alternatives considered

1. Keep injectable checker contracts/stubs until the owners/properties domain lands  
2. Model predicates + owner-scoped middleware (chosen)  
3. Global `Gate::before` / provider boot hooks for owner rules  

## Consequences

- Staff roles never enter the owner middleware stack for staff/shared routes.
- When `owners` is imported in a later phase, omit `status` unless reference-app review proves a distinct lifecycle meaning (then rename; never a generic “active”). Flag for approval rather than silently dropping a distinct meaning.
- Real terms/property queries replace the Phase 1 always-allow model stubs in their approved domain phases — without reintroducing checker services for those booleans.
- `users.active` (when added) remains login/account eligibility only, never a proxy for “has active property.”
