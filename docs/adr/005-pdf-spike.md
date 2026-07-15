# ADR-005: PDF generation — spike before first port

**Status:** Accepted (library choice deferred until spike)  
**Date:** 2026-07-12

## Decision

Do **not** commit to a PDF library in Phase 0. Before the first PDF-related port (reports, payments, payouts), run a short spike comparing:

1. DomPDF (`barryvdh/laravel-dompdf` or equivalent)
2. Browsershot (Chromium/Puppeteer)
3. A maintained wkhtmltopdf wrapper (legacy path today: `niklasravnsborg/laravel-pdf` + binaries)

Record the chosen library in a follow-up ADR or an update to this file after the spike.

## Alternatives considered

Listed above; none selected yet.

## Rationale

The old stack depends on wkhtmltopdf binaries that are fragile across OS/Sail images. PDF quality, CSS support, and operational cost differ enough that picking without a spike risks rework in Phases 4–6. Phase 0 only requires documenting candidates and the spike gate.
