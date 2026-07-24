# ADR-018: Nuxt SPA frontend (Sneat UI)

**Status:** Accepted  
**Date:** 2026-07-24

## Context

UI was served as minimal Blade views. A free Sneat Nuxt theme exists at `nuxtjs-theme/` (gitignored) as a visual reference. Spec Phase 5 remains an incremental Blade-refactor checklist and is **not** this work.

## Decision

1. The Air Concierge UI is a **Nuxt 3 SPA** under `frontend/`.
2. `nuxtjs-theme/` is **UI/UX style reference + one-time copy source only** — no product code, no runtime imports into `frontend/`.
3. Keep Laravel **`/admin/*` on `web.php`** for JSON; do not move SPA calls to `/api` in this cutover.
4. Auth: session cookies + **Laravel Sanctum** CSRF (`/sanctum/csrf-cookie`) with Nuxt dev proxy; production **same-origin**.
5. Frontend uses **services** + **Pinia stores**; all calculation/processing stays in Laravel.
6. DomPDF **Blade** templates under `resources/views/pdf/` remain for PDF generation.
7. Foundational `components/ui` Base* controls (including `BaseLink`, default same-tab) are required for converted pages.

## Standing UI/UX style rule

See also `frontend/docs/ui-ux.md`.

- Match `nuxtjs-theme/` equivalents when they exist.
- Otherwise stay within the same Sneat design language (tokens, spacing, conventions).
- No visual divergence from that style on any page going forward.

## Consequences

- Blade UI views are retired in favor of Nuxt pages with URL parity.
- CORS/`SANCTUM_STATEFUL_DOMAINS`/`FRONTEND_URL` must be configured for local Nuxt (`:3000`) and production host.
- Future UI work lands in `frontend/` only.
