# Sample Frontend

This is a lightweight standalone Vite app for the assignment API.

## Purpose

The goal is to give the interviewee a simple way to interact with their system as they implement methods.

It is not intended to be a polished product UI. It is a functional scaffold that lets them:

- create a member
- inspect portfolio, transactions, and holdings
- exercise mock control actions
- inspect audit events and request audits

## Expected Runtime Shape

The recommended setup is:

- the candidate's API runs on `http://localhost:9001`
- this Vite app runs separately, typically on `http://localhost:5173`
- Vite proxies `/public`, `/mock`, and `/inspection` to `http://localhost:9001`

The UI also exposes an API base field if the proxy is not being used.

## Files

- `package.json`
- `vite.config.js`
- `index.html`
- `src/main.js`
- `src/style.css`

## Run

```bash
cd assignment-scaffold/sample-frontend
npm install
npm run dev
```

## Notes

- the UI stores known users, member IDs, account IDs, and operation IDs in local storage
- the audit screens rely on operation IDs returned from mutating methods
- the frontend assumes the endpoint naming convention from the assignment brief
- the Vite proxy avoids the need for CORS during local development
