## ADDED Requirements

### Requirement: createMember endpoint accepts POST with JSON body
The system SHALL expose `POST /public/createMember` accepting a JSON body with fields: userId (string, required), email (string, optional), mobile (string, optional), preferredName (string, optional), residentialAddress (object, optional), postalAddress (object, optional), investmentProfile (array of {assetCode, percentage}, required).

#### Scenario: Valid request
- **WHEN** a POST is made to /public/createMember with valid userId and investmentProfile
- **THEN** the system returns `{ ok: true, memberId, accountId, operationId }` with HTTP 200

#### Scenario: Missing required fields
- **WHEN** a POST is made without userId or without investmentProfile
- **THEN** the system returns `{ ok: false, error: "..." }` with HTTP 200

### Requirement: Investment profile validation
The system SHALL validate that investmentProfile allocations sum to exactly 100.00, use only valid asset codes (Cash, Conservative, Balanced, Growth, HighGrowth), contain no duplicate asset codes, and percentages have at most 2 decimal places.

#### Scenario: Valid allocations
- **WHEN** allocations sum to 100.00 with valid codes and no duplicates
- **THEN** validation passes and member creation proceeds

#### Scenario: Allocations don't sum to 100
- **WHEN** allocations sum to 99.99 or 100.01
- **THEN** the system returns `{ ok: false, error: "..." }`

#### Scenario: Invalid asset code
- **WHEN** an allocation uses an asset code not in the valid set
- **THEN** the system returns `{ ok: false, error: "..." }`

#### Scenario: Duplicate asset code
- **WHEN** two allocations use the same asset code
- **THEN** the system returns `{ ok: false, error: "..." }`

#### Scenario: Percentage exceeds 2 decimal places
- **WHEN** an allocation has percentage like 33.333
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: Idempotency on userId
The system SHALL return existing memberId, accountId, and the original operationId when createMember is called with a userId that already exists. No new audit entry is created.

#### Scenario: Duplicate userId
- **WHEN** createMember is called twice with the same userId
- **THEN** the second call returns the same memberId and accountId as the first, with ok: true

### Requirement: ID mapping hides adminId
The system SHALL never expose adminId in public API responses. The controller generates memberId and accountId, and internally maps to adminId for AdminApiInterface calls.

#### Scenario: Response contains only platform IDs
- **WHEN** createMember succeeds
- **THEN** the response contains memberId and accountId but NOT adminId

### Requirement: All JSON keys are camelCase
The system SHALL use camelCase for all JSON request and response keys across the public API.

#### Scenario: Response key format
- **WHEN** any public API response is returned
- **THEN** all keys (ok, memberId, accountId, operationId, error) are camelCase
