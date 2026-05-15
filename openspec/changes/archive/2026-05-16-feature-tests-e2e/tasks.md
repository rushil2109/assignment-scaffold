## 1. Test Setup

- [x] 1.1 Create base test case or trait that seeds system_state in setUp
- [x] 1.2 Ensure RefreshDatabase trait is used in all feature test classes

## 2. Lifecycle Test

- [x] 2.1 Create tests/Feature/FullLifecycleTest.php
- [x] 2.2 Test: createMember → addTransactions → setDailyUnitPrices → moveDayForward → getHoldings
- [x] 2.3 Assert exact holdings values (units, unitPrice, balance) match manual calculation

## 3. Public API Tests

- [x] 3.1 Create tests/Feature/CreateMemberTest.php — test idempotency and validation
- [x] 3.2 Create tests/Feature/UpdateMemberTest.php — test update persistence and error cases
- [x] 3.3 Create tests/Feature/SetInvestmentProfileTest.php — test profile change reflected in getInvestmentPortfolio
- [x] 3.4 Create tests/Feature/GetTransactionHistoryTest.php — test date filtering
- [x] 3.5 Create tests/Feature/GetHoldingsTest.php — test asOfDate and latest semantics

## 4. Mock Control Tests

- [x] 4.1 Create tests/Feature/ResetSubjectStateTest.php — test reset then recreate flow

## 5. Inspection Tests

- [x] 5.1 Create tests/Feature/InspectionApiTest.php — test getRequestAudit structure and listAuditEvents ordering

## 6. Contract Tests

- [x] 6.1 Assert all responses contain camelCase keys (memberId, accountId, operationId, assetCode, effectiveDate, unitPrice)
- [x] 6.2 Assert all responses have ok: true/false at top level

## 7. Verification

- [x] 7.1 Run `make test` — all feature tests pass
- [x] 7.2 Verify no test depends on test execution order (each is self-contained)
