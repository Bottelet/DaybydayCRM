# DaybydayCRM Agent Guide

This file is the primary working guide for contributors and coding agents operating in this repository.

## Documentation map
- `README.md` — project overview, setup, and contributor entry points
- `CHANGELOG.md` — current branch changelog summary
- `.github/ARCHITECTURE.md` — architecture, debt, and layering details
- `.github/TESTING.md` — mandatory testing and isolation rules
- `.github/ROADMAP.md` — ongoing modernization work
- `.github/copilot-instructions.md` — shorter Copilot-specific operating rules
- `.junie/*.md` — compressed working summaries for analysis, testing, repairs, and refactors

## Project snapshot
DaybydayCRM is a Laravel CRM covering clients, leads, projects, tasks, offers, invoices, payments, appointments, absences, documents, notifications, and search.

Core technical themes in the current branch:
- Laravel 12 on PHP 8.3
- Blade + Vue 2 + Vite frontend stack
- expanding service/action architecture
- FormRequest-driven validation
- permission-heavy business rules
- extensive feature and unit test coverage
- active refactoring of legacy controllers, seeders, and integrations

## Architecture rules

### Keep controllers thin
Controllers should orchestrate requests, authorization, and responses only.

Move business logic into:
- `app/Services/*` for workflows or multi-step orchestration
- `app/Actions/*` for focused, single-purpose business operations

### Prefer FormRequests
Do not add new inline controller validation when a FormRequest should own the input rules and normalization.

### Prefer enums and typed helpers
Use enums for fixed value sets such as statuses, roles, permissions, and other repeated domain values.

### Reuse existing extension points
Before adding new logic, check whether the behavior belongs in:
- a service
- an action
- an observer
- a trait
- a policy or middleware class
- an existing repository or adapter abstraction

## Model and domain conventions
- Use `HasExternalId` for UUID routing and route keys.
- Use `Blameable` for creator/updater tracking.
- Use `Statusable` for status relationships and helper behavior.
- Keep model side effects in observers instead of controllers where possible.
- Avoid hard-coded status strings when helper methods or enums already exist.

## Response rules
Mixed web/API endpoints must differentiate between JSON and browser requests.

Pattern:
```php
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}

session()->flash('flash_message', 'Success');
return redirect()->back();
```

## Testing rules (mandatory)
- Tests must be self-contained.
- Use factories to create the data you need.
- Avoid relying on shared seeded state unless a test explicitly targets seeded behavior.
- Prefer one HTTP request per test unless validating a real workflow.
- Normalize Carbon/date values before comparing them.
- Put controller HTTP tests in `tests/Feature/*`.
- Rebind/reload users after role or permission changes before asserting authorization.
- **Never use `withoutMiddleware()` in feature tests** — it bypasses the authorization layer you are supposed to be testing. Set up proper permissions with `withPermissions()` instead.
- **Never hardcode email addresses** in test fixtures. Always use `'email' => 'user_' . uniqid() . '@test.com'` to prevent collisions in parallel runs.
- **Always assert on content, not just status codes.** `assertSessionHas('flash_message')` is weak — assert the exact message: `assertSessionHas('flash_message', __('...'))`
- **Every controller must have minimum 7 PHPUnit tests**: index, show (valid id), show (invalid id → 404), create/store (valid), create/store (invalid), update (valid), delete + unauthorized access.
- **Guard all optional relationships before access.** If `primaryContact` can be null, null-check it before calling `->toArray()` or any method on it.

### Playwright e2e rules (mandatory)
- **Always call `dismissTourIfVisible(page)` after any browser navigation** before interacting with page elements. The Bootstrap tour uses a backdrop that blocks clicks on the real UI. Import it from `tests/e2e/helpers/plain-e2e.js`.
- **Always call `page.waitForLoadState('networkidle')` after login** to let JavaScript finish executing before interacting with the page.
- **Never assert only on HTTP status codes.** After a state-changing request (assign, update, delete), verify the change persisted via a follow-up data fetch.
- **Browser-driven tests (using `page.goto()` or form clicks) must dismiss the tour** before interacting with any element.
- **Add a tour-dismissal test** in `tests/e2e/auth/auth.spec.js` if the tour behavior changes.

### Playwright e2e workflow
- `tests/e2e` now uses one plain-route Playwright spec per phenomenon, for example `tests/e2e/auth/auth.spec.js`.
- Use package scripts for local execution:
  - `npm run test:e2e` — run all e2e tests
  - `npm run test:e2e:list` — list all discovered tests
  - `npm run test:e2e:file -- tests/e2e/auth/auth.spec.js` — run a single spec (file path after `--` is required)
  - `npm run test:e2e:stop-on-failure` — run tests, stop on first failure
- Use Make targets when you want the same workflow through the repository task runner:
  - `make e2e-install` — install Playwright and browser dependencies
  - `make e2e-test` — run all e2e tests
  - `make e2e-test STOP_ON_FAILURE=true` — run all tests, stop on first failure
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js` — run a single spec
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true` — run single spec, stop on failure
  - `make e2e-fail` — run tests, stop on first failure (alternative to STOP_ON_FAILURE=true)
  - `make e2e-list` — list all discovered tests
- The repository `Makefile` keeps `make test` for PHPUnit; use explicit `e2e-*` targets for Playwright runs.
- Playwright config includes automatic screenshot and video capture on failure.
- Test logs are written to `storage/logs/e2e-*.json` and `storage/logs/e2e-*.log`.

### Base classes
- `tests/AbstractTestCase.php` is the preferred base for new Feature/controller tests.
- `tests/TestCase.php` remains in use for some legacy or unit coverage.

### Minimum validation before push/PR
```bash
git ls-files '*.php' | xargs -n1 php -l
```

## Common failure patterns

### Relationship object vs string comparison
If a model uses a relationship-backed status, compare the related property, not the relation object.

### Null relationship access
Guard optional relationships and optional date fields before accessing methods or properties. Every `->method()` call on a potentially-null relationship must have a null-check. Example: `$contact = $client->primaryContact; $merged = $contact ? array_merge($contact->toArray(), $client->toArray()) : $client->toArray();`

### Cached roles and permissions in tests
After attaching permissions or roles, refresh the user and re-authenticate so Entrust checks use fresh data.

### JSON vs web status mismatches
A `json()` test request should not expect the same response path as a browser redirect flow.

### Storage behavior in tests
Storage services should be deterministic in testing and local fallback scenarios; avoid real external dependencies in isolated tests.

### Project closed-status casing
Project closed-state checks must use the project status helper logic rather than direct string equality because legacy data casing differs.

### Bootstrap tour blocking Playwright tests
The dashboard, client index, and client create pages show a Bootstrap tour for first-time visitors. Playwright runs always start with fresh cookies, so the tour will always show. Call `dismissTourIfVisible(page)` from `tests/e2e/helpers/plain-e2e.js` after any `page.goto()` before interacting with page elements.

### Controllers as utility hubs (SRP violation)
Controllers must NOT expose public methods that are simply proxies to service methods (e.g. `getInvoices()`, `findByExternalId()`) or act as repositories (e.g. `listAllClients()`, `getAllClientsCount()`). These belong in the service layer. Public-only-because-it-was-called-from-another-controller is not a valid reason.

### `Client::all()->count()` memory waste
Never use `Model::all()->count()`. Use `Model::count()` which issues a single `COUNT(*)` query.

### DRY in DataTable columns
Repeated date-formatting and status-badge closures in DataTable methods must be extracted to private helper methods on the controller. The pattern is `$this->formatDateColumn('column_name')`.

### Middleware vs inline permission checks
Authorization must use the middleware registered in `__construct()`, not inline `auth()->user()->can()` checks inside action methods. Add the method name to the `['only' => [...]]` array in the constructor.

## Current modernization themes
The current branch includes work in these areas:
- fat-controller extraction into service classes
- request validation expansion
- test suite reorganization and isolation improvements
- storage/authentication hardening, especially Dropbox flows
- seeder and demo-data cleanup
- role/permission tooling and cache diagnostics

## Practical workflow
1. Read the relevant docs first.
2. Reuse an existing service, request, enum, or helper when possible.
3. Keep the change local to the domain you are touching.
4. Update or add tests with strict isolation.
5. Run the minimum required validation.
6. Update docs when workflows, commands, or architecture guidance change.
