## ADDED Requirements

### Requirement: updateMember spec accepts memberId
The `POST /public/updateMember` spec SHALL require both `userId` and `memberId` in the input, matching `UpdateMemberInput`: `{ userId, memberId, email?, mobile?, preferredName?, residentialAddress?, postalAddress? }`.

#### Scenario: Canonical updateMember input
- **WHEN** POST /public/updateMember is called with `{ "userId": "u1", "memberId": "m1", "email": "new@e.com" }`
- **THEN** the system accepts the request and processes the update

### Requirement: setInvestmentProfile spec accepts memberId, accountId, and uses allocations key
The `POST /public/setInvestmentProfile` spec SHALL require `userId`, `memberId`, `accountId`, and `allocations` (not `investmentProfile`), matching `SetInvestmentProfileInput`: `{ userId, memberId, accountId, allocations: [{assetCode, percentage}] }`.

#### Scenario: Canonical setInvestmentProfile input
- **WHEN** POST /public/setInvestmentProfile is called with `{ "userId": "u1", "memberId": "m1", "accountId": "a1", "allocations": [{"assetCode": "Cash", "percentage": 100}] }`
- **THEN** the system accepts the request and processes the profile change

### Requirement: getInvestmentPortfolio spec accepts memberId and accountId
The `POST /public/getInvestmentPortfolio` spec SHALL require `userId`, `memberId`, and `accountId`, matching `GetInvestmentPortfolioInput`: `{ userId, memberId, accountId }`.

#### Scenario: Canonical getInvestmentPortfolio input
- **WHEN** POST /public/getInvestmentPortfolio is called with `{ "userId": "u1", "memberId": "m1", "accountId": "a1" }`
- **THEN** the system returns the current allocations

### Requirement: getTransactionHistory spec accepts memberId and accountId
The `POST /public/getTransactionHistory` spec SHALL require `userId`, `memberId`, `accountId` with optional `fromDate` and `toDate`, matching `GetTransactionHistoryInput`: `{ userId, memberId, accountId, fromDate?, toDate? }`.

#### Scenario: Canonical getTransactionHistory input
- **WHEN** POST /public/getTransactionHistory is called with `{ "userId": "u1", "memberId": "m1", "accountId": "a1" }`
- **THEN** the system returns the transaction list

### Requirement: getHoldings spec accepts memberId and accountId
The `POST /public/getHoldings` spec SHALL require `userId`, `memberId`, `accountId` with optional `asOfDate`, matching `GetHoldingsInput`: `{ userId, memberId, accountId, asOfDate? }`.

#### Scenario: Canonical getHoldings input
- **WHEN** POST /public/getHoldings is called with `{ "userId": "u1", "memberId": "m1", "accountId": "a1" }`
- **THEN** the system returns the holdings array

### Requirement: All active specs updated to use canonical field names
Every active OpenSpec spec file for unimplemented endpoints SHALL be edited to reflect the exact field names from the canonical TypeScript contract. No spec SHALL reference field names that differ from the contract.

#### Scenario: Spec audit passes
- **WHEN** each active spec file is compared against the canonical TypeScript interface
- **THEN** all field names, required fields, and optional fields match exactly
