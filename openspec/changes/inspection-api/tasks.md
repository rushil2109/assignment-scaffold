## 1. Controller Setup

- [ ] 1.1 Create InspectionController (app/Http/Controllers/InspectionController.php)

## 2. getRequestAudit

- [ ] 2.1 Implement getRequestAudit: query audit_operations by userId + operationId
- [ ] 2.2 If not found, return `{ ok: false, error }`
- [ ] 2.3 Load associated audit_events ordered by id asc
- [ ] 2.4 Format response: userId, operationId, operation, status, events[{at, type, details}]
- [ ] 2.5 Add `POST /inspection/getRequestAudit` route

## 3. listAuditEvents

- [ ] 3.1 Implement listAuditEvents: query all audit_events for userId (join through audit_operations.user_id)
- [ ] 3.2 Order by audit_events.id ascending
- [ ] 3.3 Format response: flat array of {at, type, details}
- [ ] 3.4 Return empty array for userId with no history
- [ ] 3.5 Add `POST /inspection/listAuditEvents` route

## 4. Verification

- [ ] 4.1 Create member, perform mutations, then verify getRequestAudit returns correct structure
- [ ] 4.2 Verify listAuditEvents returns flat ordered list across multiple operations
- [ ] 4.3 Verify non-existent operationId returns error
- [ ] 4.4 Verify non-existent userId returns empty events array
