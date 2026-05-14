# Holdings processing starts on first transaction day

`moveDayForward` only processes accounts that have either prior holdings or transactions effective that day. Accounts with no financial activity are skipped — `getHoldings` returns an empty array until the first transaction is processed.

Once an account has at least one holding, it is carried forward every subsequent processed day regardless of whether new transactions occur.
