## ADDED Requirements

### Requirement: moveDayForward endpoint
The system SHALL expose `POST /mock/moveDayForward` accepting `{ days?: number }` (default 1) and returning `{ ok: true, processedDates: IsoDate[] }`.

#### Scenario: Advance one day
- **WHEN** moveDayForward is called without days parameter
- **THEN** system processes one day and returns processedDates with one date

#### Scenario: Advance multiple days
- **WHEN** moveDayForward is called with days: 3
- **THEN** system processes 3 days sequentially and returns processedDates with 3 dates

### Requirement: Eligible account identification
The system SHALL only process accounts that have prior holdings OR transactions effective on the current processing day.

#### Scenario: Account with prior holdings but no transactions
- **WHEN** an account has holdings from a previous day but no transactions today
- **THEN** it is processed (units carry forward at new prices)

#### Scenario: Account with transactions but no prior holdings
- **WHEN** an account has transactions effective today but no prior holdings
- **THEN** it is processed (starting from zero units)

#### Scenario: Account with neither holdings nor transactions
- **WHEN** an account has no prior holdings and no transactions on the processing day
- **THEN** it is skipped (no holdings row created)

### Requirement: Cash flow allocation by profile percentages
The system SHALL allocate net cash flow across asset classes according to the active investment profile. Rounding rule: floor each allocation to 2 decimal places, remainder assigned to last asset (sorted by asset_code alphabetically).

#### Scenario: Even split
- **WHEN** cash flow is 100.00 and profile is 50%/50% across two assets
- **THEN** each asset receives 50.00

#### Scenario: Fractional split with remainder
- **WHEN** cash flow is 100.00 and profile is 33.33%/33.33%/33.34%
- **THEN** first two assets get floor(33.33) and floor(33.33), last gets remainder so total equals 100.00

#### Scenario: No transactions (zero cash flow)
- **WHEN** an account has prior holdings but no transactions on the processing day
- **THEN** cash flow is 0.00, allocations are all 0.00, units carry forward unchanged

### Requirement: Unit price lookup with carry-forward
The system SHALL use the unit price for each asset on the processing day. If no price exists for that day, carry forward the most recent prior price. If no price has ever been set for an asset, use 1.0.

#### Scenario: Price set for today
- **WHEN** a unit price exists for the asset on the processing day
- **THEN** that price is used for calculations

#### Scenario: Price carry-forward
- **WHEN** no price exists for today but one was set 3 days ago
- **THEN** the 3-day-old price is carried forward

#### Scenario: No price ever set
- **WHEN** no price has ever been set for an asset
- **THEN** price defaults to 1.0

### Requirement: Holdings calculation invariants
The system SHALL calculate: `new_units = previous_units + allocated_cashflow / unit_price` and `balance = units * unit_price` (rounded to 2dp).

#### Scenario: Units calculation
- **WHEN** previous_units is 10.000000, allocated_cashflow is 50.00, unit_price is 2.000000
- **THEN** new_units = 10.000000 + 50.00 / 2.000000 = 35.000000

#### Scenario: Balance calculation
- **WHEN** units is 35.000000 and unit_price is 2.000000
- **THEN** balance = 70.00

### Requirement: Holdings snapshot persistence
The system SHALL persist one holdings row per account × asset_code × effective_date.

#### Scenario: Snapshot persisted
- **WHEN** daily processing completes for an account
- **THEN** one row per asset in the profile exists in holdings for that date

### Requirement: Double-processing guard
The system SHALL NOT reprocess a day that has already been processed. If moveDayForward is called without advancing the clock, it returns empty processedDates.

#### Scenario: Repeated call without advancement
- **WHEN** moveDayForward is called twice in succession (no other clock advancement)
- **THEN** the second call returns `{ ok: true, processedDates: [] }`

### Requirement: System date advancement
The system SHALL advance system_state.current_date by the number of days processed.

#### Scenario: Date advances
- **WHEN** moveDayForward processes 3 days starting from 2024-01-01
- **THEN** system_state.current_date becomes 2024-01-04
