## ADDED Requirements

### Requirement: Start an audit operation
The AuditService SHALL provide a method to start an audit operation that generates an operationId (UUID), persists it to audit_operations with userId, operation name, and status "pending", and returns the operationId.

#### Scenario: Operation started
- **WHEN** startOperation is called with userId and operation name
- **THEN** a new row is inserted in audit_operations with a generated UUID and status "pending"

### Requirement: Record an audit event
The AuditService SHALL provide a method to record an event against an existing operationId with a type string and optional details (JSON-serializable).

#### Scenario: Event recorded
- **WHEN** recordEvent is called with operationId, type, and details
- **THEN** a new row is inserted in audit_events linked to that operation

### Requirement: Complete an audit operation
The AuditService SHALL provide a method to mark an operation as complete with a final status (success or failed).

#### Scenario: Operation completed successfully
- **WHEN** completeOperation is called with operationId and status "success"
- **THEN** the audit_operations row is updated with status "success"

### Requirement: Audit survives member deletion
Audit operations and events SHALL NOT be deleted when the associated member is deleted (no FK cascade from members to audit tables).

#### Scenario: Member deleted, audit preserved
- **WHEN** a member is deleted
- **THEN** all audit_operations and audit_events for that user_id remain queryable
