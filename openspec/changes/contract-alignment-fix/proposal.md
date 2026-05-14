## Why

The implemented controllers and active OpenSpec specs diverge from the canonical TypeScript contract in `ASSIGNMENT.md`. The marking harness will call endpoints using the exact field names and shapes from that contract. Every mismatch is a test failure. This must be fixed before any further implementation.

## What Changes

- **BREAKING** `POST /public/createMember`: Accept flat top-level fields (`firstName`, `lastName`, `email`, `mobile`, `dateOfBirth`, `initialInvestmentProfile`) instead of nested `memberDetails` + `investmentProfile`
- **BREAKING** `POST /mock/setDailyUnitPrices`: Price field in each entry must be `unitPrice` (not `price`)
- **BREAKING** `MockAdminApi::createMember`: Store `firstName`, `lastName`, `dateOfBirth` (requires migration to add columns to members table)
- **BREAKING** `MockAdminApi::getTransactionHistory`: Return `transactionId` (not `id`)
- **BREAKING** `MockAdminApi::getHoldings`: Include `effectiveDate` in each holding object
- Fix all active specs for unimplemented endpoints to require `memberId` and `accountId` where the canonical contract mandates them
- Fix `setInvestmentProfile` spec to use `allocations` (not `investmentProfile`) as the key name
- Fix `getHoldings` spec to accept `memberId` + `accountId` (not just `userId`)
- Fix `getTransactionHistory` spec to accept `memberId` + `accountId` + date filters
- Fix `getInvestmentPortfolio` spec to accept `memberId` + `accountId`
- Fix `updateMember` spec to accept `memberId`

## Capabilities

### New Capabilities
- `contract-alignment-implemented`: Fix all already-implemented code (createMember controller, MockAdminApi, MockControlController, members migration) to match canonical contract exactly
- `contract-alignment-specs`: Fix all active (unimplemented) OpenSpec specs to match canonical contract field names and required fields

### Modified Capabilities

## Impact

- `super-admin/app/Http/Controllers/PublicApiController.php` — rewrite createMember input handling
- `super-admin/app/Http/Controllers/MockControlController.php` — rename `price` to `unitPrice`
- `super-admin/app/Services/MockAdminApi.php` — store name/DOB fields, fix getTransactionHistory and getHoldings response shapes
- `super-admin/database/migrations/` — new migration adding `first_name`, `last_name`, `date_of_birth` columns to members table
- `openspec/changes/public-api-update-and-profile/specs/` — add memberId to input contracts
- `openspec/changes/public-api-read-endpoints/specs/` — add memberId + accountId to input contracts
- `openspec/changes/mock-control-reset-subject/specs/` — no change needed (only takes userId)
- `openspec/changes/mock-control-move-day-forward/specs/` — no change needed
