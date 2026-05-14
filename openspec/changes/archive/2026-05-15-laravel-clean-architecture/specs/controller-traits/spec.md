## ADDED Requirements

### Requirement: ResolvesMember trait
The system SHALL provide `App\Traits\ResolvesMember` with a method `resolveMember(string $userId, ?string $memberId = null, ?string $accountId = null): Member` that looks up the member by user_id, optionally verifies memberId and accountId match, and returns the Member model or null.

#### Scenario: Valid resolution with all IDs
- **WHEN** resolveMember is called with userId, memberId, and accountId that all match
- **THEN** the Member model is returned

#### Scenario: userId not found
- **WHEN** resolveMember is called with a userId that has no member
- **THEN** null is returned

#### Scenario: memberId mismatch
- **WHEN** resolveMember is called with a memberId that doesn't match the found member
- **THEN** null is returned

#### Scenario: accountId mismatch
- **WHEN** resolveMember is called with an accountId that doesn't match the member's account
- **THEN** null is returned

### Requirement: ResolvesMember provides adminId
The trait SHALL also provide `resolveAdminId(string $userId, ?string $memberId = null, ?string $accountId = null): ?string` that returns the admin_id for the resolved member, or null if resolution fails.

#### Scenario: Get adminId for valid member
- **WHEN** resolveAdminId is called with valid IDs
- **THEN** the member's admin_id string is returned

### Requirement: ValidAllocations custom Rule
The system SHALL provide `App\Rules\ValidAllocations` implementing `Illuminate\Contracts\Validation\ValidationRule` that validates an array of allocations: each must have assetCode (in valid set) and percentage, no duplicate asset codes, percentages max 2 decimal places, and sum to exactly 100.00.

#### Scenario: Valid allocations pass
- **WHEN** allocations sum to 100.00 with valid codes and no duplicates
- **THEN** validation passes

#### Scenario: Sum not 100
- **WHEN** allocations sum to 99.99
- **THEN** validation fails with descriptive message

#### Scenario: Invalid asset code
- **WHEN** an allocation has assetCode "FooBar"
- **THEN** validation fails with descriptive message

#### Scenario: Duplicate asset code
- **WHEN** two allocations use "Cash"
- **THEN** validation fails with descriptive message

#### Scenario: Too many decimal places
- **WHEN** a percentage is 33.333
- **THEN** validation fails with descriptive message
