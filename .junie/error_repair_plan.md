# Error Repair Plan

Use this as the short debugging checklist. Refer to `.github/TESTING.md`, `.github/ARCHITECTURE.md`, and `AGENTS.md` for full context.

## Repair workflow
1. Reproduce the failure in isolation.
2. Identify whether the issue is in validation, authorization, service logic, storage integration, or seeded/test setup.
3. Fix the smallest domain-local cause.
4. Add or update isolated coverage.
5. Re-run the failing test and the minimum required lint.

## Playwright shortcuts
- Run the whole e2e suite with `npm run test:e2e` or `make e2e-test`.
- Run one spec with `npm run test:e2e:file -- tests/e2e/auth/auth.spec.js` (spec path required after `--`) or `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js`.

## Frequent failure signatures
- `SQLSTATE 1364` — missing required factory/model data or missing `HasExternalId`
- `SQLSTATE 1062` — stale permission/role setup or duplicate seeded relationships
- `Call to a member function ... on null` — missing relationship or missing null guard
- `Expected 302 got 200/403` — JSON/web mismatch
- wrong totals or statuses — stored percentage values interpreted incorrectly
- storage/file errors in tests — missing deterministic test behavior or fallback handling

## Branch-specific attention areas
- refactored controller flows now delegating into services
- FormRequest coverage for update/status/assignment endpoints
- Entrust cache and permission diagnostics
- Dropbox authentication and null-handling behavior
