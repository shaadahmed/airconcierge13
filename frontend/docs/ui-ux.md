# UI / UX style rule (standing)

`nuxtjs-theme/` is a **reference-only** source for UI/UX style — colors, typography, spacing, component look-and-feel, and layout patterns.

It is **not** part of the runtime app. Nothing under `frontend/` may import from `nuxtjs-theme/`.

## Rules for every page and component

1. When converting or adding a view, **match the equivalent page/pattern in `nuxtjs-theme/`** where one exists (login, tables, form layouts, dashboard cards, etc.).
2. When no theme equivalent exists, still build with the **same** design tokens, spacing scale, component conventions, and visual language. Do not invent a parallel style.
3. No page or component may visually diverge from the established `nuxtjs-theme` style, whether or not a direct reference exists.

Check this rule in review for **every** new or converted page going forward.
