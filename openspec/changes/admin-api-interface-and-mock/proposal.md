## Why

The platform needs an abstraction boundary between the public-facing API and the "vendor" (admin) system. This interface allows the public API to call vendor operations without coupling to a specific implementation. A mock implementation backed by MySQL enables deterministic testing via the mock control API.

## What Changes

- Define `AdminApiInterface` with 6 methods: `createMember`, `updateMember`, `setInvestmentProfile`, `getInvestmentPortfolio`, `getTransactionHistory`, `getHoldings`
- All interface methods accept `adminId` (CHAR(36)) as their primary identifier
- Implement `MockAdminApi` as the concrete class backed by Eloquent/MySQL
- Bind the interface to `MockAdminApi` as a singleton in the service container
- `createMember` generates a UUID `adminId` and persists member + account
- `setInvestmentProfile` is append-only: marks previous as `is_current=false`, inserts new as `is_current=true`

## Capabilities

### New Capabilities
- `admin-api-interface`: PHP interface defining the 6-method vendor boundary contract
- `mock-admin-api`: Concrete MySQL-backed implementation of the admin API interface

### Modified Capabilities

(none)

## Impact

- Creates `app/Contracts/AdminApiInterface.php`
- Creates `app/Services/MockAdminApi.php`
- Modifies `app/Providers/AppServiceProvider.php` (or a dedicated provider) to bind the interface
- Depends on schema from issue 01 being in place (tables must exist)
