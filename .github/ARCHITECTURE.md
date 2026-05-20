# System Architecture & Technical Analysis

## System Overview
DaybydayCRM is a modular CRM built with Laravel, separating logic into domain-specific modules. It utilizes a layered architecture (Controllers → Services/Actions → Repositories → Models).

---

## Technical Debt & Analysis

### 1. Factories & Database Defaults
- **Problem:** Many models (Activity, Lead, Project, Task) depend on observers or manual `boot` method logic for critical fields like `external_id` (UUID). This logic is often bypassed during tests, causing "Missing Default Value" errors.
- **Goal:** Modernize all factories to Class-based models and centralize UUID generation in the `HasExternalId` trait.

### 2. Business Logic Leaks
- **Problem:** Fat controllers are common, handling activity logging, notification sending, and data transformation.
- **Solution:** Encapsulate business logic in Service classes or single-purpose Action classes (`app/Actions/`). Controllers should focus on request handling and response generation.

### 3. Authorization & Permissions
- **Problem:** The current authorization system (Entrust) is aging and complex. Some policies are missing or incorrectly referenced.
- **Goal:** Audit all Policies and transition to Laravel's native Gates and Policies for better consistency and performance.

### 4. Frontend Asset Pipeline
- **Problem:** Vue 2 is End-of-Life (EOL), and the asset pipeline relies on legacy `laravel-mix` (Webpack).
- **Goal:** Roadmap migration to Vue 3 and Vite for faster development and improved security.

---

## Core Infrastructure

### Trait-Based Behavior
- **Blameable:** Automatically tracks `user_created_id` and `user_updated_id`.
- **Statusable:** Standardized status handling with helper methods.
- **HasExternalId:** Automatically generates UUIDs for `external_id` and sets it as route key.

### Model Observer Pattern
Observers are used for automatic side effects:
- File deletion upon model removal.
- Cascade soft deletes.
- Automatic logging/auditing.
- Search index updates.
- Registered in `AppServiceProvider::boot()`.

### Repository Pattern
Used for:
- Data access abstraction.
- External system integration (e.g., Billing, Filesystem).
- Multi-tenant query scoping.

---

## API Architecture
- **API routes:** `routes/api.php`
- **Authentication:** `auth:api` middleware.
- **Response Format:** Standard JSON responses.
