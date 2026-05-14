## ADDED Requirements

### Requirement: createMember accepts canonical CreateMemberInput shape
The controller SHALL accept a flat JSON body matching `CreateMemberInput`: `{ userId, firstName, lastName, email, mobile, dateOfBirth, initialInvestmentProfile: [{assetCode, percentage}] }`. The field `initialInvestmentProfile` (not `investmentProfile`) SHALL be used for the allocations array.

#### Scenario: Valid canonical request
- **WHEN** POST /public/createMember is called with `{ "userId": "u1", "firstName": "John", "lastName": "Doe", "email": "j@e.com", "mobile": "0400000000", "dateOfBirth": "1990-01-01", "initialInvestmentProfile": [{"assetCode": "Cash", "percentage": 100}] }`
- **THEN** the system returns `{ "ok": true, "memberId": "<uuid>", "accountId": "<uuid>", "operationId": "<uuid>" }`

#### Scenario: Missing firstName rejected
- **WHEN** POST /public/createMember is called without firstName
- **THEN** the system returns `{ "ok": false, "error": "..." }`

#### Scenario: Missing lastName rejected
- **WHEN** POST /public/createMember is called without lastName
- **THEN** the system returns `{ "ok": false, "error": "..." }`

#### Scenario: Missing email rejected
- **WHEN** POST /public/createMember is called without email
- **THEN** the system returns `{ "ok": false, "error": "..." }`

#### Scenario: Missing mobile rejected
- **WHEN** POST /public/createMember is called without mobile
- **THEN** the system returns `{ "ok": false, "error": "..." }`

#### Scenario: Missing dateOfBirth rejected
- **WHEN** POST /public/createMember is called without dateOfBirth
- **THEN** the system returns `{ "ok": false, "error": "..." }`

### Requirement: Members table stores name and date of birth
The members table SHALL have columns `first_name`, `last_name`, `date_of_birth` to persist the data from CreateMemberInput.

#### Scenario: Data persisted on create
- **WHEN** createMember succeeds with firstName "Jane", lastName "Smith", dateOfBirth "1985-06-15"
- **THEN** the members row has first_name="Jane", last_name="Smith", date_of_birth="1985-06-15"

### Requirement: MockAdminApi stores name and DOB fields
MockAdminApi::createMember SHALL persist `firstName`, `lastName`, `dateOfBirth` from the input data to the members table.

#### Scenario: Fields stored via MockAdminApi
- **WHEN** MockAdminApi::createMember is called with data containing firstName, lastName, dateOfBirth
- **THEN** the created Member model has those values in first_name, last_name, date_of_birth columns

### Requirement: setDailyUnitPrices uses unitPrice field name
The controller SHALL read the price value from each entry's `unitPrice` field (not `price`), matching the canonical `DailyUnitPrice` interface: `{ assetCode, date, unitPrice }`.

#### Scenario: Canonical price input
- **WHEN** POST /mock/setDailyUnitPrices is called with `{ "date": "2024-01-01", "prices": [{"assetCode": "Cash", "date": "2024-01-01", "unitPrice": 1.5}] }`
- **THEN** the unit_prices row has price=1.5 for Cash on 2024-01-01

### Requirement: getTransactionHistory returns transactionId
MockAdminApi::getTransactionHistory SHALL return `transactionId` (not `id`) in each transaction object.

#### Scenario: Transaction response shape
- **WHEN** getTransactionHistory is called for an account with transactions
- **THEN** each transaction in the response has key `transactionId` (not `id`)

### Requirement: getHoldings includes effectiveDate per holding
MockAdminApi::getHoldings SHALL include `effectiveDate` in each holding object in the response.

#### Scenario: Holding response shape
- **WHEN** getHoldings is called for an account with processed holdings
- **THEN** each holding object contains `assetCode`, `units`, `unitPrice`, `balance`, and `effectiveDate`

### Requirement: createMember output matches CreateMemberOutput
The createMember response SHALL be exactly `{ ok: true, memberId, accountId, operationId }` on success and `{ ok: false, error }` on failure. No other fields.

#### Scenario: Success response shape
- **WHEN** createMember succeeds
- **THEN** response is `{ "ok": true, "memberId": "...", "accountId": "...", "operationId": "..." }`

#### Scenario: Failure response shape
- **WHEN** createMember fails validation
- **THEN** response is `{ "ok": false, "error": "..." }`
