# JOURNAL

## Understanding the Requirements

The assignment asks for a multi-service system connecting a forward-facing HTTP API to an internal Admin API boundary used to communicate with vendor software. I started by identifying the distinct pieces:

1. **Three HTTP surfaces** — Public API (member-facing CRUD + reads), Mock Control API (test harness to inject data and trigger processing), and Inspection API (audit trail queries). All on port 9001.
2. **An internal Admin API boundary** — a code-level interface (not HTTP) that represents how the platform communicates with vendor software. The mock implementation of this interface is the "vendor" for this exercise.
3. **ID mapping** — the admin system has its own internal identifier (`adminId`) that the public API never exposes. The platform translates between `userId`/`memberId` and `adminId`.
4. **Daily processing** — `moveDayForward` triggers deterministic end-of-day holdings calculation: net cash flow allocated by investment profile percentages, divided by unit prices to produce units and balances.
5. **Audit system** — every public mutation returns an `operationId` and emits sequenced events queryable through the inspection API.

The Mock Control API was initially confusing. It's not a test mock in the PHPUnit sense — it's the puppet strings for a simulated vendor. In production, transactions arrive and prices update externally. Here, nothing happens unless the harness injects it. The mock control endpoints (`addTransactions`, `setDailyUnitPrices`, `moveDayForward`) are how the harness drives data into the system and triggers processing.

## Language Selection

**Chose PHP/Laravel.**

I considered TypeScript (contract is already in TS, native JSON, trivial Docker) and Go (clean interfaces, fast). I chose Laravel because:

- My primary proficiency is in Laravel. In a 10-14 hour timebox, framework familiarity dominates all other factors.
- The service container gives interface binding for free.
- Built-in validation, HTTP testing, and migrations map directly to what the spec requires.

Tradeoffs accepted:
- Must manually translate the TypeScript contract into PHP.
- Laravel conventions use `snake_case` but the spec uses `camelCase` throughout. Every JSON response will need explicit key formatting.
- Docker is slightly heavier than a Node.js setup (need PHP extensions, Composer).

## Architecture Decision

**Chose a single-process Laravel application with three route groups and an interface boundary.**

The spec explicitly states: "a single service exposing three route groups is completely acceptable. You do not need to introduce extra runtime separation unless it helps your design."

Alternatives considered and rejected:

- **Microservices (3 processes)** — overkill. 12 endpoints don't justify service discovery, inter-service networking, or multiple containers. The spec says "simple architecture is preferred over elaborate architecture."
- **Two processes (public + admin)** — would physically separate the boundary, but adds HTTP overhead between services, more complex Docker networking, and more failure modes. The boundary is better enforced in code.
- **Modular monolith with domain modules** — considered separate module folders. Rejected because the organizational overhead doesn't pay off at this scale. The interface itself is the boundary; folder structure is cosmetic.

The architecture uses constructor injection (not Facades) for the Admin API boundary. This makes the dependency graph explicit — reviewers can see at a glance what depends on what and nothing touches domain data outside the boundary. The spec values "clear boundaries over framework sophistication."

## Database Ownership

Two logical owners, one PostgreSQL instance:

- **MockAdminApi** owns domain tables: members, investment profiles, transactions, holdings, unit prices, system state.
- **AuditService** owns platform tables: operations and events.
- **MemberService** owns the mapping table: the translation between public-facing IDs and admin-internal IDs.

The audit system is a platform concern, not a vendor concern. In the real world you wouldn't ask the vendor to track your audit trail — you'd log it yourself around calls you make to them.