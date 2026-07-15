# Air Concierge 13 — Progress report

**Generated:** 2026-07-14  
**Branch:** `project-setup`  
**Verdict:** Not a bare `laravel new` scaffold. **Phase 0 (foundation) is largely complete.** No business modules have been ported yet (Phases 1–6 not started).

---

## Bottom line

| Question | Answer |
|----------|--------|
| Is this just a fresh Laravel app? | **No** — foundation, tooling, docs, and Sail/runtime are customized for the L5.1 → L13 migration. |
| Has domain code been migrated? | **No** — `app/` still has only the default Controller, User model, and AppServiceProvider. |
| Current phase | **Phase 0 complete** (per `docs/migration-plan.md` DoD). Awaiting explicit approval to start Phase 1. |

---

## What exists beyond a stock Laravel 13 skeleton

### Runtime & platform

- Laravel **13** (`laravel/framework ^13.8`), PHP **8.3+** / Sail image **8.4**
- Custom Sail stack in `compose.yaml`:
  - `laravel.test` (app)
  - **MySQL 8.4**, DB name **`airconcierge`**
  - **Redis** for queue, cache, and session
  - Dedicated **`queue`** service (`queue:work` on Redis)
  - Dedicated **`scheduler`** service
- Custom PHP 8.4 Docker build under `docker/8.4/`
- `.env.example` branded as **Air Concierge** with Redis/MySQL drivers set (no production secrets)

### Quality & agent tooling

| Item | Status |
|------|--------|
| Pest 4 | Present (`tests/Feature`, `tests/Unit` smoke examples) |
| Pint | Dev dependency |
| Larastan | Level **5** (`phpstan.neon.dist`) |
| GitHub Actions CI | `.github/workflows/ci.yml` — Pint, Larastan, Pest |
| Laravel Boost | Installed (`boost.json`, MCP config) |
| Cursor rules / skills | `.cursor/rules/migration.mdc`, Laravel + Pest skills |
| `AGENTS.md` | Migration + Boost agent guidelines |

### Documentation (migration program)

| Document | Role |
|----------|------|
| `docs/migration-plan.md` | Source of truth; Phase 0 DoD marked complete |
| `docs/migration-inventory.md` | Checklist of L5.1 controllers/routes/packages (no ports) |
| `docs/Laravel_5.1_to_13_Modernization_Spec.md` | Historical / requirements background |
| `docs/laravel-13-upgrade-cursor-prompt.md` | Working prompt |
| `docs/adr/001`–`006` | All planned Phase 0 ADRs present |

**ADRs on disk:**

1. Fresh L13 skeleton (not in-place upgrade)
2. Auth → Spatie Permission + Policies
3. Queues → Redis
4. Mail → Laravel Mailables
5. PDF spike (deferred decision)
6. Rollback = separate deploy; old XAMPP app stays live

### Schema staging

| Location | Count / note |
|----------|----------------|
| `database/migrations/` | **3** framework defaults only (users, cache, jobs) — boot path |
| `database/migrations_fresh/` | **101** files (`2026_07_11_*` … ending in `add_foreign_keys_to_fresh_schema`) |

**Note:** The migration plan previously said `migrations_fresh` had **not** been copied into this repo. On disk today those files **are present** under `database/migrations_fresh/`. They are **not** wired as the active `database/migrations/` set and, per plan, must **not** be run until explicitly approved. Inventory text still says they were absent at inventory time — treat that as stale relative to current disk.

---

## What is still fresh / not started

Application layer is still the stock greenfield tree:

```
app/
  Http/Controllers/Controller.php
  Models/User.php
  Providers/AppServiceProvider.php
```

**Missing vs target layering / Phase 1+:**

- No `Services/`, `Policies/`, `Jobs/`, Form Requests
- No `routes/api.php`; `web.php` is still the welcome route only
- No Spatie Permission (or other domain packages) in `composer.json` require
- No ported models, controllers, Blade business views, cron commands, or Hostaway/email integrations
- Per-module DoD and Phases 1–6 checkboxes remain open

---

## Git state (snapshot)

- One commit on history: `8d6ba91 first commit` (docs only at that point)
- Working tree on `project-setup`: large untracked Laravel tree + modified `.gitignore` / `migration-plan.md`
- Most of the Phase 0 app is **present on disk but not fully committed** — important if using git as the progress source of truth

---

## Phase map vs this repo

```text
Phase 0 Foundation          ← DONE (foundation only; no business port)
Phase 1 Routing & Auth      ← NOT STARTED
Phase 2 Hostaway + webhooks ← NOT STARTED
Phase 3 Email / Chronology  ← NOT STARTED
Phase 4 Bookings & Payments ← NOT STARTED
Phase 5 Reports & Dashboard ← NOT STARTED
Phase 6 Remaining + deploy  ← NOT STARTED
```

---

## Recommended next step

1. Commit / push the Phase 0 tree if git tracking should match disk.
2. Reconcile docs: update inventory / plan §14 regarding `database/migrations_fresh/` now on disk (still: do not run until approved).
3. Start **Phase 1** only after explicit approval (routing split, Spatie Permission, Policies, secure cron).

---

*Source of truth for sequencing remains [`migration-plan.md`](migration-plan.md).*
