## 1. Implementation

- [x] 1.1 Add resetSubjectState method to MockControlController
- [x] 1.2 Look up member by userId, delete if found (cascades handle the rest)
- [x] 1.3 Return `{ ok: true }` regardless of whether member existed
- [x] 1.4 Add `POST /mock/resetSubjectState` route

## 2. Verification

- [x] 2.1 Create member, add data, reset — verify all vendor data gone
- [x] 2.2 Verify audit_operations and audit_events still present after reset
- [x] 2.3 Verify createMember works again for same userId after reset
- [x] 2.4 Verify reset of non-existent userId returns ok: true
