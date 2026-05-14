## ADDED Requirements

### Requirement: getHoldings endpoint
The system SHALL expose `POST /public/getHoldings` accepting `{ userId, asOfDate? }` and returning `{ ok: true, holdings: [{assetCode, units, unitPrice, balance, effectiveDate}] }`.

#### Scenario: Holdings for specific date
- **WHEN** getHoldings is called with asOfDate that has data
- **THEN** holdings for that exact date are returned

#### Scenario: Holdings without asOfDate (latest)
- **WHEN** getHoldings is called without asOfDate
- **THEN** holdings for the most recent processed date are returned

#### Scenario: asOfDate with no data
- **WHEN** getHoldings is called with an asOfDate that has no holdings
- **THEN** the system returns `{ ok: true, holdings: [] }`

#### Scenario: No holdings ever processed
- **WHEN** getHoldings is called for a member with no holdings history
- **THEN** the system returns `{ ok: true, holdings: [] }`

#### Scenario: Member not found
- **WHEN** getHoldings is called with a userId that doesn't exist
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: Holdings response includes all fields
Each holding in the response SHALL include assetCode, units (string/number with 6dp precision), unitPrice (string/number with 6dp), balance (2dp), and effectiveDate (ISO date string).

#### Scenario: Complete holding object
- **WHEN** holdings are returned
- **THEN** each object has assetCode, units, unitPrice, balance, effectiveDate — all camelCase
