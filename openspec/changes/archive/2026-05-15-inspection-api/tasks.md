## 1. Controller Setup

- [x] 1.1 Create InspectionController (app/Http/Controllers/InspectionController.php)

## 2. getRequestAudit

- [x] 2.1 Implement getRequestAudit: query audit_operations by userId + operationId
- [x] 2.2 If not found, return `{ ok: false, error }`
- [x] 2.3 Load associated audit_events ordered by id asc
- [x] 2.4 Format response: userId, operationId, operation, status, events[{at, type, details}]
- [x] 2.5 Add `POST /inspection/getRequestAudit` route

## 3. listAuditEvents

- [x] 3.1 Implement listAuditEvents: query all audit_events for userId (join through audit_operations.user_id)
- [x] 3.2 Order by audit_events.id ascending
- [x] 3.3 Format response: flat array of {at, type, details}
- [x] 3.4 Return empty array for userId with no history
- [x] 3.5 Add `POST /inspection/listAuditEvents` route

## 4. Verification

- [x] 4.1 Create member, perform mutations, then verify getRequestAudit returns correct structure
- [x] 4.2 Verify listAuditEvents returns flat ordered list across multiple operations
- [x] 4.3 Verify non-existent operationId returns error
- [x] 4.4 Verify non-existent userId returns empty events array
