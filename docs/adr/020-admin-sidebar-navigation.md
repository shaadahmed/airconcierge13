# ADR-020: Admin Sidebar Navigation Pattern

**Status:** Accepted  
**Date:** 2026-07-27

## Context

ADR-018 established the Nuxt SPA as the sole interactive admin UI with a static `NavItems.vue` containing a flat array of 14 links. The sidebar groups visible in the legacy app (Dashboard, Properties, Financials, Staff, Reporting, Administration) were not ported as grouped navigation.

The legacy app sources non-Administration sidebar groups dynamically from a `resources` database table via a `render_menu()` helper (filtering `deleted = 0`, ordered by `order`, grouped by `parent_id`). The Administration group is **not** in `resources`; it is hardcoded in `admin-partials/sidebar.blade.php` under `@role(['superadmin', 'admin'])` with item-level `@role('superadmin')` guards.

A request was made to port full sidebar parity, building every navigation item as a working Nuxt page backed by a Laravel `/admin/*` JSON endpoint.

## Decision

1. **Navigation API endpoint:** A new `GET /admin/navigation` endpoint (`NavigationController`) reads `resources` rows grouped by `parent_id`, ordered by `order`, excluding `deleted = 1`, maps icon classes to `boxicons` equivalents, and appends the hardcoded Administration group from `config/admin_navigation.php`. Nuxt fetches this once per session on mount via `navigationService.list()`.

2. **Resources table in live migrations:** The `resources` table schema (from `migrations_fresh`) is copied to `database/migrations/` and seeded by `ResourceSeeder`. This is the authoritative navigation data source for non-Administration groups; it must not be confused with authorization — it is display/routing metadata only.

3. **Administration group source:** The Administration group is defined in `config/admin_navigation.php` because it has never been stored in `resources` in the legacy system and is role-controlled at a finer granularity (some items superadmin-only, some shared admin). Role filtering is applied in `AdminNavigationService::administrationGroup()` using `UserRole` enum values.

4. **Sidebar rendering:** `NavItems.vue` is replaced with a Pinia-free dynamic implementation that fetches groups from the navigation service and renders `VerticalNavGroup` / `VerticalNavLink` pairs. Active group detection uses `route.path.startsWith(child.to)`.

5. **Existing off-menu routes are preserved:** Chronologies, Documents, Imports, Zoho, Failed Jobs, Terms, and Owner Statements remain functional at their existing paths. They are not included in the new grouped sidebar but can be accessed directly or re-added later.

6. **Page parity scope:** All 40 sidebar items receive corresponding Nuxt pages under `frontend/pages/admin/**` and Laravel JSON endpoints under `routes/web.php`. New pages follow the existing properties/reports pattern: `adminEntityService.js` + `useAdminEntitiesStore` (or existing domain services/stores for already-implemented domains).

## Consequences

- `NavItems.vue` now performs an authenticated API call on mount; the sidebar is empty on login until the navigation request resolves.
- Adding or reordering non-Administration sidebar items requires seeding `resources` (not code changes).
- Adding or reordering Administration items still requires a code change in `config/admin_navigation.php` and may eventually justify moving Administration to the `resources` table (deferred).
- `VerticalNavGroup` gained a `defaultOpen` prop so the active group can be pre-expanded from the navigation API response.
- The `resources` table migration is now live; it must not be confused with role-permission management — authorization remains in `Policies` + `UserRole` (ADR-010).

## Files changed

- `database/migrations/2026_07_27_120000_create_resources_table.php` (from fresh)
- `database/seeders/ResourceSeeder.php`
- `app/Models/Resource.php`
- `config/admin_navigation.php`
- `app/Services/Navigation/AdminNavigationService.php`
- `app/Http/Controllers/Admin/NavigationController.php`
- `frontend/layouts/components/NavItems.vue`
- `frontend/services/navigationService.js`
- `frontend/@layouts/components/VerticalNavGroup.vue` (`defaultOpen` prop)
- `routes/web.php` (navigation route + all new admin routes)
- `app/Http/Controllers/Admin/*.php` (21 new controllers)
- `app/Policies/*.php` (new policies for all new domains)
- `app/Providers/AuthServiceProvider.php` (new policy registrations)
- `frontend/pages/admin/**/*.vue` (40+ new pages)
- `frontend/components/admin/AdminEntityCrud.vue`
- `frontend/components/admin/AdminEndpointTable.vue`
- `frontend/services/adminEntityService.js`
- `frontend/stores/adminEntities.js`
