# ADR-022: Canonical UI color palette (normal + light)

**Status:** Accepted  
**Date:** 2026-10-13  
**Relates to:** [ADR-018](018-nuxt-spa-frontend.md)

## Decision

The Nuxt admin UI uses a **fixed five-color palette**. Every accent, status, chart segment, chip, flag, icon tone, and button color must come from these pairs (normal for solid fills / icons / text emphasis; light for tonal backgrounds / soft chips / donut segments).

Do **not** introduce ad-hoc hexes, platform brand colors, or extra rainbow accents outside this set.

### Palette

| # | Role (Vuetify / product) | Normal | Light |
|---|--------------------------|--------|-------|
| 1 | Primary (Create / Add New, brand accent) | `#6865FF` | `#E7E6FF` |
| 2 | Secondary (Owner block, neutral chrome) | `#6c757d` | `#DFE2E3` |
| 3 | Success (Save, guest booking) | `#16A34A` | `#DCFCE7` |
| 4 | Warning (attention, cancelled booking) | `#D97706` | `#FEF3C7` |
| 5 | Error / danger (Cancel / Delete outline, destructive) | `#DC3545` | `#FDF2F2` |

### Product mappings

| Use | Color | Variant |
|-----|-------|---------|
| Create / Add New buttons | 1 Primary | Normal fill, white text |
| Save / Update buttons | 3 Success | Normal fill, white text |
| Cancel / Delete buttons | 5 Error | Light/white fill, normal border + text (`outlined`) |
| Guest booking (flag, chip, calendar, icon) | 3 Success | Normal (solid); Light for soft chips if needed |
| Owner block (flag, chip, calendar, icon) | 2 Secondary | Normal (solid); Light for soft chips if needed |
| Cancelled booking (flag, chip, icon) | 4 Warning | Normal (solid); Light for soft chips if needed |
| Cancelled booking (calendar bar / legend) | 5 Error | Light |
| Post-stay cleaning donut — Yes | 3 Success | Light |
| Post-stay cleaning donut — No | 2 Secondary | Light |

### Implementation source of truth

- Constants: `frontend/constants/uiColors.js`
- Vuetify theme tokens: `frontend/plugins/vuetify/theme.js` (`primary`, `secondary`, `success`, `warning`, `error` + `*-light`)
- Standing UI note: `frontend/docs/ui-ux.md`

Prefer theme role names (`primary`, `success`, …) or the shared constants — never copy raw hex into page CSS unless matching an existing constant.

## Context

Clients rejected pages that mixed many unrelated accents (platform brand flags, orange owner-block chrome, cyan info tiles, etc.). A small palette keeps calendar legend, list flags, chips, charts, and buttons consistent.

## Alternatives considered

1. Keep Sneat/Vuexy default multicolor tokens and only restyle a few screens  
2. Adopt this five-pair palette app-wide (chosen)  
3. Allow unlimited “semantic” extras (info cyan, platform brands, brown cancelled) — rejected for consistency  

## Consequences

- New UI work must use only these five normal/light pairs.
- Booking status colors supersede earlier legend browns/emeralds that sat outside this set.
- Charts and soft chips use **light** variants; solid flags/icons/buttons use **normal** variants.
- Agents and reviewers treat one-off hexes as a defect unless added to this ADR.
