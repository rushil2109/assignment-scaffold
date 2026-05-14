## 1. Mock Controller Setup

- [x] 1.1 Create MockControlController (app/Http/Controllers/MockControlController.php)
- [x] 1.2 Inject MockAdminApi (concrete class) via constructor

## 2. addTransactions Endpoint

- [x] 2.1 Implement addTransactions method: resolve userId → member → account, generate UUID per transaction, persist all
- [x] 2.2 Return `{ ok: true, addedCount: N }`
- [x] 2.3 Add `POST /mock/addTransactions` route

## 3. setDailyUnitPrices Endpoint

- [x] 3.1 Implement setDailyUnitPrices method: upsert (updateOrCreate) each price by asset_code + date
- [x] 3.2 Return `{ ok: true }`
- [x] 3.3 Add `POST /mock/setDailyUnitPrices` route

## 4. Verification

- [x] 4.1 Add transactions via mock endpoint, then verify via getTransactionHistory
- [x] 4.2 Set prices, set again for same date — verify only latest value exists
- [x] 4.3 Confirm no audit rows created by mock operations
