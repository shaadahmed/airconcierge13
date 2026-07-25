# ADR-019: Centralized authorization and FormRequest validation

**Status:** Accepted  
**Date:** 2026-07-25  
**Related:** [ADR-010](010-user-role-enum-policies.md)

## Decision

1. Controllers must not call `$this->authorize()` inside individual action methods.
2. For standard resourceful controllers, call `$this->authorizeResource(Model::class, 'param')` once in the constructor (requires the base `Controller` to extend `Illuminate\Routing\Controller`).
3. For custom / non-CRUD actions (or dual-model controllers), attach Laravel’s `can` middleware on the specific route in `routes/web.php` (or `routes/api.php`).
4. Do not mix both patterns on the same action. A controller may use `authorizeResource` for CRUD and `can` middleware for its custom endpoints.
5. Validation lives exclusively in Form Request classes. Action methods type-hint the FormRequest; no inline `$request->validate()` / manual `Validator::make`.
6. FormRequest `authorize()` performs the policy check for mutating actions that use that request. Do not also call `$this->authorize()` in the controller for the same action.

## Context

Lead review asked for thinner controllers: move authorization to `authorizeResource` / route `can` middleware, and move validation into Form Requests (example: `StoreDocumentRequest`).

## Consequences

- New controllers follow the same pattern; Blade/API response shapes stay unchanged.
- Payment dual-model surfaces and Booking-proxy auth (documents/properties) stay on `can` middleware rather than inventing extra policies in this pass.
