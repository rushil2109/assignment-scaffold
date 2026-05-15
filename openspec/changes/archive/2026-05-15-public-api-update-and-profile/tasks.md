## 1. Shared Helpers

- [x] 1.1 Extract allocation validation into a reusable private method or validator (from createMember)
- [x] 1.2 Extract userId → member → adminId resolution into a shared helper method

## 2. updateMember Endpoint

- [x] 2.1 Add updateMember method to PublicApiController
- [x] 2.2 Validate: userId and memberId required, at least one updatable field present
- [x] 2.3 Resolve userId + memberId → member → adminId, return error if member not found
- [x] 2.4 Call AdminApiInterface::updateMember with adminId and data
- [x] 2.5 Write audit (operation: updateMember, event type: member_updated)
- [x] 2.6 Add `POST /public/updateMember` route

## 3. setInvestmentProfile Endpoint

- [x] 3.1 Add setInvestmentProfile method to PublicApiController
- [x] 3.2 Validate: userId, memberId, accountId required; validate allocations using shared validator
- [x] 3.3 Resolve userId + memberId + accountId → member → adminId, return error if member not found or accountId mismatch
- [x] 3.4 Call AdminApiInterface::setInvestmentProfile with adminId and allocations (key: `allocations`)
- [x] 3.5 Write audit (operation: setInvestmentProfile, event type: profile_updated)
- [x] 3.6 Add `POST /public/setInvestmentProfile` route

## 4. Verification

- [x] 4.1 Test updateMember with valid partial data — verify response and persistence
- [x] 4.2 Test updateMember with missing memberId — verify error
- [x] 4.3 Test updateMember with missing member — verify error
- [x] 4.4 Test setInvestmentProfile with valid allocations and all IDs — verify profile change
- [x] 4.5 Test setInvestmentProfile with missing accountId — verify error
- [x] 4.6 Test setInvestmentProfile with invalid allocations — verify error
