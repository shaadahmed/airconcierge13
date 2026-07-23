# ADR-013: Zoho Sign is the primary e-sign integration

**Status:** Accepted  
**Date:** 2026-07-23  
**Relates to:** [ADR-004](004-mail-laravel-mailables.md)

## Decision

1. **Zoho Sign** is the live outbound e-sign path for Chronology and Send Email flows (`ZohoSignService`).
2. Do **not** invest in a HelloSign SDK package for new sends. Legacy HelloSign send call sites are commented out in the reference app.
3. Retain the `hellosigndetails` table name for dump compatibility; rows store Zoho request/document fields.
4. Historical HelloSign poll leftovers remain out of scope unless Wave A data proves open HelloSign requests still need completion.

## Alternatives considered

1. Port HelloSign + Zoho dual stack — rejected; dead send path and package cost  
2. Zoho-only for new sends (chosen)  
3. Defer all e-sign until later — rejected; Spec Phase 2.2 requires sign service extraction

## Consequences

- Config keys live under `services.zoho.*` / `ZOHO_*` env vars.
- `ProcessSignatureRequestJob` and `zoho:poll-completions` own completion polling; Phase 4 hardens retries/monitoring.
- Spec wording “ZohoSignService / HelloSignService” is satisfied by ZohoSignService; HelloSignService is explicitly deferred.
