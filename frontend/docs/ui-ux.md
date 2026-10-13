# UI / UX style rule (standing)

`nuxtjs-theme/` is a **reference-only** source for UI/UX style — typography, spacing, component look-and-feel, and layout patterns.

It is **not** part of the runtime app. Nothing under `frontend/` may import from `nuxtjs-theme/`.

## Color palette (ADR-022)

Use only these five normal/light pairs site-wide. Source: `frontend/constants/uiColors.js` and Vuetify theme tokens.

| # | Role | Normal | Light |
|---|------|--------|-------|
| 1 | Primary | `#6865FF` | `#E7E6FF` |
| 2 | Secondary (owner block) | `#6c757d` | `#DFE2E3` |
| 3 | Success (guest booking / Save) | `#16A34A` | `#DCFCE7` |
| 4 | Warning (cancelled booking) | `#D97706` | `#FEF3C7` |
| 5 | Error (Cancel / Delete) | `#DC3545` | `#FDF2F2` |

Do not invent extra accents (cyan info, platform brand flags, one-off oranges). Prefer theme role names or shared constants over raw hex in pages.

Canonical write-up: [`docs/adr/022-ui-color-palette.md`](../../docs/adr/022-ui-color-palette.md).

## Rules for every page and component

1. When converting or adding a view, **match the equivalent page/pattern in `nuxtjs-theme/`** where one exists (login, tables, form layouts, dashboard cards, etc.), **except** colors — colors follow ADR-022.
2. When no theme equivalent exists, still build with the **same** spacing scale, component conventions, and visual language. Do not invent a parallel style.
3. No page or component may visually diverge from the established layout language or the ADR-022 palette.

Check this rule in review for **every** new or converted page going forward.
