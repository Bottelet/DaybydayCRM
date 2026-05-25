# Fundamental Analysis

Use this file as a fast summary; the authoritative detail lives in `AGENTS.md` and `.github/ARCHITECTURE.md`.

## System identity
DaybydayCRM is a modular Laravel CRM handling sales, delivery, billing, documents, appointments, absences, permissions, and integrations.

## Core architectural direction
- **Thin controllers** — orchestrate requests, authorization, and responses only
- **Services and actions** — business logic lives in `app/Services/*` and `app/Actions/*`
- **FormRequests** — validation and normalization belong in dedicated request classes
- **Traits and observers** — repeated model behavior uses `Blameable`, `Statusable`, `HasExternalId`, and observers
- **Adapters/registries** — external integrations use adapter patterns and service registries
- **Response separation** — strong separation between web and JSON response flows via `$request->expectsJson()`

## Current branch themes
- Large-controller refactors into service/action classes
- Stronger validation boundaries via FormRequests
- Improved permission tooling and cache handling
- Storage hardening, especially Dropbox-related flows
- Broader Feature and Unit test coverage
- Route-driven Playwright e2e coverage organized one phenomenon per spec file
- Contributor/agent documentation cleanup and professionalization

## Testing approach
- **PHPUnit** — Feature and Unit tests with factory-driven, self-contained test cases
- **Playwright** — Route-level e2e specs with stop-on-failure support and automatic screenshot/video capture
- **Isolation** — tests create their own data, avoid shared state, and normalize dates before assertions

## Files to read next
- `README.md` — project overview and quick start
- `CHANGELOG.md` — current branch changes
- `AGENTS.md` — complete contributor and agent guide
- `.github/ARCHITECTURE.md` — architecture details and technical debt
- `.github/TESTING.md` — mandatory testing rules
