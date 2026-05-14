Status: ready-for-agent

# Public API: updateMember + setInvestmentProfile

## What to build

Implement `POST /public/updateMember` and `POST /public/setInvestmentProfile`. Both resolve userId → adminId, call the AdminApiInterface, write audit trail, and return operationId.

updateMember supports email, mobile, preferredName, residentialAddress, postalAddress (addresses stored as JSON). Invalid updates (member not found, nothing to update) fail with clear error.

setInvestmentProfile validates allocations (sum to 100.00, valid codes, up to 2dp percentages), then calls the interface. Profile changes affect future processing only.

## Acceptance criteria

- [ ] `POST /public/updateMember` persists changes and emits audit event (type: member_updated)
- [ ] Invalid memberId or empty update returns `{ ok: false, error }`
- [ ] `POST /public/setInvestmentProfile` validates and persists new profile
- [ ] Invalid allocations (wrong sum, bad codes, duplicates) return clear error
- [ ] Both return operationId
- [ ] Subsequent reads reflect the update

## Blocked by

- 03-public-api-create-member-audit
