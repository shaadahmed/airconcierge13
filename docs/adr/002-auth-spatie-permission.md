# ADR-002: Authorization — Entrust → Spatie Permission + Policies/Gates

**Status:** Superseded by [ADR-010](010-user-role-enum-policies.md)  
**Date:** 2026-07-12  
**Superseded:** 2026-07-22

## Decision (historical)

Replace `zizaco/entrust` and the custom `OwnerAccessVerifier` with **Spatie Laravel Permission** plus Laravel **Policies/Gates**. Do not port `OwnerAccessVerifier` as-is.

## Alternatives considered

1. Keep Entrust (no maintained L13 path)
2. Spatie Permission + Policies/Gates (chosen at the time)
3. Policies/Gates only without a roles package

## Rationale (historical)

Entrust is obsolete for modern Laravel. Spatie Permission was selected as the community standard for roles/permissions on Laravel 13.

## Follow-up (2026-07-22)

Owner terms / active-property access briefly used Spatie role names. **ADR-010** replaces Spatie with `UserRole` enum + `users.role` + Policies only (no Gates for role authZ).
