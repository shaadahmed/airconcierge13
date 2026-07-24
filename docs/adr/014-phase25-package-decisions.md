# ADR-014: Phase 2.5 remaining package decisions

**Status:** Accepted  
**Date:** 2026-07-23  
**Updated:** 2026-07-24

## Decision

| Legacy package | Decision |
|----------------|----------|
| `nao-pon/flysystem-google-drive` | Phase 4: `UploadBackupToCloudJob` + `gdrive` disk config are ready. Installing `masbug/flysystem-google-drive-ext` timed out in Sail (`google/apiclient-services` extract). **Keep local-disk backup** until the adapter is installed on a host with adequate Composer timeout; `AppServiceProvider` registers the `google` driver only when the adapter class exists. |
| `vinkla/hashids` | Not required yet — use integer IDs; revisit if obfuscated public IDs are needed |
| `sammyk/laravel-facebook-sdk` | Remove / do not port — no active Facebook integration in L13 scope |
| `flynsarmy/csv-seeder` | Replace with `ImportService` + Artisan/one-off commands (done for owners/properties/guests) |
| `doctrine/dbal` | Not added; Laravel 13 schema builder covers current alters |
| `filp/whoops` | Not needed — framework error handling |
| Yajra DataTables | Remain Phase 5 unless a report is blocked (reports currently return JSON/Blade summaries) |

## Consequences

- Dropbox / cloud backup / metrics scheduled commands dispatch Phase 4 jobs.
- Image compression uses `images:compress-uploads` dispatching per-batch `CompressImagesBatchJob`.
- Without Drive credentials/package, weekly backups remain on the local disk and log a warning (not a silent no-op).
