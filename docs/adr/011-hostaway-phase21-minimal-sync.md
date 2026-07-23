# ADR-011: Phase 2.1 minimal Hostaway sync (deferred booking mutation)

**Status:** Accepted  
**Date:** 2026-07-23  
**Relates to:** [ADR-008](008-hostaway-webhook-basic-auth.md), [ADR-009](009-owner-access-predicates-and-active-flags.md), [ADR-003](003-queues-redis.md)

## Decision

1. **Phase 2.1 owns** the Hostaway HTTP API client (`HostawayService`), OAuth token persistence (`hostaway_access_tokens`), webhook→queue orchestration, and idempotent `hostaway_reservation_logs` writes.
2. **Booking create / update / cancel are deferred** to Phase 2.3 (Bookings) once properties/platforms/bookings schema and services exist. Sync stubs record the appropriate `*_IN_PROGRESS` log status with an explicit deferral comment — they must **not** mark `STATUS_PROCESSED` (or other success statuses) as if a booking were created.
3. **Access tokens refresh on demand** when missing or expired. Do **not** refresh tokens in a service provider `boot()` (legacy pattern) — that side effect breaks tests/CI and couples boot to the network.
4. **Webhook HTTP contract unchanged:** `POST /wh/hostaway/booking/created`, Basic Auth (ADR-008), fast **200** after dispatching `SyncHostawayReservationJob`.

## Alternatives considered

1. Full Hostaway→booking sync in Phase 2.1 (pull Bookings/Properties early) — rejected; blurs 2.1 vs 2.3 and requires large schema approval  
2. Minimal API + logs + deferred mutation stubs (chosen)  
3. Leave `SyncHostawayReservationJob::handle()` empty until Bookings land — rejected; Spec/plan require job→service wiring and idempotency tests in 2.1  

## Rationale

Legacy Hostaway create/modify/cancel delegates into fat booking controllers and depends on properties, platforms, guests, and finance math. Those domains are not live in L13 yet. Porting the API client and durable ingest/idempotency now unblocks Phase 2.1 exit criteria without inventing booking behavior or silently changing Hostaway contracts.

## Consequences

- Only `hostaway_access_tokens` and `hostaway_reservation_logs` are copied from `migrations_fresh` for this slice; `booking_id` stays nullable **without** a foreign key until bookings exist.
- `HostawayReservationSyncService` routes webhook statuses (`new`, `pending`+`paid`, `modified`, `cancelled`) and stubs mutation paths.
- Admin Hostaway logs UI, reviews cron, and photo sync remain out of Phase 2.1.
- Phase 4 still owns job hardening (retries dashboard / monitoring); the job remains `ShouldQueue` on Redis.
