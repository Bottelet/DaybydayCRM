# Changelog

All notable changes documented for the current DaybydayCRM refactor branch are collected here.

This changelog is focused on the work currently represented by the active PR branch and the recent merge history behind it.

## [Unreleased] - 2026-05-20

### Highlights
- Refactored large controllers toward dedicated service classes and request objects.
- Reorganized large parts of the test suite around isolated Feature and Unit coverage.
- Hardened storage integrations, null handling, and Dropbox authentication behavior.
- Added operational tooling for permissions, upgrades, and cache diagnostics.
- Expanded project documentation for contributors and AI agents.

### Added
- New service-layer coverage across multiple domains, including Absence, Appointment, Comment, Department, Invoice, InvoiceLine, Lead, Offer, Payment, Project, Role, Task, and user update workflows.
- New operational Artisan commands:
  - `entrust:clear` for permission cache cleanup
  - `entrust:diagnose` for permission troubleshooting
  - `upgrade` for upgrade-related workflows
- New request validation for several controller flows, including integration storage, lead assignment/status/deadline changes, offer creation, settings updates, and user input handling.
- New storage and billing fallback infrastructure, including null adapters and registry-style service resolution.
- New seeders and world-building helpers to support cleaner local, demo, and test environments.
- New test coverage for:
  - controller authorization
  - controller validation and security
  - storage adapters and authentication
  - command behavior
  - controller performance-sensitive paths
  - refactored payment and role/service flows

### Changed
- Reworked large controllers to delegate more business logic into `app/Services/*` classes.
- Continued migration toward FormRequest-driven validation instead of inline controller validation.
- Standardized more response and middleware behavior around authorization and request handling.
- Restructured seeded/demo data organization, including separation of demo and dummy seeders.
- Moved or rebuilt many HTTP/controller tests under `tests/Feature/*` to align with repository testing rules.
- Improved model, view composer, and helper null-safety in areas touched by the refactor.
- Updated storage authentication abstractions and Dropbox integration flow.
- Improved role, permission, and Entrust cache handling to reduce stale-permission failures in tests and local development.

### Fixed
- Returned `404` when updating a role with an invalid external ID instead of allowing an inconsistent flow.
- Fixed failing CI-related gaps by adding missing services, migrations, and supporting test updates.
- Aligned Dropbox null handling so storage operations behave more predictably when optional values are absent.
- Updated Dropbox authentication URL assertions and related tests to match current behavior.
- Reduced brittle test behavior around authentication, storage, and controller-level expectations.

### Security
- Reinforced the ongoing move away from permissive controller logic by routing more behavior through explicit validation, middleware, and services.
- Improved permission diagnostics and cache clearing support for safer authorization troubleshooting.
- Preserved documented expectations around JSON-vs-web response handling and guarded update flows.

### Testing
- Introduced or expanded `AbstractTestCase`-based patterns in recently touched tests.
- Increased isolated Feature coverage for controllers across clients, documents, leads, offers, payments, projects, roles, settings, and tasks.
- Added focused storage authentication and Dropbox integration tests.
- Continued the broader effort to separate HTTP tests from pure unit tests.

### Developer Experience
- Clarified project-level agent guidance in `AGENTS.md`, `.github/copilot-instructions.md`, and `.junie/*.md`.
- Preserved Makefile-based workflows for host and Docker development.
- Kept PHP syntax linting as the minimum required commit-time validation.

### Notes for Upgraders
- Review new and refactored service classes before extending controller behavior.
- Prefer existing FormRequests and service classes when adding new endpoints.
- Re-run seeders or environment setup if local demo/test data falls behind the refactored seeder layout.
- If permission behavior appears stale, use the Entrust cache tooling before debugging deeper authorization issues.
