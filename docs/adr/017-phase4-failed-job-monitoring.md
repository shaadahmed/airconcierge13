# ADR-017: Phase 4 failed-job monitoring (no Horizon)

**Status:** Accepted  
**Date:** 2026-07-24

## Decision

Phase 4 provides **lightweight failed-job monitoring** without installing Laravel Horizon:

1. Superadmin-only admin UI over the `failed_jobs` table (list / retry / forget / retry-all).
2. Job `failed()` handlers notify Slack via `laravel/slack-notification-channel` (bot token + channel in `config/services.php`).
3. Local/dev continues to use Sail `queue:work` (ADR-003). Production uses supervisor/systemd **templates** under `docs/deploy/`; live host verification remains Phase 6.

## Alternatives considered

1. Install Horizon in Phase 4 — rejected for this phase (explicit product choice; revisit after ops needs grow).
2. CLI-only (`queue:failed` / `queue:retry`) — insufficient vs Spec “retry dashboard”.
3. Lightweight UI + Slack — **chosen**.

## Consequences

- ADR-003 remains in force: Redis + plain `queue:work`; Horizon still deferred.
- Slack misconfiguration must not break job failure handling (log first; notify fail-soft).
- Phase 6 confirms workers/scheduler on staging/production hosts.
