# resetSubjectState preserves audit, clean truncates everything

`resetSubjectState` deletes vendor-side data (member, account, profiles, transactions, holdings) but preserves audit operations and events. Audit is a platform concern and should survive user resets within a test run.

`clean` (the artisan command) truncates all tables including audit and resets system_state to 2024-01-01. It's a full wipe between independent harness runs.
