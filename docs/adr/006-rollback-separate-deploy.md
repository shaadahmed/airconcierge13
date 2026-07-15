# ADR-006: Rollback — old app stays live; new app is a separate deploy

**Status:** Accepted  
**Date:** 2026-07-12

## Decision

Until an explicit staging/production cutover is approved:

- The Laravel 5.1 XAMPP app at `/mnt/e/xampp/htdocs/airconcierge` remains the **production system of record** and stays untouched.
- `airconcierge13` is a **separate deploy**. Rollback means leave the new deploy unused or take it down; the old app continues to serve traffic.
- No production cutover and no shared live-DB cutover happen in Phase 0.

## Alternatives considered

1. In-place replace of the XAMPP app
2. Parallel deploy with old app as rollback (chosen)
3. Shared live database cutover during foundation work

## Rationale

A parallel deploy minimizes blast radius during a multi-phase migration. Keeping the old app live preserves a known-good rollback path without requiring blue/green infrastructure on day one.
