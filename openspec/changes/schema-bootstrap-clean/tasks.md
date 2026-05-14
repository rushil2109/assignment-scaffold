## 1. Database Migration

- [ ] 1.1 Create single migration file with all 9 tables (members, accounts, investment_profiles, transactions, unit_prices, holdings, audit_operations, audit_events, system_state)
- [ ] 1.2 Add UUID CHAR(36) primary keys for entity tables, BIGINT auto-increment for junction/event tables
- [ ] 1.3 Add foreign keys with CASCADE on member-dependent tables (accounts, investment_profiles, transactions, holdings)
- [ ] 1.4 Add unique constraints (members.user_id, members.admin_id, accounts.account_id, unit_prices[asset_code,date], holdings[account_id,asset_code,effective_date])
- [ ] 1.5 Add performance indexes (investment_profiles[account_id,is_current], transactions[account_id,effective_date], audit_operations[user_id], audit_events[operation_id])

## 2. Bootstrap Command

- [ ] 2.1 Create `app/Console/Commands/BootstrapCommand.php` registered as `app:bootstrap`
- [ ] 2.2 Run `migrate` programmatically (Artisan::call)
- [ ] 2.3 Insert system_state row (id=1, current_date=2024-01-01) using insertOrIgnore for idempotency

## 3. Clean Command

- [ ] 3.1 Create `app/Console/Commands/CleanCommand.php` registered as `app:clean`
- [ ] 3.2 Disable FK checks, truncate all 9 tables, re-enable FK checks
- [ ] 3.3 Re-insert system_state row (id=1, current_date=2024-01-01)

## 4. Verification

- [ ] 4.1 Run `make bootstrap` and confirm all tables exist with correct structure
- [ ] 4.2 Run `make clean` on populated database and confirm all data cleared, system_state reset
- [ ] 4.3 Run both commands twice consecutively to verify idempotency
