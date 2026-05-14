# Investment profiles are append-only with an is_current flag

When a new investment profile is set, the previous one is marked `is_current = false` and a new row is inserted with `is_current = true`. Historical profiles are never deleted or modified.

This makes the "profile changes affect future only" invariant trivial to enforce — `moveDayForward` grabs the profile where `is_current = true` at processing time. Past holdings snapshots remain tied to the profile that was active when they were calculated.

## Considered Options

- Update-in-place (simpler, but loses history and makes the "future only" invariant harder to reason about)
- Separate history table (unnecessary indirection for this use case)
