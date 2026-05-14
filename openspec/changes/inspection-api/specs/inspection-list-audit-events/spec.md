## ADDED Requirements

### Requirement: listAuditEvents endpoint
The system SHALL expose `POST /inspection/listAuditEvents` accepting `{ userId }` and returning `{ ok: true, events: [{at, type, details}] }`.

#### Scenario: User with multiple operations
- **WHEN** listAuditEvents is called for a user with events across multiple operations
- **THEN** all events are returned in a flat list ordered by id ascending

#### Scenario: User with no events
- **WHEN** listAuditEvents is called for a userId with no audit history
- **THEN** the system returns `{ ok: true, events: [] }`

### Requirement: Flat list across operations
Events SHALL NOT be grouped by operation — they are a single flat array ordered by id.

#### Scenario: Interleaved operations
- **WHEN** a user has events from operations A and B interleaved by time
- **THEN** events appear in id order regardless of which operation they belong to

### Requirement: Event shape matches getRequestAudit
Each event SHALL have the same shape: `at` (ISO datetime), `type` (string), `details` (object or null).

#### Scenario: Consistent event format
- **WHEN** the same event is viewed via listAuditEvents and getRequestAudit
- **THEN** the at, type, and details fields are identical
