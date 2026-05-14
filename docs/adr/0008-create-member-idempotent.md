# createMember is idempotent on userId

A second call to `createMember` with the same `userId` returns the existing `memberId`, `accountId`, and the original `operationId` — as if it succeeded. No error, no duplicate, no new audit trail. This makes retries deterministic and safe.
