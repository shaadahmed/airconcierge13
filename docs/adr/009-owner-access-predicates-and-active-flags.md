# ADR-009: Owner access predicates, service boundaries, and a single active flag

**Status:** Accepted  
**Date:** 2026-07-22  
**Relates to:** [ADR-002](002-auth-spatie-permission.md), [ADR-007](007-phase1-greenfield-routing-auth.md)

## Decision

1. **Owner terms and active-property access** are enforced on an **owner route (or equivalent authorization) boundary** — `owner.terms` / `owner.active` apply only to Property Owner–relevant routes, not the shared admin shell. Defensive role guards inside middleware are allowed; they are not the primary scoping mechanism.
2. **Simple domain predicates live on the model** (e.g. `User::hasAgreedToTerms()`, `User::hasActiveAccess()`). Middleware/Policies call those predicates. **Services orchestrate multi-step workflows** (multiple models, transactions, side effects, events, notifications, external systems). Services must not wrap ordinary Eloquent lookups or boolean predicates.
3. **Contracts/interfaces** exist only where they provide meaningful architectural value (multiple substantive implementations, external system boundaries, or testing seams with real benefit) — not merely to abstract model access.
4. **Canonical account enable flag is `users.active` only.** Do not port `owners.status` as a second enable/disable toggle. **Active access** (may use the owner app surface) is **derived** from having ≥1 qualifying live property (`properties.status`), never stored as another owner-level “active” flag.

## Context (why — senior review)

Phase 1 originally bound always-allow checker “services” behind contracts in `AppServiceProvider` and applied owner middleware across the entire `admin` group. That created three lasting risks:

1. **Anemic models / query-wrapper services** — parking `hasAgreed` / `hasActiveAccess` in a service trains later phases to treat ordinary domain questions as a service layer. Predicates about an entity’s state belong on the model; services exist when there is a workflow to coordinate.
2. **Broad owner enforcement** — hanging owner middleware on shared admin routes makes every role enter owner-specific code paths (even with early returns). Relying on every implementation to preserve a non-owner bypass is fragile and repeats past “global hook” mistakes.
3. **Duplicate “active” sources of truth** — legacy carried both `users.active` and `owners.status` (owner UI “active” dropdown) for overlapping enable/disable meaning, which caused operational confusion. Separately, legacy “active access” meant property liveness (`hasActiveProperty`) — a different concept that must stay derived, not duplicated onto the owner record.

This migration is a **revamp**, not a lift-and-shift of those patterns.

## Alternatives considered

1. Keep injectable checker contracts/stubs until owners/properties land  
2. Model predicates + owner-scoped middleware/authorization boundaries (chosen)  
3. Global `Gate::before` / provider boot hooks that evaluate owner rules for every role  

## Target flow

```text
Owner boundary (owner route group or equivalent Policy/Gate)
  → Middleware / Policy (HTTP redirect or authorize)
  → Model predicate (state / relationships)
  → Database

Service participates only when the operation is a substantive workflow
(e.g. agree to terms + audit + notify), not to answer hasAgreedToTerms().
```

### Good vs bad patterns

| Good | Bad |
|------|-----|
| `$user->hasAgreedToTerms()` on `User` | `OwnerTermsChecker` service whose only job is the same query |
| `TermsAgreementService::agree($user)` that writes agreement + audit + event | Service that only returns `$user->ownerTermsAgreement?->agreed_terms` |
| `owner.terms` / `owner.active` on an owner route group | Same middleware on all `admin/*` routes with early return for staff |
| `users.active` for account login eligibility | Also gating the same concern with `owners.status` |

## Consequences

- Staff roles never enter the owner middleware stack for staff/shared routes.
- When `owners` is imported in a later phase, omit `status` unless reference-app review proves a distinct lifecycle meaning (then rename; never a generic “active”). Flag for approval rather than silently dropping a distinct meaning.
- Real terms/property queries replace Phase 1 always-allow model stubs in their approved domain phases — **without** reintroducing checker services/contracts for those booleans.
- `users.active` (when added) remains **account eligibility** (auth) only. `hasActiveAccess()` remains **active property access** (computed). They intentionally represent different concepts.
- Contributors ask before adding a service: “Is this a model predicate?” and before attaching middleware: “Is this scoped to the smallest appropriate boundary?”
