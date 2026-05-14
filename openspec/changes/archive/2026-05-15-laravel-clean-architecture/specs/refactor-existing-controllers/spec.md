## ADDED Requirements

### Requirement: PublicApiController uses FormRequests
Every method on PublicApiController SHALL type-hint its corresponding FormRequest class instead of `Illuminate\Http\Request`. The controller SHALL contain zero validation logic.

#### Scenario: createMember uses CreateMemberRequest
- **WHEN** POST /public/createMember is called
- **THEN** `CreateMemberRequest` is injected and validated before the method body runs

#### Scenario: Invalid request never reaches controller
- **WHEN** an invalid payload is sent
- **THEN** the FormRequest returns the error response; the controller method is never invoked

### Requirement: PublicApiController uses ResolvesMember trait
PublicApiController SHALL `use ResolvesMember` and call `resolveAdminId()` / `resolveMember()` for member lookups instead of inline queries.

#### Scenario: Member not found via trait
- **WHEN** resolveMember returns null
- **THEN** the controller returns `ApiErrorResponse::make('Member not found')`

### Requirement: PublicApiController returns Resources
All successful responses from PublicApiController SHALL return an API Resource instance, not a raw `new JsonResponse(...)`.

#### Scenario: createMember success
- **WHEN** createMember succeeds
- **THEN** it returns `new CreateMemberResource([...])` which serializes to `{ ok: true, memberId, accountId, operationId }`

### Requirement: MockControlController uses FormRequests
Every method on MockControlController SHALL type-hint its corresponding FormRequest class. Zero validation in controller body.

#### Scenario: addTransactions uses AddTransactionsRequest
- **WHEN** POST /mock/addTransactions is called
- **THEN** `AddTransactionsRequest` is injected and validated before the method body runs

### Requirement: MockControlController returns consistent responses
MockControlController SHALL return `JsonResponse` with the canonical shape (`{ ok: true, addedCount }` or `{ ok: true }`) but MAY use a simple response helper instead of a full Resource class for simple shapes.

#### Scenario: addTransactions response
- **WHEN** addTransactions succeeds
- **THEN** response is `{ "ok": true, "addedCount": N }`

### Requirement: Controller methods are thin
No controller method SHALL exceed ~15 lines of body logic (excluding blank lines). Business logic lives in the AdminApiInterface/AuditService; validation lives in FormRequests; formatting lives in Resources.

#### Scenario: createMember method body
- **WHEN** the createMember method is reviewed
- **THEN** it contains: idempotency check, admin API call, audit call, return resource — no manual validation or response formatting
