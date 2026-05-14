## 1. Base Classes and Rules

- [x] 1.1 Create `App\Http\Requests\ApiFormRequest` base class (extends FormRequest, overrides failedValidation for RPC envelope, authorize returns true)
- [x] 1.2 Create `App\Rules\ValidAllocations` rule class (sum to 100.00, valid asset codes, no duplicates, max 2dp)
- [x] 1.3 Create `App\Traits\ResolvesMember` trait with `resolveMember($userId, $memberId?, $accountId?): ?Member` and `resolveAdminId(...)`: ?string`
- [x] 1.4 Create `App\Http\Resources\ApiErrorResponse` static helper class

## 2. FormRequest Classes — Public API

- [x] 2.1 Create `CreateMemberRequest` (userId, firstName, lastName, email, mobile, dateOfBirth required; initialInvestmentProfile validated via ValidAllocations)
- [x] 2.2 Create `UpdateMemberRequest` (userId, memberId required; at least one of email/mobile/preferredName/residentialAddress/postalAddress)
- [x] 2.3 Create `SetInvestmentProfileRequest` (userId, memberId, accountId required; allocations validated via ValidAllocations)
- [x] 2.4 Create `GetInvestmentPortfolioRequest` (userId, memberId, accountId required)
- [x] 2.5 Create `GetTransactionHistoryRequest` (userId, memberId, accountId required; fromDate/toDate optional|date)
- [x] 2.6 Create `GetHoldingsRequest` (userId, memberId, accountId required; asOfDate optional|date)

## 3. FormRequest Classes — Mock Control API

- [x] 3.1 Create `AddTransactionsRequest` (userId, accountId required; transactions array with effectiveDate/type/amount)
- [x] 3.2 Create `SetDailyUnitPricesRequest` (date required; prices array with assetCode/unitPrice)
- [x] 3.3 Create `MoveDayForwardRequest` (days optional|integer|min:1)
- [x] 3.4 Create `ResetSubjectStateRequest` (userId required)

## 4. FormRequest Classes — Inspection API

- [x] 4.1 Create `GetRequestAuditRequest` (userId, operationId required)
- [x] 4.2 Create `ListAuditEventsRequest` (userId required)

## 5. API Resource Classes

- [x] 5.1 Create `CreateMemberResource` (ok: true, memberId, accountId, operationId)
- [x] 5.2 Create `OperationResource` (ok: true, operationId) — for updateMember, setInvestmentProfile
- [x] 5.3 Create `InvestmentPortfolioResource` (ok: true, allocations array)
- [x] 5.4 Create `TransactionHistoryResource` (ok: true, transactions array)
- [x] 5.5 Create `HoldingsResource` (ok: true, holdings array)
- [x] 5.6 Create `RequestAuditResource` (ok: true, audit object with events)
- [x] 5.7 Create `AuditEventsResource` (ok: true, events flat array)

## 6. Refactor Existing Controllers

- [x] 6.1 Refactor `PublicApiController::createMember` — type-hint CreateMemberRequest, use ResolvesMember trait, return CreateMemberResource, remove inline validation
- [x] 6.2 Refactor `MockControlController::addTransactions` — type-hint AddTransactionsRequest, remove inline validation
- [x] 6.3 Refactor `MockControlController::setDailyUnitPrices` — type-hint SetDailyUnitPricesRequest, remove inline validation

## 7. Verification

- [x] 7.1 Test createMember with valid payload — verify Resource response shape
- [x] 7.2 Test createMember with missing field — verify ApiFormRequest returns `{ ok: false, error }`
- [x] 7.3 Test createMember with invalid allocations — verify ValidAllocations rule fires
- [x] 7.4 Test addTransactions with valid payload
- [x] 7.5 Test setDailyUnitPrices with invalid asset code — verify FormRequest rejects
