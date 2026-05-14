Status: ready-for-agent

# AdminApiInterface + MockAdminApi (create/update/profile)

## What to build

Define `AdminApiInterface` with 6 methods (createMember, updateMember, setInvestmentProfile, getInvestmentPortfolio, getTransactionHistory, getHoldings) — all accepting `adminId` as their only identifier. Implement `MockAdminApi` as the concrete class backed by MySQL. Bind the interface to the mock in the service container as a singleton.

The mock generates its own `adminId` (UUID) on createMember and stores member data, accounts, and investment profiles. `setInvestmentProfile` marks previous profile as `is_current = false` and inserts new one as `is_current = true`.

## Acceptance criteria

- [ ] `AdminApiInterface` defined with 6 methods taking `adminId`
- [ ] `MockAdminApi` implements all 6 interface methods
- [ ] Service container binds interface → MockAdminApi (singleton)
- [ ] Public controllers can inject the interface, mock controller can inject the concrete class
- [ ] `createMember` generates adminId and persists member + account
- [ ] `setInvestmentProfile` is append-only with is_current flag

## Blocked by

- 01-schema-bootstrap-clean
