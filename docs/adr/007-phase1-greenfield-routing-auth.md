# ADR-007: Phase 1 greenfield routing & auth (rewrite, not port)

**Status:** Accepted  
**Date:** 2026-07-15

## Decision

Phase 1 builds routing and authorization as a **greenfield Laravel 13 scaffold**. Do not split or port the legacy `routes.php`, Entrust middleware, or `OwnerAccessVerifier`. Use the legacy app only as a business-behavior oracle (role names, schedule intent, webhook URL preservation).

## Alternatives considered

1. Port the full ~978-line `routes.php` with stub controllers for every URI
2. Greenfield scaffold; grow routes with each domain phase (chosen)
3. Keep public `/cron/*` HTTP endpoints temporarily for cutover parity

## Rationale

Senior direction: pure rewrite with no technical baggage. Full route dumps force stubs that rot and reintroduce legacy structure. Spatie Permission + Policies/Gates with stubbed owner/terms checks, Schedule-only cron commands, and a Hostaway webhook placeholder (signature deferred from Phase 1 scaffold) meet Phase 1 DoD without copying legacy code or `migrations_fresh` tables.

## Follow-up (2026-07-19)

The modernization Spec assigns Hostaway **signature validation** to Phase 1. Phase 1 left it deferred. Execution ownership is now **Phase 1a** in `docs/migration-plan.md` (not Phase 2 service extraction).
