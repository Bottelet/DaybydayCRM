# Copilot instructions for DaybydayCRM

Start with `AGENTS.md`, then use the focused documents below as needed:
- `.github/ARCHITECTURE.md`
- `.github/TESTING.md`
- `.github/ROADMAP.md`
- `CHANGELOG.md`

## Working defaults
- Keep controllers thin.
- Prefer `app/Services/*` and `app/Actions/*` for business logic.
- Use FormRequests for request validation.
- Use enums/helpers instead of hard-coded status or permission strings when equivalents already exist.
- Preserve JSON-vs-web response differences with `$request->expectsJson()`.
- Keep changes small and domain-local.

## Testing defaults
- New HTTP/controller tests belong in `tests/Feature/*`.
- Tests must be self-contained and factory-driven.
- Normalize dates before assertions.
- Refresh users after changing roles or permissions.
- **Never use `withoutMiddleware()`** — set up proper permissions with `withPermissions()` instead.
- **Never hardcode email addresses** — use `'email' => 'user_' . uniqid() . '@test.com'`.
- **Assert content, not just status codes** — use exact flash message values: `assertSessionHas('flash_message', __('...'))`
- **Every controller needs at minimum**: index, show (valid), show (404), store (valid), store (invalid), update, delete, unauthorized tests.
- **Guard optional relationships** — null-check before calling any method on a possibly-null relation.

### Playwright rules
- **Call `dismissTourIfVisible(page)` after every `page.goto()`** before clicking anything. The Bootstrap tour blocks UI elements. Import from `tests/e2e/helpers/plain-e2e.js`.
- **Call `page.waitForLoadState('networkidle')` after login** — `loginAsAdmin` already does this.
- **After state-changing requests, verify persistence** via a follow-up data fetch, not just the HTTP status code.
- Playwright e2e coverage in `tests/e2e` is organized as one plain-route spec per phenomenon.
- Run Playwright e2e checks with:
  - `npm run test:e2e` — run all e2e tests
  - `npm run test:e2e:file -- tests/e2e/auth/auth.spec.js` — run a single spec (requires the spec path after `--`)
  - `npm run test:e2e:stop-on-failure` — run tests, stop on first failure
  - `make e2e-test` — run all e2e tests via Make
  - `make e2e-test STOP_ON_FAILURE=true` — run all tests, stop on first failure
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js` — run a single spec via Make
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true` — run single spec, stop on failure
  - `make e2e-fail` — run tests, stop on first failure via Make
- Minimum required lint before push/PR:
  ```bash
  git ls-files '*.php' | xargs -n1 php -l
  ```

## Repository patterns to respect
- `HasExternalId` for UUID routing
- `Blameable` and `Statusable` traits for repeated model behavior
- observers for side effects
- service/adapter abstractions for integrations and storage providers
- explicit null guards around optional relationships and date fields

## Known pitfalls
- Comparing relationship objects directly to strings
- Double-dividing stored percentage values
- Returning browser redirects to JSON requests
- Forgetting to refresh permission state in tests
- Directly using storage integrations in tests instead of deterministic behavior
- Comparing project status casing directly instead of using helper logic
- Calling `->toArray()` or any method on a possibly-null relationship (e.g. `$client->primaryContact`) without null-checking first — production crash
- Using `withoutMiddleware()` in feature tests — bypasses the authorization you're testing
- Hardcoded email addresses in tests — parallel CI collisions
- Inline `auth()->user()->can()` checks inside controller methods — use `__construct()` middleware instead
- `Model::all()->count()` — full table scan in memory; use `Model::count()`
- Bootstrap tour blocking Playwright UI tests — always call `dismissTourIfVisible(page)` after navigation

## Current branch focus
The active refactor work on this repository centers on:
- service extraction from large controllers
- stronger validation and authorization boundaries
- better storage/authentication abstractions
- test-suite isolation and migration of controller coverage into Feature tests
- improved contributor and agent documentation
