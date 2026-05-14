Status: ready-for-agent

# Mock Control: addTransactions + setDailyUnitPrices

## What to build

Implement `POST /mock/addTransactions` and `POST /mock/setDailyUnitPrices`. The mock controller injects MockAdminApi (concrete class) directly. These are harness-only methods that accept platform IDs (userId, accountId) and resolve internally.

addTransactions generates a transactionId (UUID) for each transaction and persists them. setDailyUnitPrices upserts unit prices by asset_code + date.

## Acceptance criteria

- [ ] `POST /mock/addTransactions` accepts `{ userId, accountId, transactions: NewTransaction[] }`, returns `{ ok, addedCount }`
- [ ] Each transaction gets a generated transactionId
- [ ] `POST /mock/setDailyUnitPrices` accepts `{ date, prices: DailyUnitPrice[] }`, returns `{ ok: true }`
- [ ] Prices are upserted (setting same date+asset again overwrites)
- [ ] Transactions are visible via getTransactionHistory after insertion
- [ ] No audit trail for mock control operations

## Blocked by

- 02-admin-api-interface-and-mock
