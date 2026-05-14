## 1. Database Migration

- [x] 1.1 Create migration adding `first_name`, `last_name`, `date_of_birth` columns to members table (all nullable)

## 2. Fix PublicApiController::createMember

- [x] 2.1 Accept flat top-level fields: `userId`, `firstName`, `lastName`, `email`, `mobile`, `dateOfBirth`, `initialInvestmentProfile`
- [x] 2.2 Validate all required fields are present (userId, firstName, lastName, email, mobile, dateOfBirth, initialInvestmentProfile)
- [x] 2.3 Read allocations from `initialInvestmentProfile` (not `investmentProfile`)
- [x] 2.4 Pass all fields to AdminApiInterface::createMember in the data array

## 3. Fix MockAdminApi::createMember

- [x] 3.1 Store `first_name`, `last_name`, `date_of_birth` from input data to members table

## 4. Fix MockControlController::setDailyUnitPrices

- [x] 4.1 Read price value from `unitPrice` field (not `price`) in each prices array entry

## 5. Fix MockAdminApi::getTransactionHistory

- [x] 5.1 Return `transactionId` (not `id`) in each transaction object

## 6. Fix MockAdminApi::getHoldings

- [x] 6.1 Include `effectiveDate` field in each holding object

## 7. Fix Active Specs — public-api-update-and-profile

- [x] 7.1 Update `public-update-member/spec.md`: add `memberId` as required input field
- [x] 7.2 Update `public-set-investment-profile/spec.md`: require `userId`, `memberId`, `accountId`, use `allocations` key (not `investmentProfile`)

## 8. Fix Active Specs — public-api-read-endpoints

- [x] 8.1 Update `public-get-investment-portfolio/spec.md`: require `userId`, `memberId`, `accountId`
- [x] 8.2 Update `public-get-transaction-history/spec.md`: require `userId`, `memberId`, `accountId`, optional `fromDate`, `toDate`
- [x] 8.3 Update `public-get-holdings/spec.md`: require `userId`, `memberId`, `accountId`, optional `asOfDate`

## 9. Verification

- [x] 9.1 Test createMember with canonical request shape via curl
- [x] 9.2 Test setDailyUnitPrices with `unitPrice` field via curl
- [x] 9.3 Verify idempotent createMember still works
