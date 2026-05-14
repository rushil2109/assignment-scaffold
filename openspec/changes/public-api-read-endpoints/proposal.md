## Why

Members need to view their portfolio allocations, transaction history, and current holdings. These are the three read-only endpoints completing the public API surface. They call through AdminApiInterface like all public endpoints but create no audit trail.

## What Changes

- Implement `POST /public/getInvestmentPortfolio` — returns current active allocations
- Implement `POST /public/getTransactionHistory` — returns transactions with optional date filtering, deterministic ordering
- Implement `POST /public/getHoldings` — returns holdings for a specific date (or latest)
- All resolve userId → adminId, call AdminApiInterface, format response in camelCase
- No audit trail for read operations

## Capabilities

### New Capabilities
- `public-get-investment-portfolio`: Read endpoint returning current allocations
- `public-get-transaction-history`: Read endpoint with date range filtering
- `public-get-holdings`: Read endpoint with asOfDate parameter

### Modified Capabilities

(none)

## Impact

- Adds three methods to PublicApiController
- Adds three routes in routes/api.php under public prefix
- No new models or services needed — reuses AdminApiInterface
