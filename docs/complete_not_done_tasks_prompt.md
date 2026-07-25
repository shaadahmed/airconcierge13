# Prompt: Complete Not Done migration checklist tasks

Copy everything below the line into a new agent/chat session when ready to implement. Do **not** expand scope beyond the listed open items unless the user explicitly asks.

---

## Objective

Close the remaining **Not Done** (and related open checklist) items identified in `docs/phase_wise_completion_report.md` and the open `[ ]` tasks in `docs/migration-plan.md` (Phase 2.4, Phase 2.5, Phase 4 follow-ups, §11 per-module DoD gaps).

Preserve business behavior. Follow `docs/AGENTS.md`, ADR-009, ADR-010, ADR-019. Controllers stay thin; validation in Form Requests; authorization in dedicated Policies (`UserRole` match); no new global helpers; no silent bug-fixes.

## Source of open items

- `docs/migration-plan.md` — Phase 2.4 tasks, Phase 2.5 Policy tasks, Phase 4 Deviations / follow-ups, §11 open DoD rows
- `docs/phase_wise_completion_report.md` — evidence and Not Done classifications
- Patterns to mirror: existing Form Requests under `app/Http/Requests/Admin/`, Policies under `app/Policies/`, route `can:` / `authorizeResource` (ADR-019)

## Tasks (in order)

### 1. Report / Dashboard Form Requests (Phase 2.4 — Not Done)

- Add Form Request classes for non-trivial `ReportController` and `DashboardController` query/body inputs (replace raw `Illuminate\Http\Request` validation needs).
- Wire them into the controllers.
- Keep request/response contracts unchanged unless a bug is documented and approved.
- Add or extend Pest feature coverage for at least one critical report path and one dashboard path.

### 2. Dedicated Policies (Phase 2.4 + 2.5 — Not Done)

Create and register Policies (via `AuthServiceProvider` / auto-discovery as appropriate):

- `ReportPolicy`
- `DashboardPolicy`
- `DocumentPolicy`
- `ImportPolicy` (or equivalent for import endpoints / models used)
- `PropertyPolicy`

Then:

- Replace `BookingPolicy::viewAny` / `can:viewAny,Booking` stand-ins on Document, Import, Property, Report, and Dashboard routes/Form Requests with the correct policy abilities.
- Match existing staff-role semantics (do **not** silently widen or narrow access). Prefer copying the current Booking stand-in outcome into the new policies first, then tighten only if product/docs already require it.
- Follow ADR-019 (`authorizeResource` / route `can` middleware) where resourceful; custom actions keep explicit `can:` middleware or Form Request `authorize()`.

### 3. Phase 4 open follow-ups (Not Done / stubbed domain bodies)

Do **not** invent legacy behavior. Port from `legacy/` (business behavior only) or document deferral with approval if parity cannot be verified.

1. **Google Drive Flysystem adapter** — install/wire adapter when environment allows; keep `UploadBackupToCloudJob` + disk config; preserve local-backup fallback until adapter works (ADR-014).
2. **`AlertDispatchService`** — implement remaining alert keys beyond `booking-conflict` from legacy cron alert logic; leave explicit follow-up comments only where behavior is unclear (wait for approval).
3. **`DropboxFormService`** — implement `processCsv` / `syncDatabase` / `updateStatuses` domain bodies (not log-only stubs).
4. **Owners-payout domain body** — only if still stubbed behind the Phase 4 pipeline; otherwise skip and note N/A in the plan checklist.

### 4. Docs checklist sync (required wrap-up)

After code lands:

- Flip the corresponding `[ ]` → `[x]` in `docs/migration-plan.md` (Phase 2.4, 2.5, Phase 4 follow-ups, §11 as applicable).
- Do **not** edit Spec checkboxes unless the user asks.
- Briefly note any approved deferrals that remain.

## Constraints

- Read-only on the reference/legacy app; never edit `legacy/` or the XAMPP tree.
- No new dependencies without approval (Drive adapter is the known exception already approved in ADR-014).
- Run Pint on dirty PHP; add Pest tests for new Policy/Form Request paths.
- Use Sail for artisan/composer/test commands when executing.
- Do not start Phase 6 deployment work in this pass.

## Acceptance criteria

- [ ] Report/Dashboard endpoints use Form Requests (no raw Request for validated inputs).
- [ ] Document/Import/Property/Report/Dashboard use dedicated Policies (no Booking stand-in).
- [ ] Phase 4 follow-up items either implemented with tests/notes or explicitly re-deferred with plan wording.
- [ ] `docs/migration-plan.md` open checklists for these items updated to match reality.
- [ ] Existing Pest suite still green for touched areas.
