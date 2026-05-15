## Why

The marking harness needs to verify that the audit trail is correctly maintained. The inspection API provides read-only access to audit data, allowing verification of operation history and event sequencing for any given user.

## What Changes

- Implement `POST /inspection/getRequestAudit` — given userId + operationId, returns the full RequestAudit object (userId, operationId, operation, status, events[])
- Implement `POST /inspection/listAuditEvents` — given userId, returns all audit events across all operations for that user, ordered by id (flat list)
- Events include: at (IsoDateTime), type, details
- Error handling for operationId not found

## Capabilities

### New Capabilities
- `inspection-get-request-audit`: Single operation audit retrieval with associated events
- `inspection-list-audit-events`: Flat list of all events for a user across operations

### Modified Capabilities

(none)

## Impact

- Creates `app/Http/Controllers/InspectionController.php`
- Adds two routes in routes/api.php under inspection prefix
- Reads from audit_operations and audit_events tables
- No new models needed (AuditOperation and AuditEvent already exist from issue 03)
