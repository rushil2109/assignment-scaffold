## Why

The test harness needs to inject transactions and unit prices into the system to set up scenarios for daily holdings processing. These mock control endpoints drive the vendor-side state without going through the public API (no audit trail, no ID mapping indirection).

## What Changes

- Implement `POST /mock/addTransactions` — accepts userId + accountId + array of transactions, generates transactionIds, persists via MockAdminApi
- Implement `POST /mock/setDailyUnitPrices` — accepts date + array of {assetCode, price}, upserts into unit_prices table
- Mock controller injects MockAdminApi (concrete class) directly, not the interface
- No audit trail for mock control operations

## Capabilities

### New Capabilities
- `mock-add-transactions`: Endpoint to inject transactions with generated IDs
- `mock-set-daily-unit-prices`: Endpoint to upsert unit prices by asset+date

### Modified Capabilities

(none)

## Impact

- Creates `app/Http/Controllers/MockControlController.php`
- Adds two routes in routes/api.php under mock prefix
- Uses MockAdminApi directly (concrete class injection)
- Adds methods to MockAdminApi if needed for direct transaction/price insertion
