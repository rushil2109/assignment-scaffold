## ADDED Requirements

### Requirement: setDailyUnitPrices endpoint
The system SHALL expose `POST /mock/setDailyUnitPrices` accepting `{ date, prices: [{assetCode, price}] }` and returning `{ ok: true }`.

#### Scenario: Set prices for a day
- **WHEN** a POST is made with date and array of asset prices
- **THEN** unit_prices rows are created for each asset_code on that date

### Requirement: Upsert behavior
If a price already exists for the same asset_code + date, it SHALL be overwritten.

#### Scenario: Price overwrite
- **WHEN** setDailyUnitPrices is called twice for the same date and asset
- **THEN** only one row exists with the latest price value

### Requirement: No audit trail for mock operations
Mock control endpoints SHALL NOT create audit_operations or audit_events.

#### Scenario: Audit tables unchanged
- **WHEN** setDailyUnitPrices is called
- **THEN** audit_operations and audit_events tables have no new rows
