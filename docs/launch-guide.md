# Air Concierge — Local launch guide

Run the Laravel 13 API + Nuxt 3 admin SPA locally and open it in a browser.

| Piece | Role | Default URL |
|-------|------|-------------|
| **Nuxt** (`frontend/`) | Interactive admin UI — use this every day | [http://localhost:3000](http://localhost:3000) |
| **Laravel** (Sail) | API, auth, domain logic | [http://localhost:8080](http://localhost:8080) |
| **Mailpit** | Outbound mail UI | [http://localhost:8025](http://localhost:8025) (or your `FORWARD_MAILPIT_DASHBOARD_PORT`) |

```text
Browser  →  Nuxt :3000  →  (proxy)  →  Laravel :8080  →  MySQL + Redis
```

You do **not** need PHP or Composer on the host — Sail runs them inside Docker.

---

## Prerequisites

| Tool | Notes |
|------|--------|
| **Docker Desktop** | Must be running before any Sail/compose command. On Windows, WSL2 backend is recommended. |
| **Node.js** | 18+ on the host (for Nuxt). |
| **Git** | Project clone. Git Bash or WSL helps if Sail scripts fail in PowerShell. |

---

## First-time setup

Do this once after cloning (or after deleting `vendor/` / wiping the database).

Commands below use **PowerShell** at the project root. On bash / Git Bash, use `cp` instead of `Copy-Item` and `./vendor/bin/sail` instead of `.\vendor\bin\sail`.

### Step 1 — Open a terminal at the project root

```text
E:\airconcierge13
```

(Use your actual clone path.)

### Step 2 — Create Laravel `.env`

```powershell
Copy-Item .env.example .env
```

Skip if `.env` already exists.

### Step 3 — Set ports and CORS-related env

`.env.example` already points Laravel at **8080** and the SPA at **3000**. Confirm these values in the root `.env` (add `APP_PORT` if missing — Sail defaults to port 80 without it):

```env
APP_URL=http://localhost:8080
APP_PORT=8080
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:8080,127.0.0.1,127.0.0.1:3000,127.0.0.1:8080
```

If host ports clash (common on Windows), set forwarded ports in the same `.env`:

```env
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
FORWARD_MAILPIT_PORT=1026
FORWARD_MAILPIT_DASHBOARD_PORT=8026
```

Leave Hostaway / Zoho / Slack / Google Drive keys empty unless you are testing those integrations.

### Step 4 — Install PHP dependencies

```powershell
docker compose run --rm laravel.test composer install
```

This creates `vendor/` (including `vendor/bin/sail`).

### Step 5 — Start Sail

```powershell
.\vendor\bin\sail up -d
```

If that fails on Windows PowerShell, try:

```powershell
bash vendor/bin/sail up -d
```

or:

```powershell
docker compose up -d
```

Sail starts:

- App (`laravel.test`) → host **8080** (when `APP_PORT=8080`)
- MySQL (`airconcierge`)
- Redis (session, cache, queue)
- Queue worker / scheduler (if defined in compose)
- Mailpit → dashboard on **8025** by default, or your `FORWARD_MAILPIT_DASHBOARD_PORT`

### Step 6 — Generate app key, migrate, and seed

Always use **Sail** for Artisan — not host `php` (this project needs PHP ≥ 8.4.1 inside the container):

```powershell
.\vendor\bin\sail artisan key:generate
.\vendor\bin\sail artisan migrate
.\vendor\bin\sail artisan db:seed
```

### Step 7 — Note the local login

| Field | Value |
|-------|--------|
| Email | `test@example.com` |
| Password | `password` |
| Role | `superadmin` |

Local/dev only.

### Step 8 — Set up the Nuxt frontend

In a **second** terminal:

```powershell
cd frontend
Copy-Item .env.example .env
npm install
```

Confirm `frontend/.env` contains:

```env
NUXT_LARAVEL_URL=http://localhost:8080
```

(`npm install` also runs icon generation via `postinstall`.)

### Step 9 — Start Nuxt

Still in `frontend/`:

```powershell
npm run dev
```

Nuxt proxies `/sanctum`, `/login`, `/logout`, and `/admin` to Laravel.

### Step 10 — Open the app and log in

1. Open [http://localhost:3000](http://localhost:3000)
2. Sign in with `test@example.com` / `password`
3. You should land on the admin dashboard (`/admin/dashboard`)

---

## Daily launch (after first setup)

### Terminal 1 — Laravel / Sail

```powershell
cd E:\airconcierge13
.\vendor\bin\sail up -d
```

### Terminal 2 — Nuxt

```powershell
cd E:\airconcierge13\frontend
npm run dev
```

### Browser

| What | URL |
|------|-----|
| **Admin UI (preferred)** | http://localhost:3000 |
| Laravel API (direct) | http://localhost:8080 |
| Mailpit | http://localhost:8025 (or your forwarded dashboard port) |
| Adminer (optional; only while its container is running) | http://localhost:8082 |

Login: `test@example.com` / `password`

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

## Browse the database

There is no built-in phpMyAdmin. Use a desktop client (recommended) or a temporary Adminer container. Sail must be running (`mysql` healthy).

### Connection settings (desktop client → host)

| Field | Value |
|-------|--------|
| Host / hostname | `127.0.0.1` |
| Port | `3306` by default, or your `FORWARD_DB_PORT` (e.g. `3307`) |
| Database | `airconcierge` |
| Username | `sail` |
| Password | `password` |

Use the values from your root `.env` if you changed `DB_*` or `FORWARD_DB_PORT`.

### HeidiSQL (Windows example)

1. Install if needed: `winget install --id HeidiSQL.HeidiSQL -e`
2. New session → **MySQL (TCP/IP)**
3. Enter the settings above → **Open**
4. Select the `airconcierge` database

### Temporary Adminer (browser UI, no repo changes)

Throwaway container — does **not** edit compose or project files.

**Start:**

```powershell
docker run --rm -d --name airconcierge-adminer -p 8082:8080 --network airconcierge13_sail adminer
```

**Open:** [http://localhost:8082](http://localhost:8082)

| Field | Value |
|-------|--------|
| System | **MySQL** |
| Server | **mysql** (Docker service name on the Sail network — not `127.0.0.1`) |
| Username | `sail` |
| Password | `password` |
| Database | `airconcierge` |

**Stop:**

```powershell
docker stop airconcierge-adminer
```

If the network name differs, run `docker network ls` and look for a `*sail*` network for this project.

### CLI alternative

```powershell
docker compose exec -T mysql mysql -usail -ppassword airconcierge
```

If the `users` table is empty, re-seed via Sail: `.\vendor\bin\sail artisan db:seed`.

---

## Optional checks

```powershell
.\vendor\bin\sail artisan about
.\vendor\bin\sail artisan route:list --path=login
.\vendor\bin\sail artisan test --compact
```

Quality gates (also used in CI):

```powershell
.\vendor\bin\sail bin pint --test
.\vendor\bin\sail bin phpstan analyse --memory-limit=1G
.\vendor\bin\sail artisan test --compact
```

---

## Troubleshooting

| Symptom | What to do |
|---------|------------|
| Redis / session 500s | Sail is down or Redis not ready — run `sail up -d` and wait. |
| Login or CSRF fails from `:3000` | Laravel must match Nuxt’s proxy target. Keep `APP_PORT`, `APP_URL`, and `NUXT_LARAVEL_URL` in sync (default **8080**), then restart Sail and Nuxt. |
| Port 80, 8080, or 3306 already in use | Change `APP_PORT` and/or `FORWARD_DB_PORT` in `.env`; update `APP_URL` and `NUXT_LARAVEL_URL` to match. |
| `vendor/bin/sail` missing | Run `docker compose run --rm laravel.test composer install`. |
| Cannot log in / no users | Re-run `.\vendor\bin\sail artisan db:seed`. Do **not** use host `php artisan`. |
| `PHP version ">= 8.4.1"` / platform_check | You ran Artisan on the host. Use Sail: `.\vendor\bin\sail artisan …` |
| Cannot connect HeidiSQL / desktop client | Sail MySQL must be up; use `FORWARD_DB_PORT` (often **3307**), host `127.0.0.1`. |
| Adminer “could not connect” | Use server name **`mysql`**, not `127.0.0.1`; ensure `--network` matches this project’s Sail network. |
| Docker / compose errors | Start Docker Desktop and wait until it is fully running. |
| Vite / asset errors on Laravel-only pages | Usually irrelevant for Nuxt UI; for Blade/PDF assets: `.\vendor\bin\sail npm run build` if needed. |
| Nuxt 500: `[ERR_LOAD_URL] @/plugins/iconify/icons.css` | Generated file missing. From `frontend/`: `npm run build:icons`, then restart `npm run dev`. |
| Nuxt 500 / “Page not found: /dashboard” | Use `/admin/dashboard` (or open `/` — it redirects). Guests go to `/login`. |

---

## Architecture reminder

- Controllers / auth / domain logic: Laravel
- Interactive admin screens: Nuxt under `frontend/`
- Remaining Blade under `resources/views`: email / PDF templates only

More detail: [`technical-documentation.md`](technical-documentation.md), [`user-documentation.md`](user-documentation.md), root [`README.md`](../README.md).
