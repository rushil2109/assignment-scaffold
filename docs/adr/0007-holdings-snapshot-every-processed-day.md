# Persist holdings snapshot for every processed day, even with no transactions

When `moveDayForward` processes a day, a holdings snapshot is written for every account regardless of whether transactions occurred that day. Units carry forward from the previous day, balance recalculates at the new day's unit prices.

If unit prices are not set for a given day/asset, the last known price for that asset is carried forward. This keeps the system deterministic and avoids failures when the harness doesn't explicitly set prices for every day.
