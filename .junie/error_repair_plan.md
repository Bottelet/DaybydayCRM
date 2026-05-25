# Error Repair Plan

Use this as the short debugging checklist. Refer to `.github/TESTING.md`, `.github/ARCHITECTURE.md`, and `AGENTS.md` for full context.

## Repair workflow
1. **Reproduce the failure in isolation** — run the specific failing test alone
2. **Identify the failure domain** — validation, authorization, service logic, storage integration, or test setup
3. **Fix the smallest domain-local cause** — make surgical changes, avoid scope creep
4. **Add or update isolated coverage** — ensure the fix has test coverage
5. **Re-run the failing test** — verify the fix works
6. **Run minimum required lint** — `git ls-files '*.php' | xargs -n1 php -l`

## PHPUnit shortcuts
```bash
make test                          # run all tests with stop-on-failure
make test-filter f=SomeTest        # run filtered subset
make test-fail                     # run until first failure
make paratest                      # run in parallel
```

## Playwright shortcuts
```bash
# Run all tests
npm run test:e2e                   # via npm
make e2e-test                      # via Make

# Run single spec
npm run test:e2e:file -- tests/e2e/auth/auth.spec.js  # via npm (path after -- required)
make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js  # via Make

# Stop on first failure
npm run test:e2e:stop-on-failure   # via npm
make e2e-fail                      # via Make

# List all tests
npm run test:e2e:list              # via npm
make e2e-list                      # via Make
```

## Frequent failure signatures
- **`SQLSTATE 1364`** — missing required factory/model data or missing `HasExternalId` trait
- **`SQLSTATE 1062`** — stale permission/role setup or duplicate seeded relationships
- **`Call to a member function ... on null`** — missing relationship or missing null guard
- **`Expected 302 got 200/403`** — JSON/web response mismatch (check `$request->expectsJson()`)
- **Wrong totals or statuses** — stored percentage values interpreted incorrectly (double-division)
- **Storage/file errors in tests** — missing deterministic test behavior or fallback handling

## Branch-specific attention areas
- **Refactored controller flows** — business logic now delegating into services/actions
- **FormRequest coverage** — validation moved to dedicated request classes for update/status/assignment endpoints
- **Entrust cache** — permission diagnostics and cache refresh after role/permission changes
- **Dropbox authentication** — null-handling behavior and fallback flows
