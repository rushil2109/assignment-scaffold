# Admin API interface only exposes vendor-realistic methods

The `AdminApiInterface` defines only the methods a real vendor would provide (create, update, get portfolio, get transactions, get holdings). Mock control methods (`addTransactions`, `setDailyUnitPrices`, `moveDayForward`, `resetSubjectState`) live only on the concrete `MockAdminApi` class. The mock controller injects the concrete class directly, while public controllers inject the interface.

Mock control methods accept platform IDs (`userId`, `accountId`) directly and resolve internally — they are harness-only and don't need to go through the vendor abstraction.

This means swapping to a real vendor only requires a new implementation of the interface — mock control methods are test-harness concerns and don't pollute the vendor boundary.

## Considered Options

- All methods on the interface (simpler binding, but leaks test concerns into the vendor contract)
- Separate interface for mock control (over-engineered for one concrete implementation)
- Mock controller resolves IDs before calling mock (unnecessary indirection for harness methods)
