# Structural Analysis

This is the short structural map for quick orientation. See `.github/ARCHITECTURE.md` for deeper detail.

## High-level layout
- `app/Http` — controllers, middleware, requests, and view composers
- `app/Services` — workflow and orchestration logic
- `app/Actions` — focused single-purpose operations
- `app/Repositories` — adapters, formatting, currency/tax helpers, and integration boundaries
- `app/Models` / `app/Observers` / `app/Traits` — domain state and shared behavior
- `database/factories|seeders|migrations` — test and environment data setup
- `tests/Feature` / `tests/Unit` / `tests/e2e` — layered test coverage
- `tests/e2e/{phenomenon}/{phenomenon}.spec.js` — route-driven Playwright specs, one file per phenomenon

## Key structural realities
- The codebase is in an active controller-to-service transition.
- Entrust-based permissions still exist, so permission cache behavior matters.
- Storage integrations use adapter-style abstractions and now include safer fallback/null behavior.
- Demo, dummy, and test seeders have been reorganized and should be treated as evolving infrastructure.
- Some legacy conventions remain, so always check whether a helper, service, or request already exists before adding new code.

## Common hotspots
- large legacy controllers
- permission-sensitive middleware and tests
- storage integrations and authentication
- status handling and response branching
- seeded data assumptions inside tests
