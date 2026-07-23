# ADR-014: Phase 2.5 remaining package decisions

**Status:** Accepted  
**Date:** 2026-07-23

## Decision

| Legacy package | Decision |
|----------------|----------|
| `nao-pon/flysystem-google-drive` | Defer until Google Drive consumer command is implemented; then Flysystem v3 adapter |
| `vinkla/hashids` | Not required yet — use integer IDs; revisit if obfuscated public IDs are needed |
| `sammyk/laravel-facebook-sdk` | Remove / do not port — no active Facebook integration in L13 scope |
| `flynsarmy/csv-seeder` | Replace with `ImportService` + Artisan/one-off commands (done for owners/properties/guests) |
| `doctrine/dbal` | Not added; Laravel 13 schema builder covers current alters |
| `filp/whoops` | Not needed — framework error handling |
| Yajra DataTables | Remain Phase 5 unless a report is blocked (reports currently return JSON/Blade summaries) |

## Consequences

- Dropbox / cloud backup / metrics scheduled commands remain wired; domain logic can grow inside existing command classes without new HTTP cron routes.
- Image compression stays as `images:compress-uploads` preparing Phase 4 batch jobs.
