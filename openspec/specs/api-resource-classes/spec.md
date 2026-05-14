## ADDED Requirements

### Requirement: CreateMemberResource
The system SHALL provide `App\Http\Resources\CreateMemberResource` that formats a successful createMember response as `{ "ok": true, "memberId": "...", "accountId": "...", "operationId": "..." }`.

#### Scenario: Successful creation
- **WHEN** a member is created successfully
- **THEN** the resource wraps the result with ok: true and the three IDs

### Requirement: OperationResource
The system SHALL provide `App\Http\Resources\OperationResource` that formats mutation responses as `{ "ok": true, "operationId": "..." }`. Used by updateMember and setInvestmentProfile.

#### Scenario: Successful mutation
- **WHEN** updateMember or setInvestmentProfile succeeds
- **THEN** the resource returns ok: true with operationId

### Requirement: InvestmentPortfolioResource
The system SHALL provide `App\Http\Resources\InvestmentPortfolioResource` that formats the response as `{ "ok": true, "allocations": [{assetCode, percentage}] }`.

#### Scenario: Portfolio returned
- **WHEN** getInvestmentPortfolio is called for a valid member
- **THEN** allocations array uses camelCase keys

### Requirement: TransactionHistoryResource
The system SHALL provide `App\Http\Resources\TransactionHistoryResource` that formats the response as `{ "ok": true, "transactions": [{transactionId, effectiveDate, type, amount}] }`.

#### Scenario: Transactions returned
- **WHEN** getTransactionHistory returns results
- **THEN** each transaction uses camelCase keys with transactionId (not id)

### Requirement: HoldingsResource
The system SHALL provide `App\Http\Resources\HoldingsResource` that formats the response as `{ "ok": true, "holdings": [{assetCode, units, unitPrice, balance, effectiveDate}] }`.

#### Scenario: Holdings returned
- **WHEN** getHoldings returns results
- **THEN** each holding uses camelCase keys with all 5 fields

### Requirement: RequestAuditResource
The system SHALL provide `App\Http\Resources\RequestAuditResource` that formats the response as `{ "ok": true, "audit": {userId, operationId, operation, status, events: [{at, type, details}]} }`.

#### Scenario: Audit returned
- **WHEN** getRequestAudit finds the operation
- **THEN** the nested audit object includes ordered events

### Requirement: AuditEventsResource
The system SHALL provide `App\Http\Resources\AuditEventsResource` that formats the response as `{ "ok": true, "events": [{at, type, details}] }`.

#### Scenario: Events listed
- **WHEN** listAuditEvents returns results
- **THEN** events are a flat camelCase array

### Requirement: ApiErrorResponse helper
The system SHALL provide a static helper `App\Http\Resources\ApiErrorResponse::make(string $error): JsonResponse` that returns `{ "ok": false, "error": "..." }` with HTTP 200.

#### Scenario: Error response from controller
- **WHEN** a controller encounters a business logic error (e.g., member not found)
- **THEN** it returns `ApiErrorResponse::make('Member not found')`

### Requirement: No wrapping key on resources
All API Resources SHALL override `$wrap = null` so Laravel does not add a `"data"` wrapper around the response.

#### Scenario: Response has no data wrapper
- **WHEN** any resource is returned
- **THEN** the JSON is flat (ok, memberId, etc.) without a "data" key
