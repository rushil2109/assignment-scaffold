# Assignment Scaffold

This directory contains the candidate-facing materials for the engineering systems exercise.

## Contents

- [ASSIGNMENT.md](./ASSIGNMENT.md): the full assignment brief and HTTP contract
- [sample-frontend/](./sample-frontend/): a minimal standalone Vite UX used to interact with the API as it is implemented

## Sample Frontend

The sample frontend is a separate Vite application intended to run alongside the candidate's API on `http://localhost:9001`.

It provides:

- member creation
- a member workspace showing portfolio, transactions, and holdings
- simple update-member and set-investment-profile controls
- mock control actions for adding transactions, setting prices, moving the system day forward, and resetting user state
- an admin workspace for viewing audit events and request audits for known users

The frontend expects the API described in [ASSIGNMENT.md](./ASSIGNMENT.md).
