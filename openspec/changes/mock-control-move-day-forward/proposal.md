## Why

Daily holdings processing is the core calculation engine. It transforms transactions into holdings by applying investment profile allocations and unit prices. Without this, the system cannot produce balance data. This is the most algorithmically complex piece of the platform.

## What Changes

- Implement `POST /mock/moveDayForward` accepting `{ days?: number }` (default 1)
- For each day advanced:
  1. Find all eligible accounts (have prior holdings OR transactions on that day)
  2. Get active investment profile for each account
  3. Collect transactions effective that day → compute net cash flow
  4. Allocate cash flow by profile percentages (floor to 2dp, remainder to last asset)
  5. Get unit prices (carry forward last known if missing, default 1.0 if none ever set)
  6. Calculate new_units = previous_units + allocated_cashflow / unit_price
  7. Calculate balance = units * unit_price (rounded to 2dp)
  8. Persist holdings snapshot (one row per account × asset_code × date)
- Advance system_state.current_date
- Guard against double-processing (don't re-process days already past cursor)
- Return `{ ok: true, processedDates: [...] }`

## Capabilities

### New Capabilities
- `move-day-forward`: Daily holdings processing engine with allocation, pricing, and snapshot persistence

### Modified Capabilities

(none)

## Impact

- Adds moveDayForward method to MockControlController
- Adds `POST /mock/moveDayForward` route
- Reads/writes: system_state, investment_profiles, transactions, unit_prices, holdings
- Heavy computation: this method touches more tables than any other
