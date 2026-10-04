# ADR-021: SoftDeletes and standard timestamps on every domain table

**Status:** Accepted  
**Date:** 2026-10-04  
**Relates to:** [ADR-012](012-early-owners-properties-foundation.md), [ADR-016](016-structural-naming-and-migration-cleanup.md), [ADR-009](009-owner-access-predicates-and-active-flags.md)

## Decision

1. **Every new domain table migration** ends with exactly these three columns, in this order:
   - `created_at`
   - `updated_at`
   - `deleted_at`
2. Populate `created_at` / `updated_at` with Laravel’s `$table->timestamps()` (or equivalent). Populate `deleted_at` with `$table->softDeletes()`.
3. **Every Eloquent model** for those tables uses the `Illuminate\Database\Eloquent\SoftDeletes` trait.
4. **Do not** introduce legacy-style boolean soft-delete columns (`deleted`, `is_deleted`, etc.) or substitute `created_date` / `modified_date` for Laravel timestamps on new tables.
5. Soft-delete / restore in application code uses Eloquent `delete()` / `restore()` / `forceDelete()` (and `withTrashed()` / `onlyTrashed()` when needed). Domain predicates such as `Property::isLive()` treat a trashed row as not live (`!$this->trashed()`).

## Migration shape (required)

```php
Schema::create('example_things', function (Blueprint $table) {
    $table->id();
    // … domain columns …
    $table->timestamps();
    $table->softDeletes();
});
```

The last three physical columns on the table must be `created_at`, `updated_at`, `deleted_at`.

## Model shape (required)

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExampleThing extends Model
{
    use SoftDeletes;
}
```

Do not set `public $timestamps = false` on domain models covered by this ADR.

## Scope

- **Applies to:** all new application/domain tables and their Eloquent models (including pivot tables that are created as first-class tables in this app).
- **Exempt:** Laravel framework / infrastructure tables that are not domain models (`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, and similar). Do not retrofit SoftDeletes onto those unless a separate ADR says so.
- **Existing tables already migrated without `deleted_at`:** bring them into compliance with a dedicated alter migration (`$table->softDeletes()`) and add `SoftDeletes` on the model the next time that table is touched — or immediately when that domain is rebuilt. Prefer compliance before new feature work on that model.

## Legacy dump import

When importing Wave A / legacy SQL dumps:

- Map `created_date` (or equivalent) → `created_at`.
- Map `modified_date` / `updated_date` (or equivalent) → `updated_at`.
- Map boolean `deleted = 1` (or legacy soft-delete flags) → non-null `deleted_at` (use legacy modified time when available, otherwise a stable import timestamp).
- Map `deleted = 0` / null → `deleted_at = null`.
- Do **not** keep a parallel boolean `deleted` column on the L13 table.

## Alternatives considered

1. Keep legacy boolean `deleted` + `created_date` / `modified_date` for dump fidelity — rejected; greenfield L13 standardizes on SoftDeletes and timestamps.
2. SoftDeletes only on some aggregates — rejected; inconsistent querying and restore behavior across the app.
3. SoftDeletes without requiring column order — rejected; fixed trailing trio makes schema review and dumps predictable.

## Relationship to other ADRs

- **ADR-009 / ADR-012:** “Not deleted” for active property access means **not soft-deleted** (`deleted_at` is null / model not trashed), not a boolean `properties.deleted` column.
- **ADR-016:** Physical legacy names may still apply to *business* columns for dump fidelity. This ADR **overrides** that preference for soft-delete and timestamp columns: always `created_at`, `updated_at`, `deleted_at`.

## Consequences

- Properties and all subsequent domain modules use SoftDeletes; plans that assumed a boolean `deleted` column are superseded.
- List endpoints default to non-trashed rows; admin “show deleted / restore” flows use `onlyTrashed()` / `restore()`.
- Factories and Pest tests must account for SoftDeletes (trashed state via `$model->delete()`, not `deleted => true`).
- Contributors and agents follow this rule via `docs/AGENTS.md` and Cursor rules; do not reintroduce boolean soft-delete flags.
