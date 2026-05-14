## Context

The canonical TypeScript contract in `ASSIGNMENT.md` defines exact request/response shapes for all 12 endpoints. The marking harness will use these exact shapes. Our implemented code (createMember, addTransactions, setDailyUnitPrices) and our unimplemented specs both diverge from the contract on field names, nesting structure, and required fields.

## Goals / Non-Goals

**Goals:**
- Every implemented endpoint accepts exactly the input shape from the canonical contract
- Every implemented endpoint returns exactly the output shape from the canonical contract
- All active OpenSpec specs for unimplemented endpoints are corrected to match the canonical contract
- Members table stores all fields from `CreateMemberInput` (`firstName`, `lastName`, `dateOfBirth`)
- `MockAdminApi` response shapes match what controllers will need

**Non-Goals:**
- Implementing unimplemented endpoints (that's separate batches D/E/F)
- Changing the `AdminApiInterface` method signatures (they take generic arrays)
- Adding new endpoints or features

## Decisions

### 1. Add columns to members table via new migration
Add `first_name`, `last_name`, `date_of_birth` columns (all nullable for backwards compat with existing test data). The controller will validate they're present on createMember, but the DB allows null so resetSubjectState + re-create flows work cleanly.

### 2. Controller reads flat top-level fields directly
No intermediate DTO or FormRequest. The controller plucks `firstName`, `lastName`, `email`, `mobile`, `dateOfBirth`, `initialInvestmentProfile` directly from `$request->json()->all()`. Simple and matches the contract 1:1.

### 3. Rename `price` → `unitPrice` in setDailyUnitPrices
Both the request field and the database column should use `unit_price` (which is what the column is already named). The controller just reads from the wrong key.

### 4. Fix MockAdminApi response shapes in-place
`getTransactionHistory` returns `transactionId` (not `id`). `getHoldings` includes `effectiveDate` per holding. These are internal return shapes consumed by future controllers.

### 5. Update active specs with canonical field names
Each active spec that references input fields must match the TypeScript interface exactly: include `memberId`, `accountId` where the contract requires them, use `allocations` not `investmentProfile` for setInvestmentProfile, etc.

## Risks / Trade-offs

- [Risk] Existing audit_operations rows reference old-format data → No mitigation needed; `make clean` resets for harness runs
- [Risk] Adding columns without backfilling → Acceptable; clean DB assumed for marking
- [Trade-off] Nullable name columns vs required → Chose nullable at DB level so the mock can function without full validation at the storage layer; validation happens at controller layer
