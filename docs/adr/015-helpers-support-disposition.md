# ADR-015: Helpers disposition and App\Support conventions

**Status:** Accepted  
**Date:** 2026-07-23  
**Relates to:** [ADR-001](001-fresh-laravel-13-skeleton.md), [ADR-004](004-mail-laravel-mailables.md), [ADR-009](009-owner-access-predicates-and-active-flags.md)

## Context

Legacy `app/helpers.php` has ~181 global functions (~3.2k lines). Spec Phase 3 requires auditing them and eliminating the Composer `files` autoload entry. The L13 tree never imported that file (no `helpers.php`, no `files` autoload). Phase 3 therefore cannot “delete a live autoload”; it must **disposition** every function and implement only the shared pure utilities needed now.

Spec wording “Business rules → Services” is coarser than ADR-009: predicates belong on models; multi-step workflows belong in services.

## Decision

1. **No global helpers** and no Composer `files` autoload for helper libraries — ever.
2. **`App\Support\*`** holds **pure** utilities only (date/money/string formatting and math with no Eloquent, Auth, or HTTP). Classes are `final`; methods are explicit and typed.
3. **Disposition vocabulary** (every legacy function in `docs/migration-inventory.md`):
   - `implemented` — Support/Model/Service home exists in L13
   - `superseded` — already replaced by Phase 1–2 code (document the replacement)
   - `deferred` — destination named; implement when Phase 4/5 (or a domain PR) needs it
   - `delete-candidate` — obsolete pattern (e.g. curl-to-legacy-cron); do not re-port without approval
4. **Do not** put HTML `list_*` option builders in Support — Phase 5 View/Blade.
5. **Do not** put Eloquent domain gates (e.g. `isMonthClosed`) in `DateFormatter` / `DateMath`.
6. **Do not** reintroduce PHPMailer or hardcoded SMTP from `sendSmtpEmail` — `EmailService` + Laravel Mail (ADR-004).
7. **Do not** recreate `hasActiveProperty` as a global or thin service — `User::hasActiveAccess()` (ADR-009).

## Alternatives considered

1. Port all ~181 helpers as unused Support methods now — rejected (speculative surface, YAGNI)
2. Inventory-only with no Support classes — rejected (Spec names `Support\DateFormatter`; critical formatters are needed soon)
3. Inventory + critical Support homes + deferred dispositions (chosen)

## Consequences

- Phase 3 exit criteria are met by full disposition + Support implementations + confirmed absent autoload — not by dumping every helper into L13.
- Deferred helpers must name a destination (service/model/view phase); “deferred” is an approved non-implementation for Phase 3, not silent deletion of business rules.
- Future phases prefer calling `App\Support\...` or domain Services instead of inventing new globals.
