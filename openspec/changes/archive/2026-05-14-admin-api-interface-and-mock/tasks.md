## 1. Interface Definition

- [x] 1.1 Create `app/Contracts/AdminApiInterface.php` with 6 method signatures (createMember, updateMember, setInvestmentProfile, getInvestmentPortfolio, getTransactionHistory, getHoldings)
- [x] 1.2 All methods accept `string $adminId` as first parameter; return type is `array`

## 2. Eloquent Models

- [x] 2.1 Create Member model (app/Models/Member.php) with fillable fields and account relationship
- [x] 2.2 Create Account model (app/Models/Account.php) with member and profiles relationships
- [x] 2.3 Create InvestmentProfile model (app/Models/InvestmentProfile.php)
- [x] 2.4 Create Transaction model (app/Models/Transaction.php)
- [x] 2.5 Create Holding model (app/Models/Holding.php)
- [x] 2.6 Create UnitPrice model (app/Models/UnitPrice.php)

## 3. Mock Implementation

- [x] 3.1 Create `app/Services/MockAdminApi.php` implementing AdminApiInterface
- [x] 3.2 Implement createMember: generate UUID adminId, persist member + account, return IDs
- [x] 3.3 Implement updateMember: find member by adminId, update provided fields, throw if not found
- [x] 3.4 Implement setInvestmentProfile: flip is_current on old rows, insert new rows with is_current=true
- [x] 3.5 Implement getInvestmentPortfolio: query is_current=true profiles for member's account
- [x] 3.6 Implement getTransactionHistory: query transactions with optional date filtering, ordered by effective_date asc, id asc
- [x] 3.7 Implement getHoldings: query holdings by asOfDate (or latest date if null)

## 4. Service Container Binding

- [x] 4.1 Add singleton binding in AppServiceProvider::register() — AdminApiInterface → MockAdminApi

## 5. Verification

- [x] 5.1 Verify interface can be resolved from container
- [x] 5.2 Verify MockAdminApi::createMember persists data correctly
- [x] 5.3 Verify setInvestmentProfile append-only behavior works
