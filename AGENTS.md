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
