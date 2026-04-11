# DaybydayCRM — AI Agent & Developer Guide

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

---

## Overview

**DaybydayCRM** is a Laravel-based CRM platform designed to manage:

* Clients, Leads, Projects, Tasks
* Invoices, Offers, Payments
* Integrations, Documents, Notifications

The system follows a **modular architecture**, separating domain logic into clear functional areas.

---

# System Architecture

## Core Directory Structure

```text
app/
 ├── Actions/       # Single-purpose business operations
 ├── Http/          # Controllers, Middleware, Requests
 ├── Models/        # Eloquent models
 ├── Repositories/  # Data access abstraction & Integrations
 ├── Services/      # Complex business logic & Workflows
 ├── Traits/        # Reusable domain behavior
 ├── Observers/     # Automatic model side effects

resources/
 ├── views/         # Blade templates

routes/
 ├── web.php        # Web routes
 ├── api.php        # API routes

database/
 ├── factories/     # Model factories (modern & legacy)
 ├── migrations/    # Database schema
 ├── seeders/       # Initial/Test data
```

## Domain Organization

Each major domain is isolated into its own structure:
- **Clients, Leads, Projects, Tasks, Invoices, Offers, Integrations, Documents, Users**

Typical domain components include:
`Controllers`, `Models`, `Services`, `Actions`, `Repositories`, `Observers`, `Factories`

---

# Request Lifecycle

## HTTP Flow

`Request` → `Routes` → `Middleware` → `Controller` → `Service / Action` → `Repository / Model` → `Response (View or JSON)`

## Responsibilities by Layer

### Controllers
- Request validation & Authorization
- Delegating logic to services/actions
- Returning responses
- **Must remain thin**.

### Services
- Complex business logic & Domain rules
- Coordination of workflows (e.g., `InvoiceCalculator`, `InvoiceNumberService`)

### Actions
- Encapsulate **single-purpose business operations** (e.g., `StoreAbsenceAction`).
- Reusable, Testable, Decoupled from HTTP layer.
- Location: `app/Actions/{Domain}/{ActionName}Action.php`

### Repositories
- Data access abstraction & Complex queries
- External integrations & Multi-tenancy logic
- **Must not contain business logic**.

---

# Core Conventions

## Data Handling & Integrations
- **External IDs (UUID Routing):** Most entities use `external_id` (UUID) instead of auto-increment IDs for routing and APIs.
- **Integrations:** Managed via `integrations` table and repository interfaces (`app/Repositories/BillingIntegration/`, `FilesystemIntegration/`).
- **Notifications:** Uses Laravel's notification system (Database, Mail, Custom). Should remain event-driven and decoupled.

## Trait Standards
- **Blameable:** Automatically tracks `user_created_id` and `user_updated_id`.
- **Statusable:** Standardized status handling (`status()` relationship, `hasStatus()`, `setStatus()`).
- **HasExternalId:** Automatically generates UUID `external_id` and sets it as route key.
- **SearchableTrait / DeadlineTrait:** Search logic and deadline management.

## Model Observers
- Registered in `AppServiceProvider::boot()`.
- Handle **automatic side effects**: File deletion, Cascade deletes, Search indexing, Audit logging.
- Example: `DocumentObserver`, `TaskObserver`, `ClientObserver`.

---

# Testing Standards

All tests must follow strict isolation rules to ensure reliability and performance.

### Required Rules
- **Self-Contained:** Create own data, avoid dependency on other tests or seeders.
- **Normalization:** Never compare `Carbon` vs `String`. Always normalize (e.g., `$model->created_at->toISOString()`).
- **Single Purpose:** One clear behavior per test, typically one HTTP request.
- **Role Usage:** Use `owner` or `administrator` roles for elevated permission requirements.
- **Cache Handling:** Always call `$user = $user->fresh()` after attaching permissions before `actingAs($user)`.

---

# UI & API

- **Frontend:** Blade partials, Custom SASS, Vue 2 (Legacy), DataTables (`yajra/laravel-datatables-oracle`).
- **API:** RESTful routes in `routes/api.php` with `auth:api` middleware.

---

# Operational Guidelines

| Always Follow | Never Allow |
| :--- | :--- |
| Thin Controllers | Business logic in controllers |
| Service/Action-Based Logic | Direct external calls from controllers |
| Strict Test Isolation | Shared test dependencies |
| UUID-Based Routing | Untracked model ownership |
| Trait-Based Model Behavior | Hard-coded status logic |
