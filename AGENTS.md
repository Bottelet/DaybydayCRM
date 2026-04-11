# AGENTS.md — DaybydayCRM AI Agent Guide

## Recent Updates (2026-04-11)

### Critical Bug Patterns to Watch For

1. **Relationship Object vs String Comparison**
   - **Symptom:** Methods like `isClosed()` return unexpected results
   - **Cause:** Comparing Eloquent relationship objects directly to strings
   - **Example:** `$this->status == 'closed'` when `status` is a BelongsTo relationship
   - **Fix:** Access relationship property: `$this->status->title == 'closed'`
   - **Always check:** Lead, Task, Project, or any model with status_id foreign key

2. **Double Division in Percentage Calculations**
   - **Symptom:** Calculations off by factor of 100 (e.g., VAT totals)
   - **Cause:** Converting percentage to decimal twice
   - **Pattern:** `(value / 100) / 100` when should be just `(value / 100)`
   - **Check:** Tax calculations, discount calculations, commission calculations
   - **Example Found:** `Tax::integerToVatRate()` was dividing by 100 twice

3. **Null Relationship Access**
   - **Symptom:** "Call to member function on null" errors
   - **Cause:** Accessing relationship properties without null checks
   - **Prevention:** Always use `$this->relationship && $this->relationship->property`
   - **Example:** `isClosed()` methods now check `$this->status` exists first

4. **Cached Roles/Permissions in Tests**
   - **Symptom:** Permission checks fail in tests after attaching permissions
   - **Cause:** Accessing `$user->roles` loads relationship into memory before permission is attached
   - **Pattern:** `$user->roles->first()->attachPermission($perm)` then `actingAs($user)` fails permission check
   - **Fix:** Call `$user = $user->fresh()` after attaching permission to reload from database
   - **Prevention:** Always reload user after modifying roles/permissions before authentication
   - **Affected:** Tests using EntrustUserTrait's `can()` method

## Project Overview
- **DaybydayCRM** is a Laravel-based CRM for managing clients, tasks, projects, invoices, and more. The architecture is modular, with clear separation between domains (Clients, Projects, Tasks, Invoices, Integrations).
- **Key Directories:**
  - `app/Models/`: Eloquent models for all major entities.
  - `app/Http/Controllers/`: RESTful controllers, grouped by domain.
  - `app/Http/Middleware/`: Custom and standard middleware for permissions, demo mode, etc.
  - `app/Repositories/`: Abstractions for integrations (Billing, Filesystem).
  - `resources/views/`: Blade templates, organized by feature.
  - `routes/`: Route definitions (`web.php`, `api.php`).
  - `database/factories/`, `migrations/`, `seeders/`: Standard Laravel data setup.

## Architecture & Data Flow
- **Service Boundaries:**
  - Core business logic is in controllers and services, with repositories abstracting external integrations.
  - Integrations (billing, file storage) are pluggable via repository interfaces (`app/Repositories/BillingIntegration/`, `FilesystemIntegration/`).
  - Notifications use Laravel's notification system, often via database channel.
- **Data Flow:**
  - HTTP requests enter via `routes/`, pass through middleware, and are handled by controllers.
  - Controllers interact with Eloquent models and repositories, returning views or JSON.
  - Views use Blade and often include partials for headers, boards, etc.

## Developer Workflows
- **Build:**
  - Frontend: Use `npm run dev`, `npm run watch`, or `npm run prod` (see `package.json`).
  - Asset compilation: `webpack.mix.js` and legacy `gulpfile.js` (Elixir) for SASS/JS.
- **Test:**
  - PHP: `vendor/bin/phpunit` (config in `phpunit.xml`).
  - ParaTest: Use `vendor/bin/paratest` or `make paratest` for parallel test execution.
  - Browser: `php artisan dusk` for browser tests.
  - CI: See `.github/workflows/phpunit.yml` for GitHub Actions setup.
- **Makefile & Docker:**
  - Use the `Makefile` for standardized build, test, and Docker workflows. Key targets: `setup`, `phpunit`, `paratest`, `docker-setup`, `docker-phpunit`, etc. (see Makefile for full list).
- **Database:**
  - Migrations: `php artisan migrate`.
  - Factories: `database/factories/` for test/dummy data.
- **Debug:**
  - Use `barryvdh/laravel-debugbar` (dev only).
  - Logging: Configured in `config/logging.php`.

## Project-Specific Conventions
- **External IDs:**
  - Most entities use `external_id` (UUID) for routing and API, not auto-increment IDs.
- **Permissions:**
  - Role/permission checks via Entrust (`app/Zizaco/Entrust/`).
  - Middleware like `user.is.admin`, `is.demo` restrict access to sensitive routes.
- **Traits:**
  - **Blameable** (`app/Traits/Blameable.php`): Automatically tracks `user_created_id` and `user_updated_id`. Use on models that need creator/updater tracking.
  - **Statusable** (`app/Traits/Statusable.php`): Provides consistent status relationship and helper methods (`hasStatus()`, `setStatus()`, query scopes). Use on models with `status_id` field.
  - **HasExternalId** (`app/Traits/HasExternalId.php`): Automatically generates UUID for `external_id` and sets it as route key. Used by most models.
  - **SearchableTrait**: Provides search functionality for models.
  - **DeadlineTrait**: Provides deadline-related functionality.
- **Model Observers:**
  - Use Observers for automatic side effects (file deletion, cascade deletes, search indexing)
  - Registered in `AppServiceProvider::boot()`
  - Examples: `DocumentObserver`, `TaskObserver`, `ClientObserver`
- **Blameable Trait:**
  - Use `Blameable` trait to auto-set `user_created_id` on model creation
  - Add `creator()` relationship: `belongsTo(User::class, 'user_created_id')`
  - Provides automatic audit trail
- **Service Layer:**
  - Business logic belongs in Services, not Controllers
  - Controllers should be thin - validate input, call Service, return response
  - Examples: `InvoiceCalculator`, `InvoiceNumberService`, `ClientNumberService`
  - Tax calculation: Use `InvoiceCalculator` - `getSubTotal()` (no VAT), `getTotalPrice()` (with VAT)
- **Repository Pattern:**
  - When creating repositories, implement `findOrFail()`, `getAll()`, `create()`, `update()`, `delete()`
  - Repositories handle data access, not business logic
  - Use for complex queries and multi-tenancy scopes
- **Testing Conventions:**
  - All tests must follow the isolation and normalization rules in `.github/copilot-instructions.md`:
    - Each test creates its own data (no reliance on seeders or other tests)
    - Only one HTTP request per test (unless testing a workflow)
    - Normalize data types before assertions (e.g., dates)
    - Use `owner` or `administrator` roles in tests as needed
    - Never compare Carbon objects to strings directly—normalize first
  - See `.github/error_repair_plan.md` for common test failures and fixes.
- **Integrations:**
  - Billing and file storage integrations are managed via `integrations` table and repository interfaces.
  - Example: Dropbox and Google Drive for file storage, Dinero for billing.
- **UI Patterns:**
  - Project/task boards use custom Blade partials and SASS (`resources/assets/sass/components/project-board.scss`).
  - DataTables for tabular data (see `yajra/laravel-datatables-oracle`).

## Integration Points
- **APIs:**
  - RESTful API routes in `routes/api.php` (auth:api middleware).
- **External Services:**
  - AWS S3, Dropbox, Google Drive, Dinero, etc. (see `composer.json` dependencies).
- **Notifications:**
  - Use Laravel's notification system, often with custom notification classes.

## Examples
- **Adding a new integration:**
  - Implement the relevant repository interface, register in the service provider, and add config/migration as needed.
- **Custom middleware:**
  - Add to `app/Http/Middleware/`, register in `app/Http/Kernel.php`.
- **New UI component:**
  - Add Blade partial in `resources/views/partials/`, SASS in `resources/assets/sass/components/`.

---
For more, see the [README.md](./readme.md) and code comments in each module.

Below is the **professionalized version of `AGENTS.md`** rewritten into structured, consistent engineering documentation.
Content is preserved but normalized, deduplicated, and clarified. Derived from the original uploaded file. 

---

# `AGENTS.md`

# DaybydayCRM — AI Agent & Developer Guide

## Overview

**DaybydayCRM** is a Laravel-based CRM platform designed to manage:

* Clients
* Leads
* Projects
* Tasks
* Invoices
* Offers
* Integrations
* Documents
* Notifications

The system follows a **modular architecture**, separating domain logic into clear functional areas.

---

# System Architecture

## Core Directory Structure

```text
app/
 ├── Actions/
 ├── Http/
 │   ├── Controllers/
 │   ├── Middleware/
 ├── Models/
 ├── Repositories/
 ├── Services/
 ├── Traits/
 ├── Observers/

resources/
 ├── views/

routes/
 ├── web.php
 ├── api.php

database/
 ├── factories/
 ├── migrations/
 ├── seeders/
```

---

## Domain Organization

Each major domain is isolated into its own structure:

* Clients
* Leads
* Projects
* Tasks
* Invoices
* Offers
* Integrations
* Documents
* Users

Typical domain components include:

```text
Controllers
Models
Services
Actions
Repositories
Observers
Factories
```

---

# Request Lifecycle

## HTTP Flow

```text
Request
   ↓
Routes
   ↓
Middleware
   ↓
Controller
   ↓
Service / Action
   ↓
Repository / Model
   ↓
Response (View or JSON)
```

---

## Responsibilities by Layer

### Controllers

Responsible for:

* Request validation
* Authorization
* Delegating logic to services/actions
* Returning responses

Controllers **must remain thin**.

---

### Services

Responsible for:

* Business logic
* Domain rules
* Coordination of workflows

Examples:

```text
InvoiceCalculator
InvoiceNumberService
ClientNumberService
```

---

### Actions

Encapsulate **single-purpose business operations**.

Location:

```text
app/Actions/{Domain}/{ActionName}Action.php
```

Example:

```text
StoreAbsenceAction
```

Benefits:

* Reusable
* Testable
* Decoupled from HTTP layer

---

### Repositories

Responsible for:

* Data access abstraction
* Complex queries
* External integrations
* Multi-tenancy logic

Standard repository methods:

```php
findOrFail()
getAll()
create()
update()
delete()
```

Repositories **must not contain business logic**.

---

# Data Flow

## Model Interaction

Controllers interact with:

* Eloquent Models
* Repository interfaces
* Service classes

Output:

* Blade views
  or
* JSON responses

---

## Notifications

Uses Laravel Notification System:

```text
Channels:
- Database
- Mail
- Custom channels
```

Notifications should remain:

* Event-driven
* Decoupled from controllers

---

# Integration Architecture

## Supported External Integrations

Typical integrations include:

* AWS S3
* Dropbox
* Google Drive
* Dinero (Billing)

Integration points:

```text
app/Repositories/BillingIntegration/
app/Repositories/FilesystemIntegration/
```

All integrations must:

* Implement defined repository interfaces
* Be registered via Service Providers

---

# Developer Workflows

## Build Process

Frontend:

```bash
npm run dev
npm run watch
npm run prod
```

Asset compilation:

```text
webpack.mix.js
gulpfile.js (legacy)
```

---

## Testing

### PHPUnit

```bash
vendor/bin/phpunit
```

Configuration:

```text
phpunit.xml
```

---

### Parallel Testing

```bash
vendor/bin/paratest
make paratest
```

---

### Browser Testing

```bash
php artisan dusk
```

---

### Continuous Integration

GitHub workflow:

```text
.github/workflows/phpunit.yml
```

---

## Docker Usage

Use Makefile targets:

```bash
make setup
make phpunit
make paratest
make docker-setup
make docker-phpunit
```

---

## Database Operations

Run migrations:

```bash
php artisan migrate
```

Factories location:

```text
database/factories/
```

---

## Debugging

Development tools:

```text
barryvdh/laravel-debugbar
```

Logging configuration:

```text
config/logging.php
```

---

# Core Conventions

## External IDs (UUID Routing)

Most entities use:

```text
external_id (UUID)
```

Instead of:

```text
auto-increment id
```

Used for:

* Routing
* APIs
* External references

---

## Permission System

Uses:

```text
Entrust
app/Zizaco/Entrust/
```

Middleware examples:

```text
user.is.admin
is.demo
```

Used to restrict:

* Administrative access
* Sensitive operations

---

# Trait Standards

Traits provide reusable domain behavior.

---

## Blameable Trait

Location:

```text
app/Traits/Blameable.php
```

Purpose:

Automatically track:

```text
user_created_id
user_updated_id
```

Usage:

```php
use Blameable;
```

Required relationship:

```php
creator()
updater()
```

Benefits:

* Automatic audit trail
* Reduced duplication
* Consistent creator tracking

---

## Statusable Trait

Location:

```text
app/Traits/Statusable.php
```

Purpose:

Provide standardized status handling.

Features:

```text
status() relationship
hasStatus()
setStatus()
withStatus()
withoutStatus()
```

Used when model contains:

```text
status_id
```

---

## HasExternalId Trait

Location:

```text
app/Traits/HasExternalId.php
```

Purpose:

Automatically:

```text
Generate UUID external_id
Use UUID as route key
```

Used by:

```text
Most core models
```

---

## Additional Traits

Also available:

```text
SearchableTrait
DeadlineTrait
```

Used for:

```text
Search logic
Deadline management
```

---

# Model Observers

Observers handle **automatic side effects**.

Registered in:

```php
AppServiceProvider::boot()
```

---

## Typical Observer Responsibilities

Examples:

```text
File deletion
Cascade deletes
Search indexing
Audit logging
```

Example observers:

```text
DocumentObserver
TaskObserver
ClientObserver
```

---

# Service Layer Guidelines

All business logic must exist in:

```text
Services or Actions
```

Never inside:

```text
Controllers
```

---

## Invoice Calculation Standards

Use:

```text
InvoiceCalculator
```

Methods:

```php
getSubTotal()
getTotalPrice()
getVatTotal()
```

Ensures:

* Centralized tax logic
* Consistent calculations

---

# Repository Guidelines

Repositories must:

* Handle data persistence
* Abstract database queries
* Provide consistent interfaces

Required methods:

```php
findOrFail()
getAll()
create()
update()
delete()
```

Used when:

* Queries are complex
* Multi-tenant scopes exist
* External systems are involved

---

# Testing Standards

All tests must follow strict isolation rules.

---

## Required Rules

Tests must:

* Create their own data
* Avoid dependency on other tests
* Use normalized data comparisons
* Perform single HTTP request per test

Unless:

```text
Workflow testing
```

---

## Required Role Usage

Use roles:

```text
owner
administrator
```

When elevated permissions are required.

---

## Date Handling Rule

Never compare:

```php
Carbon vs String
```

Always normalize:

```php
$model->created_at->toISOString()
```

---

## Reference Materials

Important files:

```text
.github/error_repair_plan.md
.github/refactor_plan.md
.github/structural_analysis.md
.github/fundamental_analysis.md
.github/test_isolation_refactor.md
```

These define:

* Known issues
* Migration strategies
* Refactoring roadmap

---

# UI & Frontend Patterns

Project/task boards use:

```text
Blade partials
Custom SASS
```

Example:

```text
resources/assets/sass/components/project-board.scss
```

---

## Tables

Uses:

```text
yajra/laravel-datatables-oracle
```

For:

```text
Server-side tabular rendering
```

---

# API Structure

API routes:

```text
routes/api.php
```

Authentication:

```text
auth:api middleware
```

---

# Integration Workflow Examples

## Adding New Integration

Steps:

1. Implement repository interface
2. Register binding in service provider
3. Add configuration
4. Create migration if needed
5. Update integration registry

---

## Creating Middleware

Steps:

1. Create middleware class
2. Register in Kernel
3. Apply to route group

---

# Operational Guidelines

## Always Follow

```text
Thin Controllers
Service-Based Logic
Reusable Actions
Centralized Calculations
Strict Test Isolation
Trait-Based Model Behavior
Observer-Based Side Effects
```

---

## Never Allow

```text
Business logic in controllers
Direct external service calls from controllers
Shared test dependencies
Untracked model ownership
Hard-coded status logic
```
