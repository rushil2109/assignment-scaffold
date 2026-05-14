## 1. Interface Definition

- [ ] 1.1 Create `app/Contracts/AdminApiInterface.php` with 6 method signatures (createMember, updateMember, setInvestmentProfile, getInvestmentPortfolio, getTransactionHistory, getHoldings)
- [ ] 1.2 All methods accept `string $adminId` as first parameter; return type is `array`

## 2. Eloquent Models

- [ ] 2.1 Create Member model (app/Models/Member.php) with fillable fields and account relationship
- [ ] 2.2 Create Account model (app/Models/Account.php) with member and profiles relationships
- [ ] 2.3 Create InvestmentProfile model (app/Models/InvestmentProfile.php)
- [ ] 2.4 Create Transaction model (app/Models/Transaction.php)
- [ ] 2.5 Create Holding model (app/Models/Holding.php)
- [ ] 2.6 Create UnitPrice model (app/Models/UnitPrice.php)

## 3. Mock Implementation

- [ ] 3.1 Create `app/Services/MockAdminApi.php` implementing AdminApiInterface
- [ ] 3.2 Implement createMember: generate UUID adminId, persist member + account, return IDs
- [ ] 3.3 Implement updateMember: find member by adminId, update provided fields, throw if not found
- [ ] 3.4 Implement setInvestmentProfile: flip is_current on old rows, insert new rows with is_current=true
- [ ] 3.5 Implement getInvestmentPortfolio: query is_current=true profiles for member's account
- [ ] 3.6 Implement getTransactionHistory: query transactions with optional date filtering, ordered by effective_date asc, id asc
- [ ] 3.7 Implement getHoldings: query holdings by asOfDate (or latest date if null)

## 4. Service Container Binding

- [ ] 4.1 Add singleton binding in AppServiceProvider::register() — AdminApiInterface → MockAdminApi

## 5. Verification

- [ ] 5.1 Verify interface can be resolved from container
- [ ] 5.2 Verify MockAdminApi::createMember persists data correctly
- [ ] 5.3 Verify setInvestmentProfile append-only behavior works
