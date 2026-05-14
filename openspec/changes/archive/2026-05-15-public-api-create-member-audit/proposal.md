## Why

The first public-facing endpoint brings together the full vertical slice: HTTP → controller → ID mapping → AdminApiInterface → audit trail. This establishes the pattern all other public endpoints will follow. The audit service is a platform concern reused by every mutation endpoint.

## What Changes

- Implement `POST /public/createMember` controller accepting JSON body with userId, email, mobile, preferredName, addresses, investmentProfile
- Generate memberId, accountId, operationId (UUIDs) in the controller layer
- Create ID mapping (userId/memberId → adminId) so the public API never exposes adminId
- Call AdminApiInterface::createMember and AdminApiInterface::setInvestmentProfile
- Implement AuditService with methods: startOperation, recordEvent, markComplete
- Audit trail: one operation (status: success) + one event (type: member_created)
- Idempotency: duplicate userId returns existing IDs without creating new audit entries
- Validate investment profile allocations (sum 100.00, valid asset codes, no duplicates, max 2dp)

## Capabilities

### New Capabilities
- `public-create-member`: The createMember HTTP endpoint with validation, ID mapping, and response contract
- `audit-service`: Reusable service for recording audit operations and events

### Modified Capabilities

(none)

## Impact

- Creates `app/Http/Controllers/PublicApiController.php` (or similar)
- Creates `app/Services/AuditService.php`
- Adds route in `routes/api.php` under public prefix
- Creates models: AuditOperation, AuditEvent (if not already created)
- The ID mapping concept (members table already has user_id + admin_id) is used here
