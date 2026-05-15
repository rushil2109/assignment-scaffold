## ADDED Requirements

### Requirement: resetSubjectState endpoint
The system SHALL expose `POST /mock/resetSubjectState` accepting `{ userId }` and returning `{ ok: true }`.

#### Scenario: Reset existing member
- **WHEN** resetSubjectState is called with a userId that exists
- **THEN** the member, account, profiles, transactions, and holdings are all deleted

#### Scenario: Reset non-existent member
- **WHEN** resetSubjectState is called with a userId that doesn't exist
- **THEN** the system returns `{ ok: true }` (no error)

### Requirement: Audit preserved after reset
Audit operations and events for the userId SHALL NOT be deleted by resetSubjectState.

#### Scenario: Audit survives reset
- **WHEN** a member is reset after having performed mutations
- **THEN** audit_operations and audit_events for that user_id still exist and are queryable

### Requirement: createMember works after reset
After resetSubjectState, the same userId SHALL be available for a fresh createMember call.

#### Scenario: Re-creation after reset
- **WHEN** createMember is called with a userId that was previously reset
- **THEN** a new member is created successfully with new memberId, accountId, and adminId

### Requirement: No audit trail for mock operations
resetSubjectState SHALL NOT create audit_operations or audit_events.

#### Scenario: No audit for reset
- **WHEN** resetSubjectState is called
- **THEN** no new rows appear in audit_operations or audit_events
