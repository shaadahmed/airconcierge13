# Frontend report (Nuxt SPA)

**Status:** Complete for ADR-018 remaining work (2026-07-25)  
**Not Spec Blade Phase 5:** Interactive UI is Nuxt; Spec Blade/Yajra checklist closed via ADR-014 + migration-plan Phase 5 rewrite.

## Summary

Laravel remains the API/auth/domain layer. The Nuxt 3 SPA under `frontend/` owns interactive admin UI (Sneat / Vuetify). Sanctum CSRF + session auth; `/admin/*` JSON on `web.php` (no `/api` move). PDF generation stays DomPDF Blade under `resources/views/pdf/`.

## Shipped surfaces

| Area | Nuxt | Notes |
|------|------|-------|
| Home / login / dashboard | Yes | Dashboard stats + Apex revenue chart |
| Bookings, payments, property-payments | Yes | Shared table shells |
| Chronologies, send-emails, reports, zoho, failed-jobs | Yes | |
| Properties, documents, imports | Yes | Against existing JSON CRUD |
| Owner terms | Yes | `dynamic_content` + agree/disagree + logout on decline |
| Owner statements | Yes | P&amp;L from available booking columns; schema gaps flagged in UI |
| PDF | Blade only | `payment-receipt`, `report`, `owner-terms-agreement` |

## Shared UI

`PageHeader`, `EmptyState`, `DataTableShell`, `ConfirmDialog`, `AppAlert` + Base* primitives.

## CI / hosting

- CI: `.github/workflows/ci.yml` frontend job — `npm ci` + `npm run lint`
- Production same-origin: `docs/deploy/spa-hosting.md`

## Follow-up cards (do not silently “fix”)

1. Staff commission dashboard / filtered AjaxDashboard headline KPIs / full performance-chart suite
2. Expand bookings schema for remaining legacy P&amp;L line items (`daily_utility_fee`, deposits, cleaners, etc.)
3. Owner terms CMS edit / preview / reset-all agreements
4. Tighten `UserPolicy` audience for terms/statements if product requires owner-only
5. Optional `vue-tsc` typecheck wiring

## Acceptance

- [x] Cut-over + polish complete for shipped admin pages
- [x] No unused `UpgradeToPro` / demo `frontend/views` cruft
- [x] Yajra declined (ADR-014)
- [x] No wholesale L5.1 Blade port
- [x] Docs updated (plan, ADRs, this report, deploy SPA hosting)
