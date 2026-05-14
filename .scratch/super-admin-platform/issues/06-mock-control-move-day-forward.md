Status: ready-for-agent

# Mock Control: moveDayForward (daily holdings processing)

## What to build

Implement `POST /mock/moveDayForward`. This is the core daily processing engine. Advances system_state.current_date by N days (default 1), and for each day processes all eligible accounts:

1. Get active investment profile
2. Collect transactions effective that day
3. Compute net cash flow
4. Allocate cash flow by profile percentages (floor to 2dp, remainder to last asset)
5. Get unit prices for that day (carry forward last known if missing, default 1.0 if none ever set)
6. Calculate new_units = previous_units + allocated_cashflow / unit_price
7. Calculate balance = units * unitPrice (rounded to 2dp)
8. Persist holdings snapshot

Only process accounts that have prior holdings OR transactions on that day. Guard against double-processing (don't reprocess a day already past the cursor).

## Acceptance criteria

- [ ] `POST /mock/moveDayForward` accepts `{ days?: number }`, returns `{ ok, processedDates: IsoDate[] }`
- [ ] Defaults to 1 day if days omitted
- [ ] Holdings snapshot persisted for every processed account per day
- [ ] Rounding rule: floor each allocation to 2dp, remainder to last asset
- [ ] sum(allocations) == net_cash_flow invariant holds
- [ ] Unit prices carry forward from last known date if not set
- [ ] Accounts with no prior holdings and no transactions are skipped
- [ ] Double-processing guard: calling again without advancing returns empty processedDates
- [ ] Days with no transactions carry forward previous units at new prices

## Blocked by

- 05-mock-control-transactions-prices
