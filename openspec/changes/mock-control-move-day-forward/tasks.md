## 1. Core Algorithm

- [ ] 1.1 Implement moveDayForward method on MockControlController
- [ ] 1.2 Read system_state.current_date as the starting cursor
- [ ] 1.3 Loop day-by-day for N days (next_date = cursor + 1 for each iteration)
- [ ] 1.4 Find eligible accounts: accounts with prior holdings OR transactions on processing date
- [ ] 1.5 For each eligible account, get active investment profile (is_current=true)

## 2. Cash Flow and Allocation

- [ ] 2.1 Sum transactions for account on processing date → net cash flow
- [ ] 2.2 Allocate cash flow: floor(cashflow * percentage / 100, 2) for each asset, sorted alphabetically by asset_code
- [ ] 2.3 Assign remainder (cashflow - sum of floored allocations) to last asset alphabetically
- [ ] 2.4 Verify sum(allocations) == net_cash_flow invariant

## 3. Unit Prices and Holdings

- [ ] 3.1 Look up unit price per asset for processing date; carry forward if missing; default 1.0 if never set
- [ ] 3.2 Get previous_units from most recent holdings row for each asset (0 if none)
- [ ] 3.3 Calculate new_units = previous_units + allocated_cashflow / unit_price
- [ ] 3.4 Calculate balance = new_units * unit_price, rounded to 2dp
- [ ] 3.5 Insert holdings row (account_id, asset_code, units, unit_price, balance, effective_date)

## 4. State Management

- [ ] 4.1 Advance system_state.current_date after processing each day
- [ ] 4.2 Implement double-processing guard (skip if processing_date <= current system date at call time)
- [ ] 4.3 Return `{ ok: true, processedDates: [...] }`

## 5. Routing

- [ ] 5.1 Add `POST /mock/moveDayForward` route

## 6. Verification

- [ ] 6.1 Test single day advancement with one transaction — verify holdings math
- [ ] 6.2 Test multi-day advancement — verify sequential processing
- [ ] 6.3 Test carry-forward (no transactions day) — verify units unchanged, balance recalculated
- [ ] 6.4 Test double-call guard — verify empty processedDates on second call
- [ ] 6.5 Test rounding with fractional percentages — verify sum invariant
