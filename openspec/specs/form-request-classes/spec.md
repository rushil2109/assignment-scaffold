## ADDED Requirements

### Requirement: Base ApiFormRequest class
The system SHALL provide a base `App\Http\Requests\ApiFormRequest` extending Laravel's `FormRequest` that overrides `failedValidation()` to throw an `HttpResponseException` with response body `{ "ok": false, "error": "<first validation error message>" }` and HTTP 200.

#### Scenario: Validation fails on any endpoint
- **WHEN** a FormRequest fails validation
- **THEN** the response is `{ "ok": false, "error": "..." }` with HTTP 200 (not 422)

#### Scenario: Authorization always passes
- **WHEN** any API request is made
- **THEN** `authorize()` returns true (auth is out of scope)

### Requirement: CreateMemberRequest
The system SHALL provide `App\Http\Requests\CreateMemberRequest` extending `ApiFormRequest` with rules: userId (required|string), firstName (required|string), lastName (required|string), email (required|string|email), mobile (required|string), dateOfBirth (required|date), initialInvestmentProfile (required|array, validated by ValidAllocations rule).

#### Scenario: All fields present and valid
- **WHEN** a valid createMember payload is submitted
- **THEN** the request passes validation and reaches the controller

#### Scenario: Missing required field
- **WHEN** firstName is missing from the payload
- **THEN** the response is `{ "ok": false, "error": "The first name field is required." }` or similar

### Requirement: UpdateMemberRequest
The system SHALL provide `App\Http\Requests\UpdateMemberRequest` with rules: userId (required|string), memberId (required|string), and at least one of: email, mobile, preferredName, residentialAddress, postalAddress.

#### Scenario: No updatable fields provided
- **WHEN** only userId and memberId are provided
- **THEN** the response is `{ "ok": false, "error": "..." }`

### Requirement: SetInvestmentProfileRequest
The system SHALL provide `App\Http\Requests\SetInvestmentProfileRequest` with rules: userId (required|string), memberId (required|string), accountId (required|string), allocations (required|array, validated by ValidAllocations rule).

#### Scenario: Missing accountId
- **WHEN** accountId is not provided
- **THEN** validation fails with appropriate error

### Requirement: GetInvestmentPortfolioRequest
The system SHALL provide `App\Http\Requests\GetInvestmentPortfolioRequest` with rules: userId (required|string), memberId (required|string), accountId (required|string).

#### Scenario: Valid read request
- **WHEN** all three IDs are provided
- **THEN** request passes validation

### Requirement: GetTransactionHistoryRequest
The system SHALL provide `App\Http\Requests\GetTransactionHistoryRequest` with rules: userId (required|string), memberId (required|string), accountId (required|string), fromDate (optional|date), toDate (optional|date).

#### Scenario: Optional date filters
- **WHEN** fromDate and toDate are omitted
- **THEN** request passes validation (they are optional)

### Requirement: GetHoldingsRequest
The system SHALL provide `App\Http\Requests\GetHoldingsRequest` with rules: userId (required|string), memberId (required|string), accountId (required|string), asOfDate (optional|date).

#### Scenario: Valid request with asOfDate
- **WHEN** all IDs plus asOfDate are provided
- **THEN** request passes validation

### Requirement: AddTransactionsRequest
The system SHALL provide `App\Http\Requests\AddTransactionsRequest` with rules: userId (required|string), accountId (required|string), transactions (required|array|min:1), transactions.*.effectiveDate (required|date), transactions.*.type (required|string), transactions.*.amount (required|numeric).

#### Scenario: Empty transactions array
- **WHEN** transactions is an empty array
- **THEN** validation fails

### Requirement: SetDailyUnitPricesRequest
The system SHALL provide `App\Http\Requests\SetDailyUnitPricesRequest` with rules: date (required|date), prices (required|array|min:1), prices.*.assetCode (required|string|in:Cash,Conservative,Balanced,Growth,HighGrowth), prices.*.unitPrice (required|numeric|gt:0).

#### Scenario: Invalid asset code
- **WHEN** an entry has assetCode "Invalid"
- **THEN** validation fails

### Requirement: MoveDayForwardRequest
The system SHALL provide `App\Http\Requests\MoveDayForwardRequest` with rules: days (optional|integer|min:1).

#### Scenario: No days parameter (defaults to 1)
- **WHEN** days is omitted
- **THEN** request passes validation

### Requirement: ResetSubjectStateRequest
The system SHALL provide `App\Http\Requests\ResetSubjectStateRequest` with rules: userId (required|string).

#### Scenario: Valid reset
- **WHEN** userId is provided
- **THEN** request passes validation

### Requirement: GetRequestAuditRequest
The system SHALL provide `App\Http\Requests\GetRequestAuditRequest` with rules: userId (required|string), operationId (required|string).

#### Scenario: Both fields required
- **WHEN** operationId is missing
- **THEN** validation fails

### Requirement: ListAuditEventsRequest
The system SHALL provide `App\Http\Requests\ListAuditEventsRequest` with rules: userId (required|string).

#### Scenario: Valid request
- **WHEN** userId is provided
- **THEN** request passes validation
