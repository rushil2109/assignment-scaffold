Status: ready-for-agent

# Public API: getInvestmentPortfolio + getTransactionHistory + getHoldings

## What to build

Implement the three read-only public API endpoints. Each resolves userId → adminId, calls AdminApiInterface, and returns data in canonical contract shape.

- `getInvestmentPortfolio`: returns current active allocations
- `getTransactionHistory`: returns transactions ordered by effectiveDate asc then id asc, with optional fromDate/toDate filtering
- `getHoldings`: returns holdings for asOfDate (latest processed day if omitted), empty array if no holdings exist

## Acceptance criteria

- [ ] `POST /public/getInvestmentPortfolio` returns `{ ok, allocations: InvestmentAllocation[] }`
- [ ] `POST /public/getTransactionHistory` returns `{ ok, transactions: Transaction[] }` with deterministic ordering
- [ ] Date filtering works (fromDate, toDate, both, neither)
- [ ] `POST /public/getHoldings` returns `{ ok, holdings: Holding[] }` with assetCode, units, unitPrice, balance, effectiveDate
- [ ] asOfDate omitted returns latest day's holdings
- [ ] asOfDate with no data returns empty array
- [ ] All responses use camelCase keys
- [ ] No audit trail for read operations

## Blocked by

- 06-mock-control-move-day-forward
