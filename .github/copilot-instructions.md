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
- Playwright e2e coverage in `tests/e2e` is organized as one plain-route spec per phenomenon.
- Run Playwright e2e checks with:
  - `npm run test:e2e`
  - `npm run test:e2e:file -- tests/e2e/auth/auth.spec.js`
  - `make e2e-test`
  - `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js`
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

## Current branch focus
The active refactor work on this repository centers on:
- service extraction from large controllers
- stronger validation and authorization boundaries
- better storage/authentication abstractions
- test-suite isolation and migration of controller coverage into Feature tests
- improved contributor and agent documentation
