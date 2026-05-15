## Context

These endpoints follow the pattern established by createMember: resolve userId → adminId, call interface, audit, respond. The allocation validation logic already exists from createMember and should be extracted into a shared validator or helper.

## Goals / Non-Goals

**Goals:**
- Two working POST endpoints matching canonical contract
- Shared allocation validation (reused from createMember)
- Clear error messages for all failure cases
- Audit trail with appropriate event types

**Non-Goals:**
- Read endpoints (getInvestmentPortfolio, getTransactionHistory, getHoldings — separate issue)
- Changing past holdings on profile update (explicitly a non-goal per spec)

## Decisions

**Extract allocation validation into a helper/trait**
Both createMember and setInvestmentProfile validate allocations identically. Extract to a private method or a dedicated validator class. Rationale: DRY, single place to maintain validation rules.

**userId resolution as a shared helper**
Both endpoints need userId → member → adminId lookup. Extract to a private method in the controller. Rationale: consistent error handling for "member not found".

**updateMember accepts partial data**
Only provided fields are updated. If no updatable fields are provided, return error. Updatable fields: email, mobile, preferredName, residentialAddress, postalAddress. Rationale: matches typical PATCH semantics but via POST RPC.

**Audit event types**
- updateMember → event type "member_updated" with changed fields in details
- setInvestmentProfile → event type "profile_updated" with new allocations in details

## Risks / Trade-offs

- [Shared validation extraction] → Slightly more refactoring of createMember. Low risk, net positive.
- [Partial update with no fields] → Must distinguish "no updatable fields" from "empty body". Validate explicitly.
