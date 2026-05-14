# Simulated clock stored in a system_state table

The system date is stored as a `current_date` column in a single-row `system_state` table, starting at `2024-01-01` on bootstrap. `moveDayForward` increments this and processes each day sequentially.

Real system time cannot be used because the harness needs to advance multiple days within seconds during tests. The stored date serves as a processing watermark and enables the double-processing guard.

## Considered Options

- System time with timezone (doesn't support deterministic multi-day advancement in tests)
- Derive from MAX(holdings.effectiveDate) (breaks when no holdings exist yet, implicit rather than explicit)
