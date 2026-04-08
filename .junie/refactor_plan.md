# Refactor Plan - DaybydayCRM Tests

This plan outlines structural refactoring steps to improve the testability and maintainability of the DaybydayCRM codebase.

## 1. Modernize Factories (High Priority)
- **Problem:** Current factories use the legacy Laravel 7 `factory()` helper and define closures in a single file.
- **Goal:** Convert to Class-based Factories (Laravel 8+ style).
- **Steps:**
    1.  Generate new factory classes: `php artisan make:factory ModelNameFactory --model=ModelName`.
    2.  Move attribute definitions from `database/factories/*.php` to the `definition()` method of the new classes.
    3.  Update tests to use `ModelName::factory()->create()`.
    4.  Add a `HasFactory` trait to models.

## 2. Centralize UUID Generation
- **Problem:** `external_id` generation is scattered across controllers, observers, and manual `boot` methods.
- **Goal:** Use a unified `HasUuid` trait for all models requiring `external_id`.
- **Steps:**
    1.  Create `app/Traits/HasUuid.php`.
    2.  Implement a `bootHasUuid` method that listens to the `creating` event.
    3.  Apply the trait to `User`, `Client`, `Lead`, `Project`, `Task`, `Activity`, etc.

## 3. Enhance TestCase for Authentication & Authorization
- **Problem:** Many tests manually assign roles or fail due to missing permissions.
- **Goal:** Provide helper methods in `TestCase` to quickly set up authorized users.
- **Steps:**
    1.  Add `protected function withRole(string $roleName): self` to `TestCase`.
    2.  Add `protected function withPermission(string $permissionName): self` to `TestCase`.
    3.  Update `setUp()` to optionally skip `db:seed` if only specific roles are needed (for speed).

## 4. Fix Routing Syntax
- **Problem:** Routes in `routes/web.php` use legacy string-based syntax (e.g., `'ClientsController@index'`).
- **Goal:** Use tuple-based syntax (e.g., `[ClientsController::class, 'index']`).
- **Steps:**
    1.  Import controller classes at the top of route files.
    2.  Update all route definitions.
    3.  This improves IDE navigation and refactoring safety.

## 5. Decouple Business Logic from Controllers
- **Problem:** Controllers contain heavy business logic (e.g., creating activities, sending notifications).
- **Goal:** Introduce Service Classes or Action Classes.
- **Steps:**
    1.  Identify complex controller methods (e.g., `TasksController@store`).
    2.  Extract logic to `app/Services/TaskService.php`.
    3.  Inject services into controllers.
    4.  Unit test services independently of HTTP requests.

## 6. Frontend Modernization
- **Problem:** Vue 2 is EOL; Webpack/Mix is slower than Vite.
- **Goal:** Prepare for Vue 3 and Vite.
- **Steps:**
    1.  Audit existing Vue components for Vue 3 compatibility.
    2.  Replace `laravel-mix` with `vite`.
    3.  Update `package.json` dependencies.
