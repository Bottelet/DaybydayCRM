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

5. **JSON vs Web Response Status Codes**
   - **Symptom:** Tests expecting 200/403 get 302 redirects (or vice versa)
   - **Cause:** Controllers not checking `$request->expectsJson()` before returning responses
   - **Pattern:** Middleware/controllers always redirect with 302 instead of aborting with 403 for JSON
   - **Fix:** Check `expectsJson()` and return appropriate status (200 for success, 403/400 for errors)
   - **Prevention:** Always differentiate JSON and web responses in authorization/validation logic
   - **Example:** Delete operations return 200 for JSON, 302 redirect for web

6. **Storage Services in Testing Environment**
   - **Symptom:** Document view/download tests fail with "File does not exist"
   - **Cause:** Storage services return null in testing environment
   - **Pattern:** `Local::view()` and `Local::download()` return null when file doesn't exist
   - **Fix:** Return fake content in testing/local environments: `if (config('app.env') === 'testing') return 'fake file content';`
   - **Prevention:** Storage integration services should provide test doubles for local/testing
   - **Affected:** DocumentsController tests for view/download operations

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
| FormRequest Validation | Direct `$request->input()` without validation |
| Type-Safe Enums | String constants for fixed value sets |

---

# Refactoring & Code Quality

## Code Quality Thresholds

### Controller Complexity
- **Maximum recommended:** 200 lines
- **Red flag:** 300+ lines
- **Action required:** 400+ lines

**Controllers exceeding threshold:**
- `ClientsController` (448 lines) → Extract to `ClientService`
- `TasksController` (418 lines) → Extract to `TaskService`
- `DocumentsController` (382 lines) → Extract to `DocumentStorageService`
- `ProjectsController` (369 lines) → Extract to `ProjectService`
- `UsersController` (362 lines) → Extract to `UserService`
- `LeadsController` (330 lines) → Extract to `LeadService`

### Service Extraction Triggers
Extract to service when controller contains:
1. Complex business logic (>50 lines in single method)
2. Multiple database operations in sequence
3. External API integration logic
4. File storage/manipulation logic
5. Complex calculations or transformations

### Enum Conversion Criteria
Convert constants to enums when:
1. Fixed set of values used across codebase
2. Type safety would prevent bugs
3. IDE autocomplete would improve DX
4. Values used in validation or comparison

**Current migration targets:**
- Task, Lead, Project status constants → Enums
- Role type constants → `RoleType` enum
- Complete `InvoiceStatus` enum migration

---

## Refactoring Roadmap

See **[.github/refactor.md](.github/refactor.md)** for complete details.

### High Priority (Security & Stability)
1. **Missing FormRequests** (15 controllers, 8 hours)
   - Prevent SQL injection and data integrity issues
   - Controllers: Leads, Tasks, Projects, Roles, Comments

2. **Response Handling Standardization** (10 controllers, 8 hours)
   - Fix JSON vs Web response inconsistencies
   - Affects API reliability and user experience

3. **Permission Middleware Consolidation** (15 files, 12 hours)
   - Centralize scattered authorization logic
   - Improve security consistency

4. **Service Extraction** (8 controllers, 40 hours)
   - Reduce controller complexity
   - Improve testability and maintainability

### Medium Priority (Code Quality)
1. **Enum Migration** (8 models, 6 hours)
   - Replace string constants with type-safe enums
   - Improve IDE support and prevent typos

2. **Status Model Refactoring** (25 files, 12 hours)
   - Add validation enums while keeping database flexibility
   - Type-safe status checks

3. **Test Organization** (39 files, 4 hours)
   - Move HTTP tests from `Unit/` to `Feature/`
   - Proper test categorization

4. **Permission Enum Completion** (25 files, 6 hours)
   - Add all permissions to `PermissionName` enum
   - Replace string literals

### Low Priority (Nice to Have)
1. Status validation standardization
2. Document policy extraction
3. Remove duplicate response headers
4. Test naming convention updates
5. PHPStorm region syntax updates

**Total Estimated Effort:** ~108 hours across 140+ files

---

## Migration Guides

### Controller → Service Migration

**Step 1: Identify extraction candidates**
```bash
# Find controllers > 200 lines
wc -l app/Http/Controllers/*.php | sort -rn | head -10
```

**Step 2: Create service**
```php
// app/Services/Task/TaskService.php
namespace App\Services\Task;

class TaskService
{
    public function updateStatus(Task $task, int $statusId): bool
    {
        // Validation
        if (!Status::isValidForType($statusId, Task::class)) {
            throw new InvalidArgumentException('Invalid status');
        }
        
        // Business logic
        $task->status_id = $statusId;
        return $task->save();
    }
}
```

**Step 3: Update controller**
```php
// Before
public function updateStatus(Request $request, $external_id)
{
    $task = Task::whereExternalId($external_id)->first();
    $validStatus = Status::typeOfTask()->where('id', $request->status_id)->exists();
    if (!$validStatus) {
        return redirect()->back();
    }
    $task->status_id = $request->status_id;
    $task->save();
}

// After
public function updateStatus(UpdateTaskStatusRequest $request, $external_id, TaskService $service)
{
    $task = Task::whereExternalId($external_id)->first();
    $service->updateStatus($task, $request->validated('status_id'));
    
    if ($request->expectsJson()) {
        return response()->json(['message' => 'Status updated']);
    }
    return redirect()->back();
}
```

### Constant → Enum Migration

**Step 1: Create enum**
```php
// app/Enums/TaskStatus.php
namespace App\Enums;

enum TaskStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case IN_PROGRESS = 'in_progress';
}
```

**Step 2: Replace usage**
```php
// Before
if ($task->status == Task::TASK_STATUS_CLOSED) { }

// After
use App\Enums\TaskStatus;
if ($task->status->title == TaskStatus::CLOSED->value) { }
```

**Step 3: Remove constant from model**

### FormRequest Creation

**Step 1: Generate request**
```bash
php artisan make:request Task/UpdateTaskStatusRequest
```

**Step 2: Add validation**
```php
namespace App\Http\Requests\Task;

class UpdateTaskStatusRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status_id' => 'required|integer|exists:statuses,id',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $status = Status::find($this->status_id);
            if ($status && $status->source_type !== Task::class) {
                $validator->errors()->add('status_id', 'Invalid status for task');
            }
        });
    }
}
```

**Step 3: Update controller**
```php
// Before
public function update(Request $request, $id)
{
    $task = Task::find($id);
    $task->update($request->all());
}

// After
public function update(UpdateTaskStatusRequest $request, $id)
{
    $task = Task::find($id);
    $task->update($request->validated());
}
```

---

# Testing Standards (Extended)

## Feature vs Unit Test Decision Matrix

| Characteristic | Feature Test | Unit Test |
|----------------|-------------|-----------|
| HTTP requests | Yes | No |
| Database | Yes | Mock/Stub |
| External services | Yes (faked) | Mock/Stub |
| Multiple classes | Yes | Ideally one |
| Execution time | Slower | Fast |
| Location | `tests/Feature/` | `tests/Unit/` |

## Current Test Organization Issues

**Problem:** 39 HTTP tests in `tests/Unit/Controllers/`

**These tests:**
- Make HTTP requests (`$this->get()`, `$this->post()`)
- Exercise full stack
- Are integration tests

**Solution:** Move to `tests/Feature/Controllers/`

**Migration:**
```bash
# Move files
mv tests/Unit/Controllers/Task tests/Feature/Controllers/Task

# Update namespace in files
sed -i 's/Tests\\Unit\\Controllers/Tests\\Feature\\Controllers/g' tests/Feature/Controllers/Task/*.php
```

---
