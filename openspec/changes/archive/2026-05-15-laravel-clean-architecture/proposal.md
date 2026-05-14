## Why

Controllers currently contain all validation, business logic, response formatting, and member resolution inline. This makes them hard to test in isolation, violates single responsibility, and doesn't leverage Laravel's built-in patterns (FormRequest, API Resources, Traits). As we add 9 more endpoints, this approach won't scale — each controller method will balloon with duplicated validation and resolution logic.

## What Changes

- Introduce **FormRequest** classes for every endpoint — validation moves out of controllers entirely
- Introduce **API Resource** classes for standardised response formatting (camelCase, consistent shape)
- Introduce a **ResolvesMember trait** for the repeated userId + memberId + accountId → member → adminId lookup pattern
- Introduce a **ValidatesAllocations trait** (or rule class) for investment profile allocation validation reuse
- Refactor existing `PublicApiController::createMember` and `MockControlController` methods to use these patterns
- Establish the pattern for all future endpoints (update, profile, reads, inspection, mock-control)

## Capabilities

### New Capabilities
- `form-request-classes`: FormRequest validation classes for all 12 endpoints with canonical contract field names and rules
- `api-resource-classes`: JsonResource/ResourceCollection classes for standardised response envelopes (ok/error pattern)
- `controller-traits`: Reusable traits for member resolution and allocation validation
- `refactor-existing-controllers`: Rewrite existing controllers to use the new patterns (thin controller, fat model/service)

### Modified Capabilities

## Impact

- `super-admin/app/Http/Requests/` — new directory with 12 FormRequest classes
- `super-admin/app/Http/Resources/` — new directory with response resource classes
- `super-admin/app/Http/Controllers/PublicApiController.php` — slimmed down, uses FormRequests + traits
- `super-admin/app/Http/Controllers/MockControlController.php` — slimmed down, uses FormRequests
- `super-admin/app/Traits/` — new directory for ResolvesMember, ValidatesAllocations
- `super-admin/app/Rules/` — optional custom validation rules (e.g., AllocationsSumTo100)
