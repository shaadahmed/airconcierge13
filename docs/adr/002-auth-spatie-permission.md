# ADR-002: Authorization — Entrust → Spatie Permission + Policies/Gates

**Status:** Accepted (implementation in Phase 1)  
**Date:** 2026-07-12

## Decision

Replace `zizaco/entrust` and the custom `OwnerAccessVerifier` with **Spatie Laravel Permission** plus Laravel **Policies/Gates**. Do not port `OwnerAccessVerifier` as-is.

## Alternatives considered

1. Keep Entrust (no maintained L13 path)
2. Spatie Permission + Policies/Gates (chosen)
3. Policies/Gates only without a roles package

## Rationale

Entrust is obsolete for modern Laravel. Spatie Permission is the community standard for roles/permissions on Laravel 13, pairs cleanly with Policies/Gates, and covers Property Owner / Regional Manager style roles used today. Implementation is deferred to Phase 1 (routing & authorization); Phase 0 only locks the direction.
