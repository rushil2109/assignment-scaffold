## ADDED Requirements

### Requirement: MockAdminApi implements AdminApiInterface
The MockAdminApi class SHALL implement all 6 methods of AdminApiInterface using Eloquent models backed by MySQL.

#### Scenario: Interface contract fulfilled
- **WHEN** MockAdminApi is instantiated
- **THEN** it satisfies the AdminApiInterface type contract

### Requirement: createMember generates adminId
MockAdminApi::createMember SHALL generate a UUID adminId, persist the member and their account, and return both IDs.

#### Scenario: New member creation
- **WHEN** createMember is called with member data
- **THEN** a member row and account row are created with generated UUIDs

### Requirement: setInvestmentProfile is append-only
MockAdminApi::setInvestmentProfile SHALL mark all existing is_current=true rows for the account as is_current=false, then insert new rows with is_current=true and the given effective_from date.

#### Scenario: Profile replacement
- **WHEN** setInvestmentProfile is called for an account that already has a profile
- **THEN** the old profile rows have is_current=false and new rows have is_current=true

#### Scenario: First profile
- **WHEN** setInvestmentProfile is called for an account with no prior profile
- **THEN** new rows are inserted with is_current=true

### Requirement: Service container binding
The service container SHALL bind AdminApiInterface to MockAdminApi as a singleton in AppServiceProvider::register().

#### Scenario: Interface injection resolves to mock
- **WHEN** a class type-hints AdminApiInterface in its constructor
- **THEN** the container injects the MockAdminApi singleton instance

#### Scenario: Concrete injection for mock controller
- **WHEN** a class type-hints MockAdminApi directly
- **THEN** the container resolves the same singleton instance
