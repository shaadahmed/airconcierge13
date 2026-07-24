# Air Concierge frontend (Nuxt 3)

Nuxt SPA for Air Concierge admin UI. Laravel remains the API/auth/domain backend (`/admin/*`, session + Sanctum CSRF).

## Setup

```bash
cd frontend
cp .env.example .env   # NUXT_LARAVEL_URL=http://localhost:8080
pnpm install           # or npm install
pnpm dev               # http://localhost:3000
```

Ensure Sail Laravel is up on port **8080**. Dev proxy forwards `/sanctum`, `/login`, `/logout`, `/admin` to Laravel.

## Architecture

- `pages/` — routes (URL parity with former Blade paths)
- `components/ui/` — BaseButton, BaseInput, BaseTextarea, BaseSelect, BaseCheckbox, BaseRadio, BaseLink
- `services/` — HTTP wrappers only (no business logic)
- `stores/` — Pinia display state
- `middleware/` — `auth`, `guest`

## Links

Use `BaseLink`. Default is same-tab SPA navigation. Set `newTab` only when explicitly required.

## Theme

See [docs/ui-ux.md](docs/ui-ux.md). Do **not** import from `nuxtjs-theme/`.

## Auth

1. `GET /sanctum/csrf-cookie`
2. `POST /login` with credentials
3. Session cookie + `X-XSRF-TOKEN` on subsequent `/admin/*` calls
