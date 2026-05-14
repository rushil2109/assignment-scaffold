## 1. Audit Service

- [ ] 1.1 Create AuditOperation model (app/Models/AuditOperation.php)
- [ ] 1.2 Create AuditEvent model (app/Models/AuditEvent.php)
- [ ] 1.3 Create AuditService (app/Services/AuditService.php) with startOperation, recordEvent, completeOperation methods

## 2. Public API Controller

- [ ] 2.1 Create PublicApiController (app/Http/Controllers/PublicApiController.php)
- [ ] 2.2 Inject AdminApiInterface and AuditService via constructor
- [ ] 2.3 Implement createMember method: validate input, check idempotency, generate IDs, call admin API, set profile, write audit, return response

## 3. Validation

- [ ] 3.1 Implement investment profile allocation validation (sum 100.00, valid codes, no duplicates, max 2dp)
- [ ] 3.2 Return `{ ok: false, error }` on validation failure before any side effects

## 4. Routing

- [ ] 4.1 Add `POST /public/createMember` route in routes/api.php pointing to PublicApiController@createMember

## 5. Verification

- [ ] 5.1 Test successful createMember via curl/HTTP — verify camelCase response
- [ ] 5.2 Test idempotency — same userId returns same IDs
- [ ] 5.3 Test validation failures — bad allocations return error
- [ ] 5.4 Verify audit_operations and audit_events rows are written
