## ADDED Requirements

### Requirement: getInvestmentPortfolio endpoint
The system SHALL expose `POST /public/getInvestmentPortfolio` accepting `{ userId }` and returning `{ ok: true, allocations: [{assetCode, percentage}] }`.

#### Scenario: Member with active profile
- **WHEN** getInvestmentPortfolio is called for a member with a current profile
- **THEN** the current allocations (is_current=true) are returned

#### Scenario: Member not found
- **WHEN** getInvestmentPortfolio is called with a userId that doesn't exist
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: No audit trail for reads
Read endpoints SHALL NOT create audit_operations or audit_events.

#### Scenario: Audit unchanged after read
- **WHEN** getInvestmentPortfolio is called
- **THEN** no new rows appear in audit_operations or audit_events
