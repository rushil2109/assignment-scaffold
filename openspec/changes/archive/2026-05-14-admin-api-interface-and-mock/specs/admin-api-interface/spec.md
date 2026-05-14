## ADDED Requirements

### Requirement: createMember method
The interface SHALL define `createMember(string $adminId, array $data): array` that persists a new member with the given adminId and returns member details.

#### Scenario: Create new member
- **WHEN** createMember is called with a valid adminId and member data
- **THEN** the member is persisted and an array with adminId and account details is returned

### Requirement: updateMember method
The interface SHALL define `updateMember(string $adminId, array $data): array` that updates mutable fields (email, mobile, preferredName, addresses) for the member identified by adminId.

#### Scenario: Update existing member
- **WHEN** updateMember is called with a valid adminId and partial data
- **THEN** only the provided fields are updated and the updated member is returned

#### Scenario: Update non-existent member
- **WHEN** updateMember is called with an adminId that doesn't exist
- **THEN** an exception or error indicator is returned

### Requirement: setInvestmentProfile method
The interface SHALL define `setInvestmentProfile(string $adminId, array $allocations): array` that sets a new investment profile for the member's account.

#### Scenario: Set new profile
- **WHEN** setInvestmentProfile is called with valid allocations summing to 100.00
- **THEN** the new profile is persisted as current and any previous profile is marked non-current

### Requirement: getInvestmentPortfolio method
The interface SHALL define `getInvestmentPortfolio(string $adminId): array` that returns the current active allocation set.

#### Scenario: Retrieve current profile
- **WHEN** getInvestmentPortfolio is called for a member with a profile
- **THEN** an array of allocations (asset_code, percentage) where is_current=true is returned

### Requirement: getTransactionHistory method
The interface SHALL define `getTransactionHistory(string $adminId, ?string $fromDate = null, ?string $toDate = null): array` that returns transactions with optional date filtering.

#### Scenario: All transactions
- **WHEN** getTransactionHistory is called without date filters
- **THEN** all transactions for the member's account are returned ordered by effective_date asc, id asc

#### Scenario: Filtered by date range
- **WHEN** getTransactionHistory is called with fromDate and toDate
- **THEN** only transactions within the inclusive date range are returned

### Requirement: getHoldings method
The interface SHALL define `getHoldings(string $adminId, ?string $asOfDate = null): array` that returns holdings snapshots.

#### Scenario: Holdings for specific date
- **WHEN** getHoldings is called with an asOfDate
- **THEN** holdings for that specific date are returned (empty array if none)

#### Scenario: Holdings without date (latest)
- **WHEN** getHoldings is called without asOfDate
- **THEN** holdings for the most recent processed date are returned
