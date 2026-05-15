## ADDED Requirements

### Requirement: Allocation sum invariant
Tests SHALL prove that floor-rounded allocations sum exactly to net cash flow for all test cases.

#### Scenario: Fractional percentage split
- **WHEN** cash flow is allocated with 33.33%/33.33%/33.34% split
- **THEN** the three allocations sum to exactly the input cash flow

#### Scenario: Single asset 100%
- **WHEN** cash flow is allocated to one asset at 100%
- **THEN** that asset receives the entire cash flow with no remainder

### Requirement: Units calculation invariant
Tests SHALL prove `new_units = previous_units + allocated_cashflow / unit_price` for each holding.

#### Scenario: Units increase with contribution
- **WHEN** 100.00 is allocated to an asset with unit_price 2.0 and previous_units 10.0
- **THEN** new_units = 10.0 + 100.00 / 2.0 = 60.0

#### Scenario: Units with zero cash flow
- **WHEN** 0.00 is allocated and previous_units is 10.0
- **THEN** new_units = 10.0 (unchanged)

### Requirement: Balance calculation invariant
Tests SHALL prove `balance = units * unitPrice` rounded to 2dp.

#### Scenario: Balance matches formula
- **WHEN** units is 35.123456 and unitPrice is 1.500000
- **THEN** balance = round(35.123456 * 1.500000, 2) = 52.69

### Requirement: Member creation idempotency
Tests SHALL prove createMember is idempotent on userId.

#### Scenario: Duplicate creation returns same IDs
- **WHEN** createMember is called twice with the same userId
- **THEN** the same adminId is returned both times without error

### Requirement: Investment profile validation
Tests SHALL prove validation rejects invalid allocations.

#### Scenario: Sum not 100
- **WHEN** allocations sum to 99.00
- **THEN** validation fails

#### Scenario: Invalid asset code
- **WHEN** allocation includes "InvalidCode"
- **THEN** validation fails

#### Scenario: Duplicate asset code
- **WHEN** two allocations use "Cash"
- **THEN** validation fails

#### Scenario: More than 2 decimal places
- **WHEN** percentage is 33.333
- **THEN** validation fails

### Requirement: Profile change isolation
Tests SHALL prove past holdings are unchanged when profile changes.

#### Scenario: Historical holdings preserved
- **WHEN** profile is changed after day 3 and day 4 is processed
- **THEN** holdings for days 1-3 remain exactly as they were before the profile change

### Requirement: Double-processing guard
Tests SHALL prove moveDayForward doesn't reprocess a day.

#### Scenario: Second call produces no new holdings
- **WHEN** moveDayForward is called twice for the same day range
- **THEN** the second call processes zero days and creates no new holdings rows

### Requirement: Determinism
Tests SHALL prove same inputs produce same outputs.

#### Scenario: Repeated calculation matches
- **WHEN** the same setup is run twice (clean + rebuild)
- **THEN** resulting holdings are identical
