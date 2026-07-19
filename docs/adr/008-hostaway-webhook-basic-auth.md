# ADR-008: Hostaway webhook authentication — Basic Auth (not HMAC)

**Status:** Accepted (implemented in Phase 1a)  
**Date:** 2026-07-19

## Decision

Satisfy the Spec Phase 1 requirement to “validate signatures” on Hostaway webhooks by verifying **Hostaway-native HTTP Basic Auth** (login + password on the `Authorization` header). Do **not** invent a custom HMAC/shared-secret signature scheme.

Invalid or missing credentials return **401** and do **not** dispatch `SyncHostawayReservationJob`. Credentials are read from `config/services.php` (`HOSTAWAY_WEBHOOK_USERNAME` / `HOSTAWAY_WEBHOOK_PASSWORD`). Fail closed when credentials are empty.

## Alternatives considered

1. Custom HMAC / “signing secret” header (implied by Spec/plan wording) — not provided by Hostaway
2. Hostaway optional Basic Auth login/password on webhook delivery (chosen)
3. Leave webhook open until Phase 2.1 — rejected; Spec assigns auth to Phase 1 / Phase 1a

## Rationale

Hostaway’s documented webhook security is optional username/password sent as Basic Auth. The legacy app did not authenticate webhooks. Interpreting Spec “signature validation” as Basic Auth verification meets the security intent without inventing a non-portable protocol or changing Hostaway API contracts. Path `POST /wh/hostaway/booking/created` remains unchanged (ADR-007).
