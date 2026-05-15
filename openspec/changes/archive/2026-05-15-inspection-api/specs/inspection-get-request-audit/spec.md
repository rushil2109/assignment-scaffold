## ADDED Requirements

### Requirement: getRequestAudit endpoint
The system SHALL expose `POST /inspection/getRequestAudit` accepting `{ userId, operationId }` and returning `{ ok: true, audit: {userId, operationId, operation, status, events: [{at, type, details}]} }`.

#### Scenario: Valid operation found
- **WHEN** getRequestAudit is called with a valid userId and operationId
- **THEN** the full audit object is returned with operation details and ordered events

#### Scenario: Operation not found
- **WHEN** getRequestAudit is called with an operationId that doesn't exist for that userId
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: Events ordered by insertion
Events within a RequestAudit SHALL be ordered by id (auto-increment = insertion order).

#### Scenario: Multiple events in order
- **WHEN** an operation has multiple events
- **THEN** they appear in the events array ordered by their id ascending

### Requirement: Event shape
Each event in the response SHALL include `at` (ISO datetime string from created_at), `type` (string), and `details` (object or null).

#### Scenario: Event with details
- **WHEN** an event was recorded with details
- **THEN** the details object is included in the response

#### Scenario: Event without details
- **WHEN** an event was recorded without details
- **THEN** details is null in the response
