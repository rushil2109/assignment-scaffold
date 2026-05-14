## 1. Implementation

- [ ] 1.1 Add resetSubjectState method to MockControlController
- [ ] 1.2 Look up member by userId, delete if found (cascades handle the rest)
- [ ] 1.3 Return `{ ok: true }` regardless of whether member existed
- [ ] 1.4 Add `POST /mock/resetSubjectState` route

## 2. Verification

- [ ] 2.1 Create member, add data, reset — verify all vendor data gone
- [ ] 2.2 Verify audit_operations and audit_events still present after reset
- [ ] 2.3 Verify createMember works again for same userId after reset
- [ ] 2.4 Verify reset of non-existent userId returns ok: true
