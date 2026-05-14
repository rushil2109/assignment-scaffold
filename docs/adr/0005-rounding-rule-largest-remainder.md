# Rounding rule: floor each allocation, assign remainder to last asset

When splitting net daily cash flow across asset classes by profile percentages, each allocation is calculated as `floor(cashflow * percentage / 100, 2dp)`. The remainder (total cash flow minus sum of floored allocations) is added to the last asset class in the list.

This guarantees the invariant `sum(allocations) == net_cash_flow` always holds exactly, with no unallocated cents.
