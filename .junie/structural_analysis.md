# Structural Analysis

This is the short structural map for quick orientation. See `.github/ARCHITECTURE.md` for deeper detail.

## High-level layout
- **`app/Http`** — controllers, middleware, requests (FormRequests), and view composers
- **`app/Services`** — workflow and orchestration logic (multi-step business operations)
- **`app/Actions`** — focused single-purpose operations (atomic business logic)
- **`app/Repositories`** — adapters, formatting, currency/tax helpers, and integration boundaries
- **`app/Models`** / **`app/Observers`** / **`app/Traits`** — domain state and shared behavior
- **`database/factories|seeders|migrations`** — test and environment data setup
- **`tests/Feature`** / **`tests/Unit`** — PHPUnit HTTP/controller and unit coverage
- **`tests/e2e/{phenomenon}/{phenomenon}.spec.js`** — route-driven Playwright specs, one file per phenomenon

## Key structural realities
- **Active controller-to-service transition** — business logic is being extracted from controllers into services/actions
- **Entrust-based permissions** — permission cache behavior matters; refresh users after role/permission changes in tests
- **Storage integrations** — adapter-style abstractions with safer fallback/null behavior
- **Evolving seed infrastructure** — demo, dummy, and test seeders have been reorganized; treat as evolving
- **Legacy conventions remain** — always check whether a helper, service, or request already exists before adding new code

## Common hotspots
- **Large legacy controllers** — candidates for service extraction
- **Permission-sensitive middleware and tests** — Entrust cache and authorization flows
- **Storage integrations and authentication** — Dropbox, local storage, and fallback behavior
- **Status handling and response branching** — JSON vs web response separation
- **Seeded data assumptions inside tests** — avoid shared state, use factories instead

## Testing structure
- **PHPUnit** — `tests/Feature/*` for HTTP/controller coverage, `tests/Unit/*` for isolated logic
- **Playwright** — `tests/e2e/*` for route-level e2e specs with automatic failure capture
- **Test helpers** — `tests/e2e/helpers/plain-e2e.js` for login, CSRF, and fixture creation
- **Stop-on-failure support** — both PHPUnit (`make test-fail`) and Playwright (`make e2e-fail`) support early exit
