## 1. getInvestmentPortfolio

- [ ] 1.1 Add getInvestmentPortfolio method to PublicApiController
- [ ] 1.2 Validate: userId, memberId, accountId required
- [ ] 1.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [ ] 1.4 Call AdminApiInterface::getInvestmentPortfolio, format response with camelCase keys
- [ ] 1.5 Add `POST /public/getInvestmentPortfolio` route

## 2. getTransactionHistory

- [ ] 2.1 Add getTransactionHistory method to PublicApiController
- [ ] 2.2 Validate: userId, memberId, accountId required
- [ ] 2.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [ ] 2.4 Pass optional fromDate/toDate to AdminApiInterface::getTransactionHistory
- [ ] 2.5 Format response: transactionId, type, amount, effectiveDate — all camelCase
- [ ] 2.6 Add `POST /public/getTransactionHistory` route

## 3. getHoldings

- [ ] 3.1 Add getHoldings method to PublicApiController
- [ ] 3.2 Validate: userId, memberId, accountId required
- [ ] 3.3 Resolve userId + memberId + accountId → member → adminId, return error if not found or accountId mismatch
- [ ] 3.4 Call AdminApiInterface::getHoldings with optional asOfDate
- [ ] 3.5 Format response: assetCode, units, unitPrice, balance, effectiveDate — all camelCase
- [ ] 3.6 Add `POST /public/getHoldings` route

## 4. Verification

- [ ] 4.1 Test getInvestmentPortfolio returns current allocations after profile set
- [ ] 4.2 Test getTransactionHistory with and without date filters
- [ ] 4.3 Test getHoldings with asOfDate, without asOfDate, and with empty result
- [ ] 4.4 Verify no audit rows created for any read operation
- [ ] 4.5 Test missing memberId/accountId returns error
