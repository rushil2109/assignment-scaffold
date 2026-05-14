# Controller resolves userId to adminId before calling the Admin API

The public controllers look up `adminId` from the members table and pass it into the `AdminApiInterface`. The interface methods accept `adminId` as their only identifier — no `accountId` or `memberId` crosses the boundary. The mapping lives on the platform side, not inside the vendor boundary.

The vendor side (mock implementation) maintains its own internal concept of accounts. The platform-side `accountId` is never passed into the interface. `createMember` returns an `adminId` which the controller stores alongside the generated `memberId` and `accountId`.

This keeps the interface clean — a real vendor wouldn't know our platform IDs. The boundary only speaks the vendor's language.
