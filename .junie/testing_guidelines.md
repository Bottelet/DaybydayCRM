# Testing Guidelines

For the complete rules, use `.github/TESTING.md` and `AGENTS.md`.

## Mandatory rules
- Tests must be self-contained.
- Prefer factories over shared seed data.
- Keep controller/HTTP coverage in `tests/Feature/*`.
- Prefer one HTTP request per test unless validating a real workflow.
- Normalize Carbon/date values before assertions.
- Refresh users after changing roles or permissions.
- **Never use `withoutMiddleware()`** — grant correct permissions with `withPermissions()` instead.
- **Never hardcode email addresses** — use `'email' => 'user_' . uniqid() . '@test.com'`.
- **Assert exact flash messages** — `assertSessionHas('flash_message', __('...'))` not just `assertSessionHas('flash_message')`.
- **Minimum 7 tests per controller**: index, show/valid, show/404, store/valid, store/invalid, update, delete, unauthorized.
- **Guard optional relationships** — null-check before any `->method()` on a possibly-null relation.

## Playwright mandatory rules
- **Call `dismissTourIfVisible(page)` after every `page.goto()`** before interacting with UI elements — the Bootstrap tour backdrop blocks all clicks. Helper is in `tests/e2e/helpers/plain-e2e.js`.
- **`loginAsAdmin` already calls `waitForLoadState('networkidle')`** — do not remove it.
- **After state-changing requests, verify persistence** via a follow-up data fetch — never assert on HTTP status alone.
- **Browser-driven tests** (anything using `page.goto()` + element clicks) must call `dismissTourIfVisible` before touching any element.

## Practical commands

### PHPUnit
```bash
git ls-files '*.php' | xargs -n1 php -l
make test
make test-filter f=SomeTest
make test-fail
make paratest
```

### Playwright e2e
```bash
# Installation
make e2e-install

# Running tests
npm run test:e2e                                          # run all tests
npm run test:e2e:file -- tests/e2e/auth/auth.spec.js    # run single spec (requires path after --)
npm run test:e2e:stop-on-failure                         # stop on first failure
npm run test:e2e:list                                    # list all tests

# Via Make
make e2e-test                                            # run all tests
make e2e-test STOP_ON_FAILURE=true                       # run all tests, stop on first failure
make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js  # run single spec
make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true  # run single spec, stop on failure
make e2e-fail                                            # stop on first failure (alternative)
make e2e-list                                            # list all tests
```

## Playwright note
- `tests/e2e` uses one plain-route spec per phenomenon such as `tests/e2e/auth/auth.spec.js`.
- Keep `make test` for PHPUnit and use `e2e-*` Make targets for Playwright work.

## Watch for these failures
- permission cache stale after role/permission updates
- JSON requests asserting browser redirect responses
- status checks comparing relation objects or raw strings incorrectly
- null relationship or null date access — especially `$client->primaryContact->toArray()` when no contact exists
- nondeterministic storage behavior in tests
- seeded VAT/percentage data causing unexpected calculations
- Bootstrap tour blocking Playwright UI interactions — `dismissTourIfVisible(page)` required after navigation
- `withoutMiddleware()` bypassing authorization under test
- hardcoded emails causing duplicate-entry failures in parallel CI
- `Model::all()->count()` causing full table scans
- inline `auth()->user()->can()` bypassing middleware-level auth
