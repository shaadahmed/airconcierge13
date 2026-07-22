# ADR-010: UserRole enum + Policies (Spatie removed)

**Status:** Accepted  
**Date:** 2026-07-22  
**Supersedes:** [ADR-002](002-auth-spatie-permission.md)

## Decision

1. Represent each user’s single role as `users.role` (VARCHAR) cast to PHP backed enum `App\Enums\UserRole`.
2. Enum string values (snake_case): `superadmin`, `admin`, `manager`, `owner`, `cleaner`, `maintenance` — the only place role string literals are defined.
3. Role-based authorization uses **Laravel Policies** with `match` on `UserRole`. Do **not** use `Gate::define` for role checks. Do **not** use Spatie or other RBAC packages.
4. Column type is **string**, not MySQL ENUM (SQLite tests + easier role additions).

## Context

ADR-002 chose Spatie Permission. With a fixed set of ~6 roles and **role-level permissions only** (no per-user overrides), Spatie added tables and stringly-typed `hasRole('Property Owner')` calls without enough benefit. The lead required a `UserRole` enum and Policies.

## Alternatives considered

1. Spatie roles + enum wrappers (keep package)  
2. UserRole enum + Policies, remove Spatie (chosen)  
3. UserRole + Gates for non-model abilities  

## Consequences

- One role per user (`users.role`).
- Spatie migrations no-op’d / tables dropped; `spatie/laravel-permission` removed from Composer.
- Owner middleware identity filter: `$user->role === UserRole::Owner`.
- Call sites: `$this->authorize()` / `can:` → `UserPolicy`.
- Rename map from Spatie labels: Property Owner → `owner`, Regional Manager → `manager`.
