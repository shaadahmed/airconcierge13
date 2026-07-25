# Air Concierge — Local launch guide

Step-by-step instructions to run the Laravel 13 API + Nuxt 3 admin SPA locally and open it in a browser.

**Use this URL for day-to-day checking:** [http://localhost:3000](http://localhost:3000)

Laravel is the API/auth backend. Nuxt is the interactive UI.

**Status key (this machine, as of last update):**

- ✅ = already done
- ⬜ = still needs to be done

---

## Your current checklist

| Step | Status | Notes |
|------|--------|--------|
| Prerequisites (Docker / Node / Git) | ✅ | Assumed available — Docker is running |
| Laravel `.env` exists | ✅ | Copied / present at project root |
| `APP_URL=http://localhost:8080` | ✅ | Set in `.env` |
| `APP_PORT=8080` | ✅ | Set in `.env` |
| `FRONTEND_URL=http://localhost:3000` | ✅ | Set in `.env` |
| `SANCTUM_STATEFUL_DOMAINS` (incl. `:3000` / `:8080`) | ✅ | Set in `.env` |
| Forwarded host ports (DB/Redis/Mailpit) | ✅ | `3307` / `6380` / `8026` — avoids local conflicts |
| `APP_KEY` generated | ✅ | Present in `.env` |
| `vendor/` + Sail installed | ✅ | `vendor/bin/sail` present |
| Sail stack running | ✅ | App, MySQL, Redis, scheduler, Mailpit up; Laravel on **8080** |
| Migrations run | ✅ | All live migrations Ran |
| Database seeded (`test@example.com`) | ⬜ | User **not** in DB yet — run seed |
| `frontend/.env` | ⬜ | Missing — copy from `.env.example` |
| `frontend/node_modules` | ✅ | Already installed |
| Nuxt `npm run dev` | ⬜ | Start after creating `frontend/.env` |
| Open UI + log in | ⬜ | After seed + Nuxt |

---

## Prerequisites

| Tool | Notes |
|------|--------|
| **Docker Desktop** | Must be running. On Windows, WSL2 backend is recommended. |
| **Node.js** | 18+ on the host (for Nuxt). |
| **Git** | Project clone. Git Bash or WSL helps if Sail scripts fail in PowerShell. |

You do **not** need PHP or Composer on the host — Sail runs them inside Docker.

---

## First-time setup

Do this once after cloning (or after deleting `vendor/` / wiping the DB).

### 1. Open a terminal at the project root — ✅

```text
E:\airconcierge13
```

### 2. Create Laravel `.env` — ✅

Already present. (For a fresh clone only:)

**PowerShell:**

```powershell
Copy-Item .env.example .env
```

**bash / Git Bash:**

```bash
cp .env.example .env
```

### 3. Set the app port and related env — ✅

Already set in your `.env`:

```env
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:3000
APP_PORT=8080
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:8080,127.0.0.1,127.0.0.1:3000,127.0.0.1:8080
```

Your forwarded ports (also ✅):

```env
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
FORWARD_MAILPIT_PORT=1026
FORWARD_MAILPIT_DASHBOARD_PORT=8026
```

Leave Hostaway / Zoho / Slack / Google Drive keys empty unless you are testing those integrations.

### 4. Install PHP dependencies — ✅

`vendor/` is present. (Only if missing:)

```powershell
docker compose run --rm laravel.test composer install
```

### 5. Start Sail — ✅ (currently running)

Already up. To start again later:

```powershell
.\vendor\bin\sail up -d
```

If that fails on Windows PowerShell, try one of:

```powershell
bash vendor/bin/sail up -d
```

```powershell
docker compose up -d
```

Sail starts:

- App (`laravel.test`) → host **8080**
- MySQL (`airconcierge`)
- Redis (session, cache, queue)
- Queue worker (`queue:work`) — if defined in compose
- Scheduler (`schedule:work`)
- Mailpit → dashboard **http://localhost:8026** (your forwarded port)

### 6. Generate app key, migrate, and seed

| Sub-step | Status |
|----------|--------|
| `key:generate` | ✅ |
| `migrate` | ✅ |
| `db:seed` | ⬜ **Do this next** |

Run seed only:

```powershell
docker compose exec -T laravel.test php artisan db:seed
```

Or via Sail (Git Bash / WSL):

```bash
./vendor/bin/sail artisan db:seed
```

### 7. Seed login (local QA only) — ⬜ after step 6 seed

| Field | Value |
|-------|--------|
| Email | `test@example.com` |
| Password | `password` |
| Role | `superadmin` |

Use only on local/dev.

### 8. Start the Nuxt frontend — ⬜ partly done

| Sub-step | Status |
|----------|--------|
| `frontend/.env` | ⬜ |
| `npm install` | ✅ |
| `npm run dev` | ⬜ |

In a **second** terminal:

```powershell
cd frontend
Copy-Item .env.example .env
npm run dev
```

(`npm install` already done; re-run only if `node_modules` is missing.)

Frontend `.env` must be:

```env
NUXT_LARAVEL_URL=http://localhost:8080
```

Nuxt proxies `/sanctum`, `/login`, `/logout`, and `/admin` to Laravel.

---

## What you still need to do (short path)

1. ⬜ Seed the DB:
   ```powershell
   docker compose exec -T laravel.test php artisan db:seed
   ```
2. ⬜ Create frontend env and start Nuxt:
   ```powershell
   cd frontend
   Copy-Item .env.example .env
   npm run dev
   ```
3. ⬜ Open http://localhost:3000 and log in with `test@example.com` / `password`

---

## Daily launch (after first setup)

### Terminal 1 — Laravel / Sail — ✅ right now

```powershell
cd E:\airconcierge13
.\vendor\bin\sail up -d
```

### Terminal 2 — Nuxt — ⬜

```powershell
cd E:\airconcierge13\frontend
npm run dev
```

### Open in the browser — ⬜

| What | URL |
|------|-----|
| **Admin UI (preferred)** | http://localhost:3000 |
| Laravel API (direct) | http://localhost:8080 |
| Mailpit (outbound mail UI) | http://localhost:8026 |

1. Open http://localhost:3000  
2. Sign in with `test@example.com` / `password`  
3. You should land on the admin dashboard  

---

## Stop everything

1. In the Nuxt terminal: `Ctrl+C`
2. Stop Sail:

```powershell
cd E:\airconcierge13
.\vendor\bin\sail stop
```

Or:

```powershell
docker compose stop
```

---

## Optional checks

```powershell
docker compose exec -T laravel.test php artisan about
docker compose exec -T laravel.test php artisan route:list --path=login
docker compose exec -T laravel.test php artisan test --compact
```

Quality gates (also used in CI):

```powershell
docker compose exec -T laravel.test ./vendor/bin/pint --test
docker compose exec -T laravel.test ./vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec -T laravel.test php artisan test --compact
```

---

## Troubleshooting

| Symptom | What to do |
|---------|------------|
| Redis / session 500s | Sail is down or Redis not ready — run `sail up -d` and wait. |
| Login or CSRF fails from `:3000` | Laravel must be on **8080**. Set `APP_PORT=8080`, keep `APP_URL` / `NUXT_LARAVEL_URL` in sync, restart Sail and Nuxt. |
| Port 80, 8080, or 3306 already in use | Change `APP_PORT` and/or `FORWARD_DB_PORT` in `.env`; update `APP_URL` and `NUXT_LARAVEL_URL` to match. |
| `vendor/bin/sail` missing | Run `docker compose run --rm laravel.test composer install`. |
| Cannot log in / no users | Run `php artisan db:seed` (seed user was missing on this machine). |
| Docker / compose errors | Start Docker Desktop and wait until it is fully running. |
| Vite / asset errors on Laravel-only pages | Usually irrelevant for Nuxt UI; for Blade/PDF assets: `sail npm run build` if needed. |

---

## Architecture reminder (local)

```text
Browser  →  Nuxt :3000  →  (proxy)  →  Laravel :8080  →  MySQL + Redis
```

- Controllers / auth / domain logic: Laravel  
- Interactive admin screens: Nuxt under `frontend/`  
- Remaining Blade under `resources/views`: email / PDF templates only  

More detail: [`technical-documentation.md`](technical-documentation.md), [`user-documentation.md`](user-documentation.md), root [`README.md`](../README.md).
