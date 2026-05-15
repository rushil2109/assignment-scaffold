# JOURNAL

## System Shape

```
+---------------------------------------------------------------------+
|                    Single Laravel Process (:9001)                    |
+---------+-----------+---------------------+-------------------------+
| POST /public/*      | POST /mock/*        | POST /inspection/*      |
| (6 endpoints)       | (4 endpoints)       | (2 endpoints)           |
+---------+-----------+---------------------+-------------------------+
|                        Route Groups (api.php)                       |
+--------------------+------------------------+-----------------------+
| PublicApiController | MockControlController  | InspectionController  |
+--------------------+------------------------+-----------------------+
|            |                                        |               |
|   AdminApiInterface (PHP interface)          AuditService           |
|            |                                        |               |
|   MockAdminApi (Eloquent/MySQL)              audit_operations        |
|                                              audit_events            |
+---------------------------------------------------------------------+
|                          MySQL 8.0                                   |
| members | accounts | investment_profiles | transactions | holdings  |
| unit_prices | audit_operations | audit_events | system_state        |
+---------------------------------------------------------------------+
```

Single process, three route groups. One command to start. The spec explicitly said simple architecture is preferred — I took that at face value and didn't add complexity that wasn't earning its place.

---

## How I Read the Spec

My first read left me genuinely confused about the distinction between the Mock Control API and the Admin API boundary. I couldn't tell if they were the same thing, parallel things, or one wrapping the other.

Before writing any code I used a few Claude-based tools — opsx:explore, grill-with-docs, and grill-me — to work through the spec properly. The process was: feed the spec in, get interrogated on it, answer questions, expose the gaps in my understanding, repeat. What eventually clicked was thinking about the Mock Control API as puppet strings. In production, transactions arrive and prices update from the outside world. Here, nothing happens in that vendor world unless the harness pulls the strings explicitly. The Mock Control API is how you simulate that external activity. The Admin API is the internal code boundary your server crosses to reach the vendor world — mock or real. Once I separated those two things in my head the whole system made sense.

That clarity before implementation was worth the time investment. I didn't want to be making conceptual corrections halfway through building.

---

## Architecture Decisions

After understanding the spec I spent time on the design phase before touching code. I looked at the tradeoffs between microservices, a modular monolith, and a simple monolith. For a 10-14 hour solo timebox with no real operational complexity requirement, microservices would have added overhead with no actual benefit. The seam in this problem is a code-level interface, not a network boundary. A single process with clean internal boundaries demonstrates the same thing without the operational complexity.

I used grill-with-docs to work through all the low-level design decisions before implementation: table names, the canonical contract shape, ID mapping strategy, invariants, the audit model. That session produced an 11-phase project plan with a spec document, per-phase task lists, and documented decisions. Having that upfront meant I wasn't making design calls mid-implementation under time pressure.

**Language: PHP/Laravel.** I work in it day-to-day. In a timebox, framework familiarity is the dominant factor. The tradeoffs I accepted were: manually translating the TypeScript contract into PHP, fighting Laravel's `snake_case` conventions against the spec's `camelCase` throughout every response, and slightly heavier Docker setup. All worth it for the productivity.

**Admin API as a PHP interface, not HTTP.** The boundary is clean without network overhead. `MockAdminApi` is a full concrete implementation of `AdminApiInterface` backed by Eloquent and MySQL. Swapping to a real vendor SDK is one line in `AppServiceProvider`. That's the whole point of the exercise.

**ID mapping via `admin_id` column on members.** Rather than a separate mapping table, `admin_id` lives as a column on `members`. A `ResolvesMember` trait handles the translation between client-facing identifiers and the admin-side identifier. Simple enough for this scale and straightforward to follow.

**Floor-based rounding with remainder to last asset.** Each asset except the last gets `floor(cashflow * percentage / 100)` to 2dp. The last asset gets `total - sum_of_others`. This guarantees allocations sum exactly to net cash flow with no floating-point drift. Documented and tested.

**Holdings as append-only snapshots.** Each processed day creates new `holdings` rows. Past snapshots are never touched. This naturally satisfies "profile changes affect future only" — old rows are immutable, new rows use whatever profile is current on that day.

**Real MySQL for all tests.** No SQLite. The schema uses MySQL-specific syntax, foreign keys, and unique constraints that matter for correctness. Testing against the real stack gives real confidence. SQLite would have hidden real problems.

---

## Implementation Order and Why

The order was a deliberate dependency chain, not accidental:

1. **Schema first** — nothing else has a foundation until the tables exist
2. **AdminApiInterface + MockAdminApi** — the critical seam, everything else depends on it
3. **Mock control endpoints** — harness needs to seed data before public endpoints are useful
4. **Public API mutations + audit trail** — first full vertical slice: HTTP → controller → interface → DB → audit
5. **Contract alignment** — fixed field name mismatches early before more code built on the wrong shape
6. **Remaining public endpoints** — reads and the computational core (`moveDayForward`)
7. **Unit tests** — invariants locked in formally
8. **Feature tests** — blackbox lifecycle verification
9. **Cleanup** — removed dead boilerplate

Each step left the system in a runnable state. I never had a phase where things were half-broken and untestable. The dependency order meant I could always verify the new thing against the existing foundation.

---

## How I Worked Through the Phases

Once the design phase produced the 11-phase task breakdown, I used opsx:apply to work through implementation. To save time I identified which phases could run in parallel and used git worktrees — one worktree per parallel workstream, complete the tasks, merge back to main. Claude handled the commits after each phase.

After every phase I ran the full test suite to check for regressions, then used the sample frontend to manually verify the new endpoints were wired up correctly. The frontend was genuinely useful here — seeing data flow through the UI confirmed the pieces were connected properly, not just that isolated tests passed. If something was broken in the wiring, the frontend showed it immediately.

---

## Assumptions I Made

These aren't explicitly in the spec but my code depends on them:

**`userId` is trusted as-is.** No auth, no token validation, no access control. The spec said authentication is out of scope and `userId` is the trust boundary. I treated it as an opaque string and moved on.

**Allocations must sum to exactly 100%.** The spec says percentages must be valid but doesn't specify the exact rule. I chose strict equality — no tolerance, no rounding. This is documented in the `ValidAllocations` rule and tested.

**Unit prices carry forward if not set for a given day.** If no price exists for an asset on the processing date, the system falls back to the most recent previous price. If no price has ever been set, it defaults to 1.0. The spec doesn't address this case. It's a real assumption — a harness that never sets prices would get holdings calculated at 1.0 per unit without any error or warning. It's implemented and documented in code but I'll flag it here because it's the kind of implicit behavior that can surprise people.

**Transaction ordering is by `effectiveDate` ascending, then insertion order.** The spec says ordering must be deterministic and documented — that's my rule.

**`resetSubjectState` is scoped to `userId`.** It resets that member's transactions, holdings, and investment profile. It doesn't touch the system clock or other members. This is what makes it useful for harness isolation between runs.

---

## Harness-Friendly Design

A few decisions were made specifically to make the automated harness's job easier:

**Consistent `ok: boolean` envelope on every response.** Every endpoint returns `{ ok: true, ... }` or `{ ok: false, error: "..." }`. The harness can assert pass/fail on a single field without parsing error strings or inspecting HTTP status codes for business logic. This came directly from the TypeScript contract — I kept it consistent throughout rather than letting it drift.

**`operationId` returned on every mutation.** Every mutating public endpoint returns an `operationId` immediately so the harness can query the inspection API for the audit record without any secondary lookup or guessing.

**`bootstrap` and `clean` commands.** The harness can call `bootstrap` to prepare the database from scratch and `clean` to reset between runs. Both are documented in the README and work from a fresh checkout with no hidden setup.

**Deterministic processing.** `moveDayForward` always processes `current_date + 1` and advances the clock. The same inputs always produce the same holdings. The harness can predict exact output values — which is exactly what `FullLifecycleTest` does, asserting specific numerical values rather than just "something was returned."

---

## The Vendor Boundary — How Replaceable Is It?

The entire `MockAdminApi` class is the controlled simulation of the downstream vendor boundary. It's a full concrete implementation of `AdminApiInterface` backed by Eloquent. The mock control API drives it. This is what the spec asks for — a downstream boundary that can be replaced or simulated in a controlled way.

The binding in `AppServiceProvider`:
```php
$this->app->singleton(AdminApiInterface::class, MockAdminApi::class);
```

Swapping to a real vendor SDK means changing that one line and providing a new implementation. Controllers never touch `MockAdminApi` directly — they only know about the interface.

The honest gap: I didn't write an explicit test that swaps the binding and proves the controller still works with a stub. Something like:

```php
$this->app->instance(AdminApiInterface::class, $stub);
$this->postJson('/public/getHoldings', [...]);
// Assert stub was called correctly
```

That would prove the seam is replaceable rather than just claiming it. It's the next thing I'd add — the architecture supports it trivially, I just didn't get there in the timebox.

---

## Mistakes and Course Corrections

**Docker volume mount clobbering vendor/.** My first Docker setup had a simple bind mount that overwrote the `vendor/` directory built during `docker build`. The fix is a named `vendor` volume that preserves container-side dependencies. I partially fixed this during implementation but didn't fully resolve it before the deadline. First thing I'm fixing post-submission.

**Contract field mismatches.** Early on, `createMember` was using a nested response shape rather than the flat structure in the TypeScript contract. This happened because the tool generating code hadn't properly ingested the canonical contract despite me specifically asking it to. Lesson: always verify generated output against the source of truth. The fix was a dedicated cleanup pass — straightforward, but it cost time I didn't need to spend.

**`moveDayForward` logic in the controller.** I only spotted this during the final cleanup phase. Holdings calculation belongs in a `DailyProcessingService` or behind the Admin API boundary — not in `MockControlController` mixed with HTTP concerns. I saw it, documented it, ran out of time to fix it. It's the thing I'm least happy with in the submission.

**Unit price carry-forward not tested as a standalone invariant.** The behavior is implemented and works, covered implicitly by lifecycle tests. But there's no isolated test that says "given no price set for this day, expect carry-forward from previous day." That's a gap.

---

## Testing

92 tests, 342 assertions, all passing.

**Unit tests** cover the domain invariants: allocation math (floor on all but last, last gets the remainder guaranteeing exact sum), holdings calculation (`new_units = prev + cashflow / price`, `balance = round(units * price, 2)`), determinism across separate runs, double-processing guard, member idempotency, profile validation, and profile change isolation.

**Feature tests** cover all 12 endpoints — happy paths and error cases — plus a contract test verifying camelCase keys and `ok: boolean` envelope throughout.

**The lifecycle test** (`FullLifecycleTest`) runs the full sequence: create member → seed transactions → set prices → move day forward → read holdings → assert exact numerical values. This is the closest thing to what the marking harness will do and it's where I get the most confidence that the system actually hangs together.

The two gaps I'd close first: a standalone unit test for unit price carry-forward, and one binding-swap test to formally prove the vendor seam is replaceable.

---

## What I'd Do Next

1. **Extract `DailyProcessingService`** — move holdings calculation out of the controller
2. **Fix the Docker volume mount** — finish what I started
3. **Unit price carry-forward as a tested invariant** — formal isolation, not just implicit coverage
4. **One binding-swap test** — prove `AdminApiInterface` is replaceable in a controller test, not just structurally
5. **Request-level idempotency** — `updateMember` and `setInvestmentProfile` create duplicate audit entries on repeat calls; an optional `operationId` in the request would fix this
6. **Negative balance guard** — withdrawals work mathematically but nothing stops units going below zero
7. **Pagination** — transaction history and audit event lists have no limit; fine for a harness, not fine at scale

---

## Known Limitations

- No authentication (per spec — `userId` is the trust boundary)
- `artisan serve` is single-threaded, not production-ready
- No CORS middleware (assumes Vite proxy for local frontend development)
- No pagination on list endpoints
- Audit events capture operation type and result but not full request payloads
- `moveDayForward` business logic lives in the controller, not a domain service
- `AdminApiInterface` replaceability is architectural, not explicitly proven in tests