# ADR-005: PDF generation — DomPDF

**Status:** Accepted  
**Date:** 2026-07-12  
**Updated:** 2026-07-23

## Decision

Use **`barryvdh/laravel-dompdf`** for PDF generation.

Spike result: DomPDF is Sail-friendly (no Chromium/Puppeteer, no wkhtmltopdf OS binaries). Payment receipt PDFs are a future Phase 4 consumer; Phase 2.3 only records `payment_receipts.receipt_path` and ships a stub `GeneratePdfJob` plus thin `App\Services\Pdf\PdfService`. Controllers must not generate PDFs yet.

## Alternatives considered

1. DomPDF (`barryvdh/laravel-dompdf`) — **chosen**
2. Browsershot (Chromium/Puppeteer) — rejected (heavy Sail/runtime deps)
3. wkhtmltopdf wrappers (legacy `niklasravnsborg/laravel-pdf`) — rejected (fragile binaries across OS/Sail images)

## Rationale

The old stack depends on wkhtmltopdf binaries that are fragile across OS/Sail images. DomPDF keeps PDF work inside PHP Composer packages and is adequate for receipt-style HTML→PDF in Phase 4.
