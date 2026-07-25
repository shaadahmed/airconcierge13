# Air Concierge (Laravel 13)

Fresh Laravel 13 port of the Air Concierge application. See `docs/AGENTS.md`, `docs/migration-plan.md`, and `docs/Laravel_5.1_to_13_Modernization_Spec.md` for migration context.

## Local setup (plug-and-play)

Requirements: Docker Desktop (or compatible engine) and Git. PHP/Composer/Node on the host are optional — Sail runs them in containers.

1. Clone the repo and copy env:

```bash
cp .env.example .env
```

2. Start the stack (compose lives under `docker/`; the root file is a thin include so no `-f` flag is needed):

```bash
docker compose up -d --build
```

Or via Sail after Composer install inside the container on first boot:

```bash
docker compose run --rm laravel.test composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

Compose layout:

- `compose.yaml` — root pointer (`include: docker/compose.yaml`)
- `docker/compose.yaml` — Sail services (app, queue, scheduler, MySQL, Redis, Mailpit)
- `docker/8.4/` — PHP 8.4 Dockerfile and container assets

Default app URL: `http://localhost` (override with `APP_PORT`). Mailpit UI: `http://localhost:8025`.

## Quality gates

- Pint: `vendor/bin/sail bin pint --test`
- Larastan **level 8**: `vendor/bin/sail bin phpstan analyse --memory-limit=1G`
- Pest: `vendor/bin/sail artisan test --compact`

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Frontend

Nuxt SPA work lives on the `staging` / `nuxtjs` branches under `frontend/`. Remaining Blade under `resources/views` is for email/PDF templates only.
