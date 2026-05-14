## Context

Currently the two controllers (`PublicApiController`, `MockControlController`) contain inline validation, manual JSON plucking, member resolution logic, and raw `JsonResponse` construction. There are no FormRequest classes, no API Resources, no traits. With 12 endpoints total (6 public, 4 mock, 2 inspection), the inline approach creates massive duplication and untestable controller methods.

## Goals / Non-Goals

**Goals:**
- Every endpoint uses a dedicated FormRequest for validation (zero validation in controllers)
- Response formatting uses API Resource classes with consistent `{ ok, ...data }` / `{ ok: false, error }` envelope
- Member resolution (userId → member → adminId) is a reusable trait used by all public endpoints
- Allocation validation is extracted into a custom Rule class reusable across createMember and setInvestmentProfile
- Controllers become thin orchestrators: resolve → call service → return resource

**Non-Goals:**
- Not implementing new endpoints (other changes handle that)
- Not changing the AdminApiInterface or MockAdminApi signatures
- Not introducing a service layer beyond what exists (AdminApiInterface is already the service boundary)
- Not changing route structure or HTTP contract shapes

## Decisions

### 1. FormRequest per endpoint, not per HTTP verb

Each endpoint gets its own FormRequest (e.g., `CreateMemberRequest`, `SetDailyUnitPricesRequest`). This keeps validation rules co-located with the endpoint they serve. FormRequests override `failedValidation()` to return the RPC-style `{ ok: false, error }` instead of Laravel's default 422 response.

### 2. Base ApiFormRequest class for shared RPC error format

All FormRequests extend a base `ApiFormRequest` that overrides `failedValidation()` to throw an `HttpResponseException` with `{ ok: false, error: <first error message> }`. This eliminates the need for try/catch or manual error formatting in controllers.

### 3. API Resources with RPC envelope

A base `ApiResource` handles the `{ ok: true, ...data }` wrapping. Endpoint-specific resources (e.g., `CreateMemberResource`) define only the data fields. Error responses use a simple `ApiErrorResponse` helper (not a Resource, since errors have no model).

### 4. ResolvesMember trait for public endpoints

A trait `ResolvesMember` provides a `resolveMember(string $userId, ?string $memberId = null, ?string $accountId = null): Member` method. Returns the member or throws an exception that the base FormRequest/controller catches. Used by all 6 public endpoints.

### 5. Custom Rule class for allocations

`App\Rules\ValidAllocations` implements Laravel's `Rule` interface. Validates: sum to 100.00, valid asset codes, no duplicates, max 2 decimal places. Used in both `CreateMemberRequest` and `SetInvestmentProfileRequest` via `new ValidAllocations`.

### 6. Trait for standardised error responses in controllers

A `RespondsWithJson` trait provides `okResponse(array $data)` and `errorResponse(string $message)` helpers, keeping even the response construction out of controller logic.

## Risks / Trade-offs

- [Risk] FormRequest `failedValidation` override changes error format globally → Mitigated by only applying to requests that extend our `ApiFormRequest` base class
- [Trade-off] More files vs simpler controllers → Worth it: each file has one job, easy to test and locate
- [Trade-off] Custom Rule class vs inline closure validators → Rule class is reusable and testable in isolation
- [Risk] Laravel's FormRequest auto-injection changes request type hints → Minimal risk; Laravel resolves FormRequests by type-hint automatically
