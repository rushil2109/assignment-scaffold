# Tests run against real MySQL, not SQLite in-memory

Tests use the same MySQL database engine as production via Laravel's `RefreshDatabase` trait (transaction rollback per test). SQLite was rejected because it diverges from MySQL in JSON handling, FK enforcement, and DECIMAL precision — all of which matter for this system's allocation math and holdings calculations.

## Considered Options

- SQLite in-memory (faster, but hides real bugs in JSON columns, decimal precision, and FK cascades)
- Separate MySQL test database with RefreshDatabase (same engine, fast rollbacks, no divergence)
