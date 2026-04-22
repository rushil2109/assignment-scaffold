# Engineering Systems Exercise

## Overview

Build a small multi-service system that connects a forward-facing HTTP API to an internal Admin API boundary used to communicate with vendor software.

## System Premise

This exercise is set in the context of a superannuation platform.

In a superannuation system, members have personal details, investment allocations, transaction history, and daily-priced investment holdings. In practice, a platform like this often depends on a third-party administration or custody vendor to manage core records and operational workflows.

One of the key engineering challenges in this kind of system is designing the product so that the vendor-facing side is both:

- switchable, so the product is not tightly coupled to one provider
- testable, so platform behavior can be validated independently of a live vendor

This assignment is intended to simulate that problem. You will build:

- a forward-facing API that behaves like the product surface
- an internal Admin API boundary that represents how the server communicates with vendor software
- a mock control API that drives a specific mock implementation of that Admin API
- an inspection surface that makes the system observable and verifiable

The goal is not to model all of superannuation. The goal is to show that you can design clean seams between internal and external structures, make data flow through those seams predictably, and leave the system easy to test and operate.

## Exercise Focus

This exercise is intended to focus on how you think about and structure a system with:

- a forward-facing API
- an internal Admin API boundary to vendor software behind that API
- a separate mock control surface for a concrete implementation of that boundary
- auditability and sequencing
- deterministic processing over financial state
- automated harnesses that verify behavior across system boundaries

When making design choices for this exercise:

- simple architecture is preferred over elaborate architecture
- clear boundaries are preferred over framework sophistication
- deterministic behavior is preferred over realism
- testability and operability matter more than completeness
- a coherent design is better than a broad but fragile implementation

This exercise is intentionally about:

- tying independent interfaces together
- designing harness-friendly APIs
- reasoning about multiple cooperating servers in one system
- encoding invariants in tests
- making a system easy to operate from a clean checkout

The final output must be published as a Git repository that reviewers and harnesses can clone directly.

## Timebox

- Submission window: `48-72 hours`
- Expected effort: `10-14 hours`
- Do not spend more than `14 hours`

A partial but coherent solution is better than a broad but fragile one.

## Submission

Submit:

- repository URL
- exact submission commit SHA
- `README.md`
- `JOURNAL.md`
- Git commit log / commit history
- one command to start the system
- one command to run tests

The marking harness will clone the repository at the submitted commit and evaluate only what is present there.

The commit log is a deliverable and will be assessed. Reviewers will look at how you structure work, isolate changes, and communicate intent through commits.

## Operational Requirement

A reviewer and harness must be able to:

1. clone the repository
2. check out the submitted commit
3. run `docker compose up --build`
4. have the system ready for the harness on `http://localhost:9001`

In addition:

- the running system must expose the public, mock control, and inspection APIs on port `9001`
- the repository must provide documented database lifecycle commands named `bootstrap` and `clean`
- these commands may be implemented via `make`, `npm`, shell scripts, application subcommands, or similar, but they must be easy to invoke and clearly documented
- the harness should be able to use `bootstrap` to prepare the database and `clean` to reset it between runs
- if you run multiple internal processes, document clearly how `docker compose` brings them up while still exposing the required API surface on port `9001`

There must be no hidden setup or undocumented runtime assumptions.

## Sample Frontend

A sample frontend scaffold is provided in `assignment-scaffold/sample-frontend`.

This frontend is a separate Vite application intended to run alongside your API. It is not part of the required backend deliverable, but it is provided so you can interact with your system as you implement methods.

Expected local runtime shape:

- your API runs on `http://localhost:9001`
- the sample frontend runs separately, typically on `http://localhost:5173`
- the Vite dev server proxies `/public`, `/mock`, and `/inspection` to `http://localhost:9001`

Install and run the sample frontend with:

```bash
cd assignment-scaffold/sample-frontend
npm install
npm run dev
```

The sample frontend README contains more detail about the available screens and expected endpoint conventions.

## CORS For Local Frontend Development

If you run the sample frontend as a separate browser application and call your API directly from `http://localhost:5173`, configure the server to allow CORS from that origin.

Recommended local CORS behavior:

- allow origin `http://localhost:5173`
- allow methods `POST` and `OPTIONS`
- allow the `Content-Type` header
- allow any additional headers you choose to require
- respond correctly to browser preflight `OPTIONS` requests

If you use the provided Vite proxy during local development, CORS is not required because browser requests are proxied through the frontend dev server. Still, your setup should make it easy to enable direct browser access if needed, and your `README.md` should document the chosen approach.

## What To Build

Implement three HTTP API surfaces around the provided internal Admin API interface:

1. `Public API`
2. `Mock Control API`
3. `Inspection API`

The provided Admin API interface is not itself one of these HTTP surfaces. It is the internal boundary your server should use when communicating with the vendor-side implementation.

The `Mock Control API` in this brief is a bespoke test-only control surface for a specific mock implementation of that Admin API.

These may run as:

- one process with three route groups
- two processes
- or three separate processes

But they must still satisfy the one-command startup requirement, and the harness must still be able to reach the system through port `9001`.

For the avoidance of doubt: a single service exposing three route groups is completely acceptable. You do not need to introduce extra runtime separation unless it helps your design.

## API Style

This exercise uses RPC-style HTTP, not strict REST.

Each method must:

- accept a single JSON request object
- return a single JSON response object

Recommended endpoint convention:

- `POST /public/createMember`
- `POST /public/updateMember`
- `POST /public/setInvestmentProfile`
- `POST /public/getInvestmentPortfolio`
- `POST /public/getTransactionHistory`
- `POST /public/getHoldings`
- `POST /mock/addTransactions`
- `POST /mock/setDailyUnitPrices`
- `POST /mock/moveDayForward`
- `POST /mock/resetSubjectState`
- `POST /inspection/getRequestAudit`
- `POST /inspection/listAuditEvents`

You may add a small number of helper endpoints if needed, but do not expand the surface area without reason.

## Canonical Contract

The canonical contract for this exercise is represented as simple TypeScript interfaces with method-specific input and output objects.

For this exercise, the only supported investment asset codes are:

- `Cash`
- `Conservative`
- `Balanced`
- `Growth`
- `HighGrowth`

```ts
export type UserId = string;
export type MemberId = string;
export type AccountId = string;
export type OperationId = string;
export type IsoDate = string;
export type IsoDateTime = string;
export type AssetCode =
  | "Cash"
  | "Conservative"
  | "Balanced"
  | "Growth"
  | "HighGrowth";

export interface PublicApi {
  createMember(input: CreateMemberInput): Promise<CreateMemberOutput>;
  updateMember(input: UpdateMemberInput): Promise<UpdateMemberOutput>;
  setInvestmentProfile(
    input: SetInvestmentProfileInput
  ): Promise<SetInvestmentProfileOutput>;
  getInvestmentPortfolio(
    input: GetInvestmentPortfolioInput
  ): Promise<GetInvestmentPortfolioOutput>;
  getTransactionHistory(
    input: GetTransactionHistoryInput
  ): Promise<GetTransactionHistoryOutput>;
  getHoldings(input: GetHoldingsInput): Promise<GetHoldingsOutput>;
}

export interface MockControlApi {
  addTransactions(input: AddTransactionsInput): Promise<AddTransactionsOutput>;
  setDailyUnitPrices(
    input: SetDailyUnitPricesInput
  ): Promise<SetDailyUnitPricesOutput>;
  moveDayForward(input: MoveDayForwardInput): Promise<MoveDayForwardOutput>;
  resetSubjectState(
    input: ResetSubjectStateInput
  ): Promise<ResetSubjectStateOutput>;
}

export interface InspectionApi {
  getRequestAudit(input: GetRequestAuditInput): Promise<GetRequestAuditOutput>;
  listAuditEvents(input: ListAuditEventsInput): Promise<ListAuditEventsOutput>;
}

export interface Address {
  line1: string;
  line2?: string;
  suburb: string;
  state: string;
  postCode: string;
  country: string;
}

export interface InvestmentAllocation {
  assetCode: AssetCode;
  percentage: number;
}

export interface Transaction {
  transactionId: string;
  effectiveDate: IsoDate;
  type: string;
  amount: number;
}

export interface NewTransaction {
  effectiveDate: IsoDate;
  type: string;
  amount: number;
}

export interface DailyUnitPrice {
  assetCode: AssetCode;
  date: IsoDate;
  unitPrice: number;
}

export interface Holding {
  assetCode: AssetCode;
  units: number;
  unitPrice: number;
  balance: number;
  effectiveDate: IsoDate;
}

export interface SeedMember {
  firstName: string;
  lastName: string;
  email: string;
  mobile: string;
  dateOfBirth: IsoDate;
}

export interface AuditEvent {
  at: IsoDateTime;
  type: string;
  details: Record<string, unknown>;
}

export interface RequestAudit {
  userId: UserId;
  operationId: OperationId;
  operation: string;
  status: string;
  events: AuditEvent[];
}

export interface CreateMemberInput {
  userId: UserId;
  firstName: string;
  lastName: string;
  email: string;
  mobile: string;
  dateOfBirth: IsoDate;
  initialInvestmentProfile: InvestmentAllocation[];
}

export interface CreateMemberOutput {
  ok: boolean;
  memberId?: MemberId;
  accountId?: AccountId;
  operationId?: OperationId;
  error?: string;
}

export interface UpdateMemberInput {
  userId: UserId;
  memberId: MemberId;
  email?: string;
  mobile?: string;
  preferredName?: string;
  residentialAddress?: Address;
  postalAddress?: Address;
}

export interface UpdateMemberOutput {
  ok: boolean;
  operationId?: OperationId;
  error?: string;
}

export interface SetInvestmentProfileInput {
  userId: UserId;
  memberId: MemberId;
  accountId: AccountId;
  allocations: InvestmentAllocation[];
}

export interface SetInvestmentProfileOutput {
  ok: boolean;
  operationId?: OperationId;
  error?: string;
}

export interface GetInvestmentPortfolioInput {
  userId: UserId;
  memberId: MemberId;
  accountId: AccountId;
}

export interface GetInvestmentPortfolioOutput {
  ok: boolean;
  allocations?: InvestmentAllocation[];
  error?: string;
}

export interface GetTransactionHistoryInput {
  userId: UserId;
  memberId: MemberId;
  accountId: AccountId;
  fromDate?: IsoDate;
  toDate?: IsoDate;
}

export interface GetTransactionHistoryOutput {
  ok: boolean;
  transactions?: Transaction[];
  error?: string;
}

export interface GetHoldingsInput {
  userId: UserId;
  memberId: MemberId;
  accountId: AccountId;
  asOfDate?: IsoDate;
}

export interface GetHoldingsOutput {
  ok: boolean;
  holdings?: Holding[];
  error?: string;
}

export interface AddTransactionsInput {
  userId: UserId;
  accountId: AccountId;
  transactions: NewTransaction[];
}

export interface AddTransactionsOutput {
  ok: boolean;
  addedCount: number;
}

export interface SetDailyUnitPricesInput {
  date: IsoDate;
  prices: DailyUnitPrice[];
}

export interface SetDailyUnitPricesOutput {
  ok: boolean;
}

export interface MoveDayForwardInput {
  days?: number;
}

export interface MoveDayForwardOutput {
  ok: boolean;
  processedDates: IsoDate[];
}

export interface ResetSubjectStateInput {
  userId: UserId;
}

export interface ResetSubjectStateOutput {
  ok: boolean;
}

export interface GetRequestAuditInput {
  userId: UserId;
  operationId: OperationId;
}

export interface GetRequestAuditOutput {
  ok: boolean;
  audit?: RequestAudit;
  error?: string;
}

export interface ListAuditEventsInput {
  userId: UserId;
}

export interface ListAuditEventsOutput {
  ok: boolean;
  events?: AuditEvent[];
  error?: string;
}
```

## Admin API Boundary For This Exercise

For the purposes of this exercise, assume that the provided internal Admin API interface broadly mirrors the member and account operations exposed by the public API.

For this test, the Admin API should be thought of as supporting corresponding create, update, portfolio, transaction, and holdings interactions behind the public API.

The important difference is that the Admin API works with an additional admin-system internal identifier that is not exposed through the client-facing API.

Your design should account for that mapping:

- the admin-side implementation should have an internal identifier of its own
- the public API should not expose that identifier
- the system should translate cleanly between client-facing identifiers and the admin-side identifier as needed

You may name this field however you like, for example `adminId`, `adminRecordId`, or similar.

## Authentication Assumption

Authentication is out of scope.

You do not need to model real access tokens, session handling, or access control.

The harness will choose the target user explicitly by providing `userId` in request bodies. You should treat `userId` as the user-selection key for this exercise.

## Public API Requirements

The UX harness must be able to:

- create a member with required fields
- update member details
- change the member investment portfolio
- view the member investment portfolio
- view the member transaction history
- view the member holdings, including unit prices

### Create Member

Requirements:

- the system must not create duplicate members for the same logical `userId`
- retries must be deterministic
- on success, the created member and account must be readable
- account creation must emit an auditable sequence

### Update Member

Requirements:

- support basic detail updates such as email, mobile, preferred name, and addresses
- invalid updates must fail clearly
- successful updates must be visible through subsequent reads
- updates must emit an auditable sequence

### Set Investment Profile

Requirements:

- allocations must be validated
- percentage totals must satisfy your documented rule
- a successful profile update affects future daily allocation behavior
- past holdings snapshots must not be rewritten
- profile changes must emit an auditable sequence

### View Investment Portfolio

Requirements:

- the public API must expose the current active portfolio allocations for the account
- the returned portfolio must reflect the latest accepted profile

### View Transaction History

Requirements:

- the public API must expose transaction history seeded through the mock control API
- date filtering is optional but recommended
- ordering must be deterministic and documented

### View Holdings

Requirements:

- the public API must expose processed holdings
- holdings must include `assetCode`, `units`, `unitPrice`, `balance`, and `effectiveDate`
- the endpoint must reflect admin-side daily processing, not a synthetic mock-only response path

## Mock Control API Requirements

The mock control API exists to let the harness control a specific mock implementation of the Admin API.

This is a test-only internal interface. It does not need production auth or production hardening.

This is not the same thing as the provided Admin API interface. The provided Admin API is the boundary your server uses to communicate with vendor software. The mock control API is a bespoke control surface for driving a concrete mock implementation of that boundary.

The important thing is that the rest of your system is cleanly structured around the Admin API boundary and remains easy to test.

### Add Transactions

Requirements:

- allow the harness to add transaction data for a subject/account
- transaction data introduced through this interface must participate in the same system behavior observed through the public API

### Set Daily Unit Prices

Requirements:

- allow the harness to set unit prices by asset for a given day
- pricing must be deterministic and harness-friendly
- day `D` processing must use day `D` prices

### Move Day Forward

Requirements:

- advance the system date by one day by default
- optionally support `days > 1`
- trigger daily holdings processing for each processed day
- return the list of processed dates

### Reset Subject State

Requirements:

- reset a subject's seeded state so the harness can start fresh

## Daily Holdings Processing

The system must implement deterministic daily holdings processing initiated by `moveDayForward`.

When a day is advanced, the admin-side system must:

1. determine the active investment profile for each affected account
2. collect transactions effective that day
3. compute net daily cash flow
4. allocate that cash flow across investment options in proportion to the active investment profile
5. apply the unit prices for that day
6. update units held and balances
7. persist a holdings snapshot for that day

The resulting holdings must be visible through the public API.

You are not required to simulate real markets. Unit prices are supplied through the mock control API.

Deterministic fake pricing is preferred to realism.

## Inspection API Requirements

The marking harness must be able to:

- inspect audit log details
- inspect request sequencing
- verify or inspect invariant-related state through the audit surface

### Get Request Audit

Requirements:

- retrieve the full audit record for a request
- include terminal status and ordered events

### List Audit Events For Subject

Requirements:

- retrieve ordered audit events for a subject
- make it easy to inspect sequencing across multiple requests

## Auditability

The marking harness must be able to inspect:

- what request occurred
- for which subject
- in what order events happened
- what terminal outcome was reached

Your audit model should make sequencing easy to verify mechanically.

Mutating public API methods should return an `operationId` so the harness can query the inspection API for the corresponding audit record.

## Testing Requirements

This is not only a feature-implementation exercise. It is also an exercise in designing a system whose behavior can be verified with confidence.

Your repository must include tests that demonstrate correctness at more than one level of the system.

At minimum, your solution should show that:

- important domain behavior can be verified in isolation
- interface-level behavior can be verified reliably
- behavior across system boundaries can be verified end to end
- at least one downstream boundary can be replaced or simulated in a controlled way for testing

We are not assessing adherence to a specific testing taxonomy. We are assessing whether you can design a testing approach that makes the system understandable, mockable, and trustworthy.

## Important Architectural Rule

Data introduced through the mock control API must enter the system by exercising a concrete implementation of the Admin API boundary and must participate in the same system behavior observed through the public API.

The public API must not bypass normal system behavior by serving separate mock-only responses that are disconnected from the underlying processing model.

The goal is to preserve a meaningful boundary between the forward-facing system and the vendor-facing Admin API, while keeping that boundary testable and replaceable.

## Required Invariants

Your repository must include unit tests proving important invariants.

At minimum, test:

- member creation does not create duplicates
- member updates validate and persist correctly
- investment profile allocations are valid
- daily transaction allocation sums back to total daily net flow within a documented rounding rule
- `new_units = previous_units + allocated_cashflow / unit_price`
- `balance = units * unitPrice`
- changing the investment profile affects future days only
- `moveDayForward` is deterministic and guarded against double-processing the same day
- public holdings reflect processed admin-side state
- every public mutation emits an audit trail in the expected sequence

If you implement request-level idempotency, also test that explicitly.

## Scope Guidance

Keep the solution focused on the required behaviors and choose a level of sophistication that fits the timebox.

We care more about clarity of structure, reasoning, operability, and testability than about architectural ambition or feature volume.

Simple solutions are acceptable. More layered solutions are also acceptable. The important thing is that the design is coherent, that the boundaries are defensible, and that the behavior can be verified.

Do not spend time on:

- frontend UI
- production auth
- beneficiaries
- rollovers
- insurance
- realistic market simulation

This is a systems exercise, not a product-completeness exercise.

## Deliverables

Your repository must contain:

- running code
- startup command
- test command
- minimal config and environment documentation
- `README.md`
- `JOURNAL.md`
- a meaningful Git commit history

### README

Your `README.md` must explain:

- how to start the system
- what services or route groups are exposed
- how the harness should call the public API
- how the harness should call the mock control API
- how the harness should call the inspection API

### Journal

Your `JOURNAL.md` should briefly explain:

- your system shape
- key tradeoffs
- assumptions
- known limitations
- what you would do next with more time

## Marking Criteria

- `20%` Ease of operation: clone, run one command, system is ready
- `25%` Public API correctness and completeness
- `15%` Mock control API design and forward data flow fidelity
- `15%` Auditability and inspection surface
- `15%` Testing design, unit tests, and invariant coverage
- `10%` Overall system design across multiple cooperating services

Commit history will also be reviewed qualitatively as part of the assessment. Reviewers will consider whether commits are reasonably structured, understandable, and useful as an engineering record of the work.

## Assessment Goals

This exercise is designed to evaluate:

- how well you tie independent structures together
- how you approach design of test frameworks and harness-friendly systems
- how you approach multiple servers operating in the same system
- how you encode invariants in code and tests
- how you make a system easy to operate and reason about

## Notes

- Prefer clarity and determinism over breadth.
- Document your decisions.
- If you do not finish everything, leave the system in a coherent state and explain the remaining work.
