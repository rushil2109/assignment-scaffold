## 1. getInvestmentPortfolio

- [x] 1.1 Add getInvestmentPortfolio method to PublicApiController
- [x] 1.2 Validate: userId, memberId, accountId required
- [x] 1.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [x] 1.4 Call AdminApiInterface::getInvestmentPortfolio, format response with camelCase keys
- [x] 1.5 Add `POST /public/getInvestmentPortfolio` route

## 2. getTransactionHistory

- [x] 2.1 Add getTransactionHistory method to PublicApiController
- [x] 2.2 Validate: userId, memberId, accountId required
- [x] 2.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [x] 2.4 Pass optional fromDate/toDate to AdminApiInterface::getTransactionHistory
- [x] 2.5 Format response: transactionId, type, amount, effectiveDate — all camelCase
- [x] 2.6 Add `POST /public/getTransactionHistory` route

## 3. getHoldings

- [x] 3.1 Add getHoldings method to PublicApiController
- [x] 3.2 Validate: userId, memberId, accountId required
- [x] 3.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [x] 3.4 Call AdminApiInterface::getHoldings with optional asOfDate
- [x] 3.5 Format response: assetCode, units, unitPrice, balance, effectiveDate — all camelCase
- [x] 3.6 Add `POST /public/getHoldings` route

## 4. Verification

- [x] 4.1 Test getInvestmentPortfolio returns current allocations after profile set
- [x] 4.2 Test getTransactionHistory with and without date filters
- [x] 4.3 Test getHoldings with asOfDate, without asOfDate, and with empty result
- [x] 4.4 Verify no audit rows created for any read operation
- [x] 4.5 Test missing memberId/accountId returns error
