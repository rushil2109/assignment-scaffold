## Why

Members need to update their personal details and change investment allocations after account creation. These are the two remaining mutation endpoints on the public API surface.

## What Changes

- Implement `POST /public/updateMember` — resolves userId → adminId, calls AdminApiInterface::updateMember, writes audit trail
- Implement `POST /public/setInvestmentProfile` — resolves userId → adminId, validates allocations, calls AdminApiInterface::setInvestmentProfile, writes audit trail
- Both return operationId on success
- Error handling: member not found, empty update payload, invalid allocations

## Capabilities

### New Capabilities
- `public-update-member`: The updateMember HTTP endpoint with field validation and audit
- `public-set-investment-profile`: The setInvestmentProfile HTTP endpoint with allocation validation and audit

### Modified Capabilities

(none)

## Impact

- Adds two methods to PublicApiController
- Adds two routes in routes/api.php under public prefix
- Reuses AuditService and AdminApiInterface from issue 03
- Reuses allocation validation logic from createMember
