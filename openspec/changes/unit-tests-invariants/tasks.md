## 1. Allocation Math Tests

- [ ] 1.1 Create tests/Unit/AllocationInvariantTest.php
- [ ] 1.2 Test fractional split (33.33/33.33/33.34) sums to cash flow
- [ ] 1.3 Test single-asset 100% allocation
- [ ] 1.4 Test zero cash flow produces zero allocations

## 2. Holdings Calculation Tests

- [ ] 2.1 Create tests/Unit/HoldingsCalculationTest.php
- [ ] 2.2 Test units = previous + cashflow / price invariant
- [ ] 2.3 Test balance = units * price invariant (with rounding)
- [ ] 2.4 Test carry-forward (zero cash flow, units unchanged)

## 3. Member and Profile Tests

- [ ] 3.1 Create tests/Unit/MemberIdempotencyTest.php
- [ ] 3.2 Test duplicate userId returns same adminId
- [ ] 3.3 Create tests/Unit/ProfileValidationTest.php
- [ ] 3.4 Test sum != 100 rejected, invalid codes rejected, duplicates rejected, excess decimals rejected

## 4. Processing Guard Tests

- [ ] 4.1 Create tests/Unit/ProfileChangeIsolationTest.php
- [ ] 4.2 Process days 1-3, change profile, process day 4 — verify days 1-3 unchanged
- [ ] 4.3 Create tests/Unit/DoubleProcessingGuardTest.php
- [ ] 4.4 Call moveDayForward twice — verify second produces no new rows
- [ ] 4.5 Create tests/Unit/DeterminismTest.php
- [ ] 4.6 Run same scenario twice (with clean between) — verify identical holdings

## 5. Verification

- [ ] 5.1 Run `make test` — all unit tests pass
- [ ] 5.2 Verify tests use RefreshDatabase trait and real MySQL
