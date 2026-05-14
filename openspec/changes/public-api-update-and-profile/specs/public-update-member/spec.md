## ADDED Requirements

### Requirement: updateMember endpoint
The system SHALL expose `POST /public/updateMember` accepting a JSON body with userId (required) and optional fields: email, mobile, preferredName, residentialAddress, postalAddress.

#### Scenario: Valid update
- **WHEN** a POST is made with a valid userId and at least one updatable field
- **THEN** the system updates the member, writes audit, and returns `{ ok: true, operationId }`

#### Scenario: Member not found
- **WHEN** a POST is made with a userId that doesn't exist
- **THEN** the system returns `{ ok: false, error: "..." }`

#### Scenario: No updatable fields provided
- **WHEN** a POST is made with only userId (no other fields)
- **THEN** the system returns `{ ok: false, error: "..." }`

### Requirement: updateMember writes audit trail
The system SHALL create an audit operation with event type "member_updated" including the changed fields in details.

#### Scenario: Audit recorded on update
- **WHEN** updateMember completes successfully
- **THEN** an audit_operation (status: success) and audit_event (type: member_updated) are persisted

### Requirement: Address fields stored as JSON
residentialAddress and postalAddress SHALL be stored as JSON objects and returned as objects in subsequent reads.

#### Scenario: Address update
- **WHEN** updateMember is called with residentialAddress as an object
- **THEN** the address is stored as JSON and can be retrieved as the same object structure
