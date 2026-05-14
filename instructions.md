# Execution Plan: Worktree-per-Change Implementation

## Execution DAG

```
Batch A (sequential root):
  schema-bootstrap-clean

Batch B (after A):
  admin-api-interface-and-mock

Batch C (after B — PARALLEL):
  public-api-create-member-audit    ║    mock-control-transactions-prices

Batch D (after C — PARALLEL):
  public-api-update-and-profile     ║    mock-control-reset-subject
  inspection-api                    ║    mock-control-move-day-forward

Batch E (after D — PARALLEL):
  public-api-read-endpoints         ║    unit-tests-invariants

Batch F (after E — leaf):
  feature-tests-e2e
```

## Merge Conflict Notes

| Batch | Risk | Details |
|-------|------|---------|
| C | Low | Both touch `routes/api.php` — different prefix sections (`/public/` vs `/mock/`) |
| D | Minimal | Four separate controllers/route groups. `mock-control-*` both add to MockControlController but different methods |
| E | None | Test files vs controller/route files — zero overlap |

## Prerequisites

```bash
# Ensure main is clean and up to date
cd /Users/rushilpardasani/Desktop/betashares/rushil/assignment-scaffold
git status  # should be on main, working tree clean (stash or commit WIP first)
```

## Batch A — Schema & Bootstrap

```bash
# Single change, runs on main directly (everything else depends on it)
git checkout -b impl/schema-bootstrap-clean

# >>> Implement: openspec/changes/schema-bootstrap-clean <<<
# After implementation:
git add -A && git commit -m "feat: database schema, bootstrap and clean commands"
git checkout main && git merge impl/schema-bootstrap-clean --no-ff
```

## Batch B — Admin API Interface

```bash
git checkout -b impl/admin-api-interface-and-mock

# >>> Implement: openspec/changes/admin-api-interface-and-mock <<<
git add -A && git commit -m "feat: AdminApiInterface + MockAdminApi implementation"
git checkout main && git merge impl/admin-api-interface-and-mock --no-ff
```

## Batch C — Parallel (2 worktrees)

```bash
# Create worktrees from current main (which now includes A + B)
git worktree add ../wt-create-member impl/public-api-create-member-audit -b impl/public-api-create-member-audit
git worktree add ../wt-transactions-prices impl/mock-control-transactions-prices -b impl/mock-control-transactions-prices
```

```bash
# Terminal 1 — public-api-create-member-audit
cd ../wt-create-member
# >>> Implement: openspec/changes/public-api-create-member-audit <<<
git add -A && git commit -m "feat: POST /public/createMember + audit trail"
```

```bash
# Terminal 2 — mock-control-transactions-prices
cd ../wt-transactions-prices
# >>> Implement: openspec/changes/mock-control-transactions-prices <<<
git add -A && git commit -m "feat: POST /mock/addTransactions + setDailyUnitPrices"
```

```bash
# Merge both back to main
cd /Users/rushilpardasani/Desktop/betashares/rushil/assignment-scaffold
git merge impl/public-api-create-member-audit --no-ff
git merge impl/mock-control-transactions-prices --no-ff
# Resolve routes/api.php if needed (different sections, trivial merge)

# Cleanup worktrees
git worktree remove ../wt-create-member
git worktree remove ../wt-transactions-prices
```

## Batch D — Parallel (4 worktrees)

```bash
git worktree add ../wt-update-profile impl/public-api-update-and-profile -b impl/public-api-update-and-profile
git worktree add ../wt-reset-subject impl/mock-control-reset-subject -b impl/mock-control-reset-subject
git worktree add ../wt-inspection impl/inspection-api -b impl/inspection-api
git worktree add ../wt-move-day impl/mock-control-move-day-forward -b impl/mock-control-move-day-forward
```

```bash
# Terminal 1 — public-api-update-and-profile
cd ../wt-update-profile
# >>> Implement: openspec/changes/public-api-update-and-profile <<<
git add -A && git commit -m "feat: POST /public/updateMember + setInvestmentProfile"
```

```bash
# Terminal 2 — mock-control-reset-subject
cd ../wt-reset-subject
# >>> Implement: openspec/changes/mock-control-reset-subject <<<
git add -A && git commit -m "feat: POST /mock/resetSubjectState"
```

```bash
# Terminal 3 — inspection-api
cd ../wt-inspection
# >>> Implement: openspec/changes/inspection-api <<<
git add -A && git commit -m "feat: POST /inspection/getRequestAudit + listAuditEvents"
```

```bash
# Terminal 4 — mock-control-move-day-forward
cd ../wt-move-day
# >>> Implement: openspec/changes/mock-control-move-day-forward <<<
git add -A && git commit -m "feat: POST /mock/moveDayForward with holdings processing"
```

```bash
# Merge all four back to main
cd /Users/rushilpardasani/Desktop/betashares/rushil/assignment-scaffold
git merge impl/public-api-update-and-profile --no-ff
git merge impl/mock-control-reset-subject --no-ff
git merge impl/inspection-api --no-ff
git merge impl/mock-control-move-day-forward --no-ff

# Cleanup
git worktree remove ../wt-update-profile
git worktree remove ../wt-reset-subject
git worktree remove ../wt-inspection
git worktree remove ../wt-move-day
```

## Batch E — Parallel (2 worktrees)

```bash
git worktree add ../wt-read-endpoints impl/public-api-read-endpoints -b impl/public-api-read-endpoints
git worktree add ../wt-unit-tests impl/unit-tests-invariants -b impl/unit-tests-invariants
```

```bash
# Terminal 1 — public-api-read-endpoints
cd ../wt-read-endpoints
# >>> Implement: openspec/changes/public-api-read-endpoints <<<
git add -A && git commit -m "feat: POST /public/getHoldings + getTransactionHistory + getInvestmentPortfolio"
```

```bash
# Terminal 2 — unit-tests-invariants
cd ../wt-unit-tests
# >>> Implement: openspec/changes/unit-tests-invariants <<<
git add -A && git commit -m "test: unit tests for allocation, holdings, idempotency invariants"
```

```bash
# Merge both back
cd /Users/rushilpardasani/Desktop/betashares/rushil/assignment-scaffold
git merge impl/public-api-read-endpoints --no-ff
git merge impl/unit-tests-invariants --no-ff

# Cleanup
git worktree remove ../wt-read-endpoints
git worktree remove ../wt-unit-tests
```

## Batch F — Feature Tests (sequential leaf)

```bash
git checkout -b impl/feature-tests-e2e

# >>> Implement: openspec/changes/feature-tests-e2e <<<
git add -A && git commit -m "test: blackbox E2E feature tests for full lifecycle"
git checkout main && git merge impl/feature-tests-e2e --no-ff
```

## Verification After All Batches

```bash
docker compose up --build -d
make bootstrap
make test        # all unit + feature tests green
make pint        # lint clean
```

---

## Claude Code Agent Invocation (alternative)

Instead of manual worktrees, spawn agents with worktree isolation:

```
# Batch C — two agents in parallel
Agent(isolation: "worktree", prompt: "/openspec-apply-change public-api-create-member-audit")
Agent(isolation: "worktree", prompt: "/openspec-apply-change mock-control-transactions-prices")
```

Each agent gets its own worktree, implements the change, commits, and returns the branch name. You then merge sequentially on main.

## Branch Naming Convention

All implementation branches: `impl/<change-name>`
Worktree directories: `../wt-<short-name>` (sibling to main repo)
