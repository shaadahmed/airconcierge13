# ADR-001: Fresh Laravel 13 skeleton vs incremental upgrade

**Status:** Accepted  
**Date:** 2026-07-12

## Decision

Create a **fresh Laravel 13 application** in `airconcierge13` and port behavior domain-by-domain from the Laravel 5.1 app.

## Alternatives considered

1. Incremental in-place upgrade (5.1 → 5.2 → … → 13)
2. Fresh L13 skeleton + domain port (chosen)

## Rationale

The jump from Laravel 5.1 to 13 is not practical as a continuous upgrade on the existing tree (routing, middleware, auth, package ecosystem, PHP constraints). A greenfield skeleton becomes the source of truth, preserves the old XAMPP app as the live system until cutover, and allows intentional layering (services, form requests, policies, jobs) instead of carrying forward fat controllers and global helpers.
