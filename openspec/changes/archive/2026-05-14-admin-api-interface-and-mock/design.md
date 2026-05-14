## Context

The architecture requires a PHP interface (not HTTP) separating the public API from vendor logic. Public controllers inject `AdminApiInterface`; mock controllers inject `MockAdminApi` directly. The mock is the only implementation for this exercise but the interface must remain implementation-agnostic.

## Goals / Non-Goals

**Goals:**
- Clean interface contract with typed parameters and return arrays/objects
- MockAdminApi that uses Eloquent models to persist to MySQL
- Service container binding so DI works transparently for both injection paths

**Non-Goals:**
- HTTP-based admin API (the boundary is in-process PHP)
- DTOs or value objects (plain arrays suffice for exercise scope)
- Real vendor implementation

## Decisions

**Interface methods accept adminId, not userId**
The admin boundary knows nothing about platform user IDs. Translation happens in the public API layer. Rationale: clean separation — the vendor system has its own identifier namespace.

**Return arrays rather than DTOs**
Methods return associative arrays matching the contract shapes. Rationale: avoids premature abstraction; the contract is already defined by the API spec.

**Singleton binding**
`MockAdminApi` is bound as singleton because it's stateless (all state is in MySQL). Rationale: avoids repeated instantiation, consistent with Laravel conventions for service classes.

**AppServiceProvider for binding**
The binding goes in `AppServiceProvider::register()`. No dedicated provider needed for a single binding. Rationale: simplicity.

## Risks / Trade-offs

- [Arrays over DTOs] → Less type safety at interface boundary. Acceptable for exercise scope.
- [Singleton] → If MockAdminApi ever holds request-scoped state, this breaks. Mitigated: all state is in DB.
