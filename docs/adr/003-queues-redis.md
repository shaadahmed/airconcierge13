# ADR-003: Queues — Redis via Sail (Horizon deferred)

**Status:** Accepted  
**Date:** 2026-07-12

## Decision

Use **Redis** as the queue (and cache/session) driver via Laravel Sail. Run workers with plain `php artisan queue:work`. **Do not** install Laravel Horizon in Phase 0.

## Alternatives considered

1. Database / sync queues (legacy default — underused)
2. Redis + `queue:work` via Sail (chosen for Phase 0)
3. Redis + Horizon from day one

## Rationale

Redis matches the long-term target in the migration plan and supports async email, Hostaway sync, PDF, and imports in later phases. Horizon adds dashboard/ops value but is unnecessary for a greenfield foundation; revisit after meaningful job volume exists.
