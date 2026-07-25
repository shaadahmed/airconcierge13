# SPA production hosting (Nuxt + Laravel)

**Audience:** Ops / Phase 6 deployment  
**Related:** ADR-018, `docs/frontend-report.md`, Phase 6 checklist in `docs/migration-plan.md`

## Goal

Serve the Nuxt SPA and Laravel API on the **same origin** so Sanctum session cookies work without fragile cross-site CORS.

## Preferred: reverse proxy (same origin)

Example layout:

| Path | Upstream |
|------|----------|
| `/sanctum/*`, `/login`, `/logout`, `/admin/*`, `/wh/*`, `/chronology/*` | Laravel (Sail/`php-fpm`) |
| Everything else (`/`, `/admin/dashboard` SPA routes, assets) | Nuxt Node server **or** static `nuxt generate` output |

### Env (production)

- `APP_URL=https://app.example.com`
- `FRONTEND_URL=https://app.example.com` (same host)
- `SANCTUM_STATEFUL_DOMAINS=app.example.com`
- `SESSION_DOMAIN=.example.com` only if you intentionally share across subdomains (usually leave unset for same host)

### Nginx sketch

```nginx
server {
    server_name app.example.com;

    location ~ ^/(sanctum|login|logout|admin|wh|chronology) {
        try_files $uri @laravel;
    }

    location / {
        # Option A: proxy to `nuxt start` / Node
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location @laravel {
        # standard Laravel public/index.php pass-through
    }
}
```

Exact Laravel `try_files` / PHP-FPM blocks should match your Phase 6 host templates.

## Alternative: static SPA in `public/`

1. `cd frontend && npm ci && npm run generate` (or `build` + copy `.output/public`).
2. Place generated assets under Laravel `public/` (or a dedicated `public/spa/` with history fallback).
3. Add a catch-all route that serves `index.html` for non-API GET paths **without** shadowing `/admin/*` JSON (Laravel owns those routes first).

History fallback must not intercept authenticated JSON routes.

## Local vs production

| Env | Approach |
|-----|----------|
| Local | Nuxt `:3000` + Vite/Nitro proxy to Sail (`NUXT_LARAVEL_URL`) |
| Production | Same-origin reverse proxy (preferred) |

## Checklist before cutover

- [ ] `SANCTUM_STATEFUL_DOMAINS` matches the browser host
- [ ] CSRF cookie path works (`GET /sanctum/csrf-cookie`)
- [ ] Login/logout JSON still works from the SPA
- [ ] PDF download/export routes still hit Laravel
- [ ] No dual hosting of Blade UI for cut-over surfaces
