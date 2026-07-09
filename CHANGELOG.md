# Changelog

All notable changes to this project are documented in this file.

## [Unreleased]

### Highlights
- Root-caused and fixed Vue never mounting anywhere in the app: a broken Vite alias silently forced the runtime-only build (no template compiler) into every page, so every Vue-templated feature — the "New Offer"/invoice-line modals, the navbar search bar, dashboard/profile charts — rendered as an empty placeholder with no console error at all.
- Implemented real `edit`/`update` for tasks, leads, and projects, and `show`/`edit`/`update` for departments, closing a genuine gap: there was previously no way to edit a task/lead/project's title or description after creation.
- Refactored large controllers toward dedicated service classes and request objects, continuing the move away from fat controllers.
- Reorganized large parts of the test suite around isolated Feature and Unit coverage, and added a full Playwright end-to-end suite (132 tests) alongside an expanded PHPUnit suite (907 tests).
- Repaired `TestSeeder`, which crashed on every run (calling two nonexistent methods) and, once fixed, collided with `DatabaseSeeder` via a non-idempotent user seeder. It now layers additional data on top cleanly.
- Hardened storage integrations, null handling, and Dropbox authentication behavior.
- Fixed CI pipeline failures (database step ordering, a missing frontend build, an overwritten test-config file) that only surfaced once the underlying issues were corrected one layer at a time.

### Added
- `UpdateTaskRequest`, `UpdateLeadRequest`, `UpdateProjectRequest` FormRequests, plus matching edit views, backing the new edit/update routes.
- `departments.show`/`edit`/`update` views and routes; department names in the index table now link to a real show page.
- New service-layer coverage across multiple domains, including Absence, Appointment, Comment, Department, Invoice, InvoiceLine, Lead, Offer, Payment, Project, Role, Task, and user update workflows.
- New operational Artisan commands:
  - `entrust:clear` for permission cache cleanup
  - `entrust:diagnose` for permission troubleshooting
  - `upgrade` for upgrade-related workflows
- New request validation for several controller flows, including integration storage, lead assignment/status/deadline changes, offer creation, settings updates, and user input handling.
- New storage and billing fallback infrastructure, including null adapters and registry-style service resolution.
- New seeders and world-building helpers to support cleaner local, demo, and test environments.
- Playwright regression coverage for: the Vue-mount fix, deadline-update flows across leads/tasks/projects, the Products module (previously zero coverage), notifications click-through, and the new edit/update/show routes.
- A `createAppointment()` test helper, since `AppointmentsController` has no create endpoint anywhere in the app — the only place one is ever created is seeder factory logic.
- A focused unit test on `Currency`/`MoneyConverter` guarding USD's decimal/thousand-separator formatting.
- New test coverage for controller authorization, controller validation and security, storage adapters and authentication, command behavior, controller performance-sensitive paths, and refactored payment/role/service flows.

### Fixed
- Vue-templated modals silently rendering empty (root cause above), plus two contributing bugs once the compiler was actually available: a self-closing custom-element tag invalid in in-DOM templates, and an inline `<script>` that Vue's compiler silently drops from its own mount target.
- `Setting::cached()` returning a corrupted `__PHP_Incomplete_Class` after a cache write, caused by the model missing from the cache serialization allow-list.
- A systemic bug affecting 5 modules (tasks, leads, projects, departments, integrations): `Route::resource()` registers all 7 RESTful routes regardless of which methods the controller implements, so hitting an unimplemented one directly threw an uncaught 500 instead of a 404.
- `UsersTableSeeder` unconditionally deleting and recreating the admin user, breaking any seeder run that happens after other data already references that row.
- `Currency`'s USD entry had its decimal and thousand separators swapped (rendering "$99,00" instead of "$99.00").
- Products module: creating any product always 500'd (a required `archived` column with no default was never set), plus a separate PHP 8 type error on an empty price field.
- A wrong-permission bug gating the project deadline-edit UI, and a misleading, fully-editable "time" field on that same modal that was silently discarded (project deadlines are date-only by design).
- `NotificationsController@markRead` crashing on an already-read or invalid notification.
- Several null-safety crashes (`User::canChangeRole`/`canChangePasswordOn` with zero roles, invoice line-item/currency lookups with a missing product or settings row).
- A raw Eloquent model being echoed directly into a Blade view, rendering a raw JSON blob instead of a field value.
- A broken onboarding route name that only ever affected a brand-new install with no company configured yet.
- The dashboard "tour" popup blocking Playwright test runs.
- Returned `404` when updating a role with an invalid external ID instead of allowing an inconsistent flow.
- Aligned Dropbox null handling so storage operations behave more predictably when optional values are absent, and updated Dropbox authentication URL assertions to match current behavior.
- Reduced brittle test behavior around authentication, storage, and controller-level expectations.

### Changed
- All 8 `Route::resource()` calls expanded into explicit `Route::get/post/match/delete` declarations for readability; verified byte-for-byte identical routing behavior via `route:list` before and after.
- Removed an entire dead, never-wired TypeScript test framework (`tests/e2e/helpers/route-cases.ts`, `coverage-fixtures.ts`, `session-context.ts`, `tests/helpers/*.ts`) that duplicated functionality the live Playwright suite already had.
- Converted Vue-rendered toast notifications to plain Bootstrap alerts.
- Reworked large controllers to delegate more business logic into `app/Services/*` classes, continuing migration toward FormRequest-driven validation instead of inline controller validation.
- Standardized more response and middleware behavior around authorization and request handling.
- Restructured seeded/demo data organization, including separation of demo and dummy seeders.
- Moved or rebuilt many HTTP/controller tests under `tests/Feature/*` to align with repository testing rules.
- Improved model, view composer, and helper null-safety in areas touched by the refactor.
- Improved role, permission, and Entrust cache handling to reduce stale-permission failures in tests and local development.

### CI
- Fixed GitHub Actions step ordering in `phpunit.yml`/`playwright.yml`: `cache:clear` ran before the test database existed, failing with "Unknown database" once `CACHE_STORE` moved to a database-backed cache.
- Added a frontend build step to `phpunit.yml` (previously only in `playwright.yml`), since Feature tests rendering the shared layout now depend on a real Vite build existing.
- Stopped a `.env.ci` → `.env.testing` copy step from overwriting `.env.testing`'s committed content, which one test asserts on directly.

### Security
- Reinforced the ongoing move away from permissive controller logic by routing more behavior through explicit validation, middleware, and services.
- Improved permission diagnostics and cache clearing support for safer authorization troubleshooting.
- Preserved documented expectations around JSON-vs-web response handling and guarded update flows.

### Documentation
- Fixed dead README badges (a deprecated shields.io workflow-status endpoint and a defunct dependency-badge service), replaced with working ones.
- Clarified project-level agent guidance in `AGENTS.md`, `.github/copilot-instructions.md`, and `.junie/*.md`.

### Notes for Upgraders
- Review new and refactored service classes before extending controller behavior.
- Prefer existing FormRequests and service classes when adding new endpoints.
- Re-run seeders or environment setup if local demo/test data falls behind the refactored seeder layout.
- If permission behavior appears stale, use the Entrust cache tooling before debugging deeper authorization issues.
