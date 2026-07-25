# Contributing

## Static analysis (Larastan)

Before committing PHP changes, run Larastan at **level 8**:

```bash
vendor/bin/sail bin phpstan analyse --memory-limit=1G
# or, inside the Sail container / with PHP 8.4+:
vendor/bin/phpstan analyse --memory-limit=1G
```

Configuration: `phpstan.neon.dist` (includes Larastan + Carbon extensions, scans `app/`).

If analysis reports existing debt that is out of scope for the current change, regenerate the baseline rather than lowering the level:

```bash
vendor/bin/phpstan analyse --memory-limit=1G --generate-baseline=phpstan-baseline.neon
```

Then include the baseline from `phpstan.neon.dist`. CI runs the same `phpstan analyse` command and must stay green.

## Style

```bash
vendor/bin/sail bin pint --dirty
```

## Tests

```bash
vendor/bin/sail artisan test --compact
```

## Docker / Sail

Compose source of truth: `docker/compose.yaml`. Root `compose.yaml` only includes that file so `docker compose up` and `./vendor/bin/sail up` work from a fresh clone with no extra flags. See the README “Local setup” section.
