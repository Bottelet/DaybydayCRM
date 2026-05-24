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

### Playwright e2e workflow
- `tests/e2e` now uses one plain-route Playwright spec per phenomenon, for example `tests/e2e/auth/auth.spec.js`.
- Use package scripts for local execution:
  - `npm run test:e2e`
  - `npm run test:e2e:list`
  - `npm run test:e2e:one -- tests/e2e/auth/auth.spec.js`
- Use Make targets when you want the same workflow through the repository task runner:
  - `make e2e-install`
  - `make e2e-test`
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js`
- The repository `Makefile` still keeps `make test` for PHPUnit, so use the explicit `e2e-*` targets for Playwright runs.

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
Guard optional relationships and optional date fields before accessing methods or properties.

### Cached roles and permissions in tests
After attaching permissions or roles, refresh the user and re-authenticate so Entrust checks use fresh data.

### JSON vs web status mismatches
A `json()` test request should not expect the same response path as a browser redirect flow.

### Storage behavior in tests
Storage services should be deterministic in testing and local fallback scenarios; avoid real external dependencies in isolated tests.

### Project closed-status casing
Project closed-state checks must use the project status helper logic rather than direct string equality because legacy data casing differs.

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
