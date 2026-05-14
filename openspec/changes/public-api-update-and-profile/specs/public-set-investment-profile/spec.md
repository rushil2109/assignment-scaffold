## ADDED Requirements

### Requirement: setInvestmentProfile endpoint
The system SHALL expose `POST /public/setInvestmentProfile` accepting a JSON body with userId (required) and investmentProfile (array of {assetCode, percentage}, required).

#### Scenario: Valid profile change
- **WHEN** a POST is made with valid userId and valid allocations summing to 100.00
- **THEN** the system sets the new profile, writes audit, and returns `{ ok: true, operationId }`

#### Scenario: Member not found
- **WHEN** a POST is made with a userId that doesn't exist
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: Allocation validation (same rules as createMember)
The system SHALL validate allocations: sum to 100.00, valid asset codes only, no duplicate codes, percentages max 2 decimal places.

#### Scenario: Invalid allocations rejected
- **WHEN** allocations fail any validation rule
- **THEN** the system returns `{ ok: false, error: "..." }` without modifying state

### Requirement: Profile changes affect future only
Setting a new investment profile SHALL NOT rewrite any existing holdings snapshots. Only future daily processing uses the new profile.

#### Scenario: Past holdings unchanged after profile update
- **WHEN** a new profile is set after holdings have been calculated
- **THEN** previously persisted holdings snapshots remain exactly as they were

### Requirement: setInvestmentProfile writes audit trail
The system SHALL create an audit operation with event type "profile_updated" including the new allocations in details.

#### Scenario: Audit recorded on profile change
- **WHEN** setInvestmentProfile completes successfully
- **THEN** an audit_operation (status: success) and audit_event (type: profile_updated) are persisted
