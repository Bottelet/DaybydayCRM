# Refactoring Suggestions

After fixing the test failures, several patterns emerged that could benefit from refactoring to improve code quality, consistency, and maintainability.

## 0. Lessons Learned from Recent Test Fixes (2026-04-11)

This section documents critical patterns discovered while fixing 30+ test failures. These should inform future development and refactoring efforts.

### VAT/Percentage Storage Convention
**Discovery:** VAT is stored as `percentage × 100` (e.g., 2100 for 21%) but was being divided by 100 instead of 10000.

**Impact:** All invoice calculations were off by factor of 100.

**Pattern to Follow:**
```php
// CORRECT: For percentage stored as integer (2100 = 21%)
$decimalRate = $storedValue / 10000; // 2100 / 10000 = 0.21

// WRONG: Double division
$decimalRate = ($storedValue / 100) / 100; // Results in 0.21 instead of 0.0021
```

**Action Item:** Document this convention clearly in:
- Model docblocks for fields storing percentages
- Service classes handling percentage calculations
- Migration files that create percentage columns

### JSON vs Web Response Handling Must Be Explicit
**Discovery:** Many controller methods don't check `$request->expectsJson()`, causing wrong HTTP status codes in tests.

**Impact:** 
- JSON API tests fail expecting 200/403 but get 302 redirects
- Web tests might fail expecting redirects but get JSON responses
- Inconsistent user experience between web and API

**Pattern to Follow:**
```php
// In controllers with mixed web/JSON usage:
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}

session()->flash('flash_message', 'Success');
return redirect()->back();
```

**Critical Controllers:**
- TasksController (delete, update status)
- ProjectsController (delete, update assignment, update status)
- LeadsController (delete, update deadline)
- DocumentsController (view, download)
- SettingsController (admin-only actions via middleware)

### Status source_type Must Use Full Class Names
**Discovery:** Some tests/code use string literals (`'task'`) but scopes expect full class names (`Task::class`).

**Impact:** Status validation fails because `Status::typeOfTask()` scope filters by `Task::class` not `'task'`.

**Pattern to Follow:**
```php
// CORRECT:
Status::factory()->create(['source_type' => Task::class]);
Status::where('source_type', Task::class)->get();

// WRONG:
Status::factory()->create(['source_type' => 'task']);
Status::where('source_type', 'task')->get();
```

**Action Item:** Create a migration to update existing `source_type` values from strings to class names.

### Trait Methods Must Handle Null Values
**Discovery:** DeadlineTrait's `isOverDeadline()` didn't check if deadline was null before comparing with Carbon::now().

**Impact:** Tests fail with unexpected true/false results when models don't have deadlines set.

**Pattern to Follow:**
```php
// In trait methods that access optional model properties:
public function isOverDeadline(): bool
{
    if ($this->isClosed()) {
        return false;
    }
    
    // MUST check for null before comparison
    if (!$this->deadline) {
        return false;
    }
    
    return $this->deadline < Carbon::now();
}
```

**Action Item:** Audit all trait methods for similar null-safety issues.

### Storage Services Need Test Doubles
**Discovery:** Local storage's `view()` and `download()` return null in testing, breaking document tests.

**Impact:** All document authorization tests fail with "File does not exist" errors.

**Pattern to Follow:**
```php
// In storage service methods:
public function view($file)
{
    if (config('app.env') === 'testing' || config('app.env') === 'local') {
        return 'fake file content'; // Test double
    }
    
    // Real implementation
    return Storage::disk('local')->get($file);
}
```

**Action Item:** Review all integration services (billing, filesystem, etc.) for proper test doubles.

### Test Data Setup for Calculations
**Discovery:** Invoice/tax calculation tests inherit random VAT from database seeder, causing non-deterministic failures.

**Impact:** Tests fail intermittently based on seeded data.

**Pattern to Follow:**
```php
// In test setUp() when testing calculations:
protected function setUp(): void
{
    parent::setUp();
    
    // Explicitly create Setting with known values
    Setting::factory()->create(['vat' => 0]);
    
    // Now calculations are deterministic
    $this->invoice = Invoice::factory()->create();
}
```

**Action Item:** Add to testing guidelines - always create Setting records in calculation tests.

---

## 1. Standardize JSON vs Web Response Handling

### Current Problem
Controllers have inconsistent handling of JSON vs web requests for authorization failures and validation errors:
- Some use `abort(403)` unconditionally
- Some check `expectsJson()` and return different responses
- Some use middleware that always aborts
- Response codes are inconsistent (302, 403, 400)

### Recommendation
Create a trait or base controller method for consistent response handling:

```php
trait RespondsWithHttpStatus
{
    protected function respondUnauthorized(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }
        
        session()->flash('flash_message_warning', $message);
        return redirect()->back();
    }
    
    protected function respondValidationError(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 400);
        }
        
        session()->flash('flash_message_warning', $message);
        return redirect()->back();
    }
    
    protected function respondSuccess(string $message, Request $request, int $jsonStatus = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $jsonStatus);
        }
        
        session()->flash('flash_message', $message);
        return redirect()->back();
    }
}
```

**Files to update:**
- `app/Http/Controllers/DocumentsController.php`
- `app/Http/Controllers/TasksController.php`
- `app/Http/Controllers/ProjectsController.php`
- `app/Http/Controllers/LeadsController.php`
- `app/Http/Controllers/InvoiceLinesController.php`

## 2. Consolidate Permission Checks in Middleware

### Current Problem
Permission checks are scattered:
- Some in controller constructors as inline closures
- Some in controller methods directly
- Some in named middleware
- Duplicated `can()` checks with flash messages

### Recommendation
Create dedicated permission middleware classes for each permission type:

```php
class EnsureUserCan
{
    public function handle($request, Closure $next, string $permission)
    {
        if (! auth()->check() || ! auth()->user()->can($permission)) {
            if ($request->expectsJson()) {
                abort(403, __('You do not have permission'));
            }
            
            session()->flash('flash_message_warning', __('You do not have permission'));
            return redirect()->back();
        }
        
        return $next($request);
    }
}
```

Then use in routes:
```php
Route::delete('/tasks/{task}', [TasksController::class, 'destroy'])
    ->middleware('can:task-delete');
```

**Benefits:**
- Removes permission checks from controller methods
- Centralizes authorization logic
- Consistent error responses
- Easier to test

## 3. Complete the PermissionName Enum Migration

### Current Problem
Some permissions are defined in the enum, others only in the seeder. Tests sometimes use string literals, sometimes use enum values.

### Recommendation
1. Add ALL permissions to `PermissionName` enum
2. Update seeder to use enum values
3. Convert all string permission checks to enum usage:

```php
// Instead of:
auth()->user()->can('task-upload-files')

// Use:
auth()->user()->can(PermissionName::TASK_UPLOAD_FILES->value)
```

**Files to update:**
- `app/Enums/PermissionName.php` - add missing permissions
- `database/seeders/PermissionsTableSeeder.php` - use enum
- All controllers - replace string literals
- All tests - replace string literals

## 4. Improve Test Isolation and Setup

### Current Problem
- Tests manually set up permissions in verbose, repetitive ways
- Cache flushing is inconsistent
- Some tests create new users instead of using `$this->user`
- `fresh()` is called without re-authenticating

### Recommendation
Enhance `AbstractTestCase` with better helpers:

```php
abstract class AbstractTestCase extends BaseTestCase
{
    /**
     * Grant permissions to current test user and re-authenticate
     */
    protected function grantPermissions(array|PermissionName $permissions): self
    {
        $this->withPermissions($permissions);
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);
        return $this;
    }
    
    /**
     * Create a user with specific permissions for testing unauthorized access
     */
    protected function createUserWithPermissions(array|PermissionName $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'test-role']);
        
        foreach ((array)$permissions as $permission) {
            $name = $permission instanceof PermissionName ? $permission->value : $permission;
            $p = Permission::firstOrCreate(['name' => $name]);
            $role->attachPermission($p);
        }
        
        $user->attachRole($role);
        Cache::flush();
        
        return $user->fresh();
    }
}
```

**Files to update:**
- `tests/AbstractTestCase.php`
- All test files - simplify permission setup

## 5. Standardize Status Validation

### Current Problem
Status validation is duplicated across controllers with slightly different implementations:

```php
// TasksController
$validStatus = Status::typeOfTask()->where('id', $input['status_id'])->exists();

// ProjectsController  
$validStatus = Status::typeOfProject()->where('id', $input['status_id'])->exists();
```

### Recommendation
Create a status validation service or add to existing status model:

```php
class Status extends Model
{
    public static function isValidForType(int $statusId, string $type): bool
    {
        return self::where('source_type', $type)
            ->where('id', $statusId)
            ->exists();
    }
}

// Usage:
if (!Status::isValidForType($statusId, Task::class)) {
    // handle error
}
```

**Files to update:**
- `app/Models/Status.php`
- `app/Http/Controllers/TasksController.php`
- `app/Http/Controllers/ProjectsController.php`
- `app/Http/Controllers/LeadsController.php`

## 6. Extract Document Authorization Logic

### Current Problem
`DocumentsController` has complex ownership checking logic embedded in private methods.

### Recommendation
Create a dedicated policy or service:

```php
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        $source = $document->source;
        
        if (!$source) {
            return false;
        }
        
        if ($document->source_type === Client::class) {
            return $source->user_id === $user->id;
        }
        
        if (in_array($document->source_type, [Task::class, Project::class, Lead::class])) {
            return $this->userOwnsAssignableSource($source, $user);
        }
        
        return false;
    }
    
    private function userOwnsAssignableSource($source, User $user): bool
    {
        return $source->user_created_id === $user->id
            || $source->user_assigned_id === $user->id
            || ($source->client && $source->client->user_id === $user->id);
    }
}
```

Register in `AuthServiceProvider`:
```php
protected $policies = [
    Document::class => DocumentPolicy::class,
];
```

**Files to update:**
- Create `app/Policies/DocumentPolicy.php`
- `app/Http/Controllers/DocumentsController.php` - use policy
- `app/Providers/AuthServiceProvider.php` - register policy

## 7. Remove Duplicate Response Headers

### Current Problem
Some successful operations return both session flash messages AND JSON responses, wasting session storage for API calls.

### Recommendation
Only flash session messages for web requests:

```php
// Before:
session()->flash('flash_message', __('Task deleted'));
if ($request->expectsJson()) {
    return response()->json(['message' => __('Task deleted')], 200);
}
return redirect()->back();

// After:
if ($request->expectsJson()) {
    return response()->json(['message' => __('Task deleted')], 200);
}

session()->flash('flash_message', __('Task deleted'));
return redirect()->back();
```

## 8. Missing FormRequest Validation

### Current Problem
Several controllers use direct `$request->input()` without proper FormRequest validation, creating security and data integrity risks.

### Controllers Requiring FormRequests

#### LeadsController
- **Method:** `store()` (lines 100-105)
- **Issue:** Uses `StoreLeadRequest` in signature but then directly accesses `$request->input()`
- **Fields:** `title`, `description`, `user_assigned_id`, `deadline`, `status_id`
- **Solution:** Use `$request->validated()` instead of direct input access

#### TasksController  
- **Methods:** `updateAssign()`, `updateDeadline()`, `updateStatus()` (lines 280+)
- **Issue:** Direct `$request->input()` or `$request->only()` usage
- **Missing:** `UpdateTaskAssignRequest`, `UpdateTaskDeadlineRequest`, `UpdateTaskStatusRequest`

#### ProjectsController
- **Methods:** Similar pattern to TasksController
- **Missing:** `UpdateProjectAssignRequest`, `UpdateProjectDeadlineRequest`, `UpdateProjectStatusRequest`

#### RolesController
- **Method:** `update()` (line 91)
- **Issue:** `$request->input('permissions')` without validation
- **Missing:** `UpdateRoleRequest`

#### CommentController
- **Method:** `store()` (line 19)
- **Issue:** Uses inline validation via `$this->validate()`
- **Missing:** `StoreCommentRequest`

**Priority:** High — Security Risk

---

## 9. Model Constants → Enum Migration

### Current Problem
Models use string constants that should be type-safe enums.

### Constants to Convert

#### Task Model
```php
// Current
public const TASK_STATUS_CLOSED = 'closed';

// Proposed
enum TaskStatus: string {
    case OPEN = 'open';
    case CLOSED = 'closed';
    case IN_PROGRESS = 'in_progress';
}
```

#### Lead Model
```php
// Current
public const LEAD_STATUS_CLOSED = 'closed';

// Proposed - consolidate with Status model
enum LeadStatus: string
```

#### Project Model
```php
// Current (Note inconsistent casing!)
public const PROJECT_STATUS_CLOSED = 'Closed';

// Proposed
enum ProjectStatus: string
```

#### Role Model
```php
// Current
public const OWNER_ROLE = 'owner';
public const ADMIN_ROLE = 'administrator';

// Proposed
enum RoleType: string {
    case OWNER = 'owner';
    case ADMINISTRATOR = 'administrator';
}
```

#### Invoice Model
```php
// Current
public const STATUS_SENT = 'sent';

// Note: InvoiceStatus enum already exists!
// Action: Complete migration, remove constant
```

**Priority:** Medium — Type Safety & Maintainability

---

## 10. Status Model Enum Refactoring

### Major Opportunity
The `Status` model currently stores statuses in the database with `source_type` distinguishing between Task, Lead, and Project statuses.

### Current Approach
- Database table with `source_type` field (Task::class, Lead::class, Project::class)
- Dynamic statuses managed in database
- Scopes: `typeOfTask()`, `typeOfLead()`, `typeOfProject()`

### Proposed Alternatives

#### Option A: Keep Database, Add Validation Enums
- Keep database table for flexibility
- Create enums for **validation** and **default statuses**
- Benefits: Flexibility + type safety for common statuses
- Lower risk migration

#### Option B: Full Enum Migration
- Replace database statuses with dedicated enums
- `TaskStatus`, `LeadStatus`, `ProjectStatus`
- Benefits: Type-safe, no database lookups, simpler queries
- Risks: Loss of dynamic status creation, complex migration

**Recommendation:** Option A — provides type safety while maintaining flexibility.

**Priority:** Medium-High — Affects core domain logic

---

## 11. Controllers Requiring Service Extraction

Controllers exceeding 200 lines should have business logic extracted to services.

### ClientsController (448 lines)
**Contains:**
- Client number generation (already has `ClientNumberService`)
- Billing API integration
- File storage operations

**Extract to:**
- `ClientService` — orchestrate client operations
- `ClientStorageService` — handle file operations
- Better utilize existing `ClientNumberService`

### TasksController (418 lines)
**Contains:**
- File upload logic
- Status update validation
- Assignment logic
- Deadline management

**Extract to:**
- `TaskService` — orchestrate task operations
- Note: Some logic exists in `TaskAction` — consolidate

### DocumentsController (382 lines)
**Contains:**
- Complex authorization logic
- File storage and retrieval

**Extract to:**
- `DocumentPolicy` (already identified in #6)
- `DocumentStorageService`

### ProjectsController (369 lines)
**Contains:**
- Project creation
- Status updates
- Assignment handling

**Extract to:**
- `ProjectService`

### UsersController (362 lines)
**Contains:**
- User lifecycle management
- Calendar integration

**Extract to:**
- `UserService`
- `CalendarService`

### LeadsController (330 lines)
**Extract to:**
- `LeadService`

### InvoicesController (231 lines)
**Note:** Already has `InvoiceCalculator` and `InvoiceNumberService`

**Extract to:**
- `InvoiceService` — orchestration layer

### SettingsController (233 lines)
**Contains:**
- Multiple inline validation blocks
- Integration with existing validation services

**Action:** Improve integration with existing services

**Priority:** High — Maintainability & Testability

---

## 12. Test Organization — Unit vs Feature

### Problem
39 test files located in `tests/Unit/Controllers/` are actually Feature tests (making HTTP requests).

**Why they're Feature tests:**
- Use HTTP methods: `$this->get()`, `$this->post()`, `$this->put()`, `$this->delete()`
- Exercise full controller stack (routes, middleware, controllers)
- Are integration tests, not unit tests

### Affected Directories
```
tests/Unit/Controllers/Absence/        (1 file)
tests/Unit/Controllers/Appointment/    (3 files)
tests/Unit/Controllers/Department/     (1 file)
tests/Unit/Controllers/Document/       (4 files)
tests/Unit/Controllers/Lead/           (5 files)
tests/Unit/Controllers/Payment/        (2 files)
tests/Unit/Controllers/Role/           (1 file)
tests/Unit/Controllers/Search/         (1 file)
tests/Unit/Controllers/Settings/       (2 files)
tests/Unit/Controllers/Task/           (5 files)
tests/Unit/Controllers/User/           (4 files)
... and more
```

### Migration Strategy
1. Move all files to `tests/Feature/Controllers/`
2. Update namespace: `Tests\Unit\Controllers` → `Tests\Feature\Controllers`
3. Continue extending `AbstractTestCase`
4. Group by domain, not test type

### True Unit Tests (Correctly Placed)
```
tests/Unit/Enums/*
tests/Unit/Invoice/InvoiceCalculatorTest.php
tests/Unit/Invoice/InvoiceNumberServiceTest.php
tests/Unit/Repositories/RoleRepositoryTest.php
tests/Unit/Format/*
tests/Unit/Events/*
tests/Unit/Models/*
tests/Unit/Entrust/*
tests/Unit/Deadline/*
```

**Priority:** Medium — Test Organization & Clarity

---

## Phase 2.2: Service Extraction - AI Agent Prompt

**Note:** This is a large architectural refactoring (40 hours) best suited for agentic AI implementation.

### AI Agent Instructions

Extract business logic from the following controllers into dedicated service classes. For each controller:

1. **Analyze the controller** to identify:
   - Complex business logic (>10 lines per method)
   - Multiple database operations in sequence
   - External API integration calls
   - File storage/manipulation logic
   - Complex calculations or transformations

2. **Create the service class** following these patterns:
   ```php
   namespace App\Services\{Domain};
   
   class {Domain}Service
   {
       // Inject dependencies via constructor
       public function __construct(
           private Repository $repository,
           private OtherService $otherService
       ) {}
       
       // Single-purpose, well-named methods
       public function createClientWithValidation(array $data): Client
       {
           // Business logic here
       }
   }
   ```

3. **Update the controller** to use the service:
   - Inject service via constructor
   - Replace business logic with service calls
   - Keep only: validation, authorization, response formatting

4. **Write tests** for the service (in `tests/Unit/{Domain}/`)

5. **Update existing controller tests** to mock the service if needed

### Controllers to Extract (in priority order):

1. **ClientsController** (448 lines) → `ClientService`
   - Priority: Highest (largest controller)
   - Extract: Client number generation, billing API integration, file storage

2. **TasksController** (418 lines) → `TaskService`
   - Extract: File upload logic, status update validation, assignment logic

3. **DocumentsController** (382 lines) → `DocumentStorageService`
   - Extract: File storage, retrieval, complex authorization

4. **ProjectsController** (359 lines) → `ProjectService`
   - Extract: Project creation, status updates, assignment handling

5. **UsersController** (362 lines) → `UserService`
   - Extract: User lifecycle management, calendar integration

6. **LeadsController** (341 lines) → `LeadService`
   - Extract: Lead lifecycle logic, status management

7. **InvoicesController** (231 lines) → `InvoiceService`
   - Note: Already has InvoiceCalculator and InvoiceNumberService
   - Create orchestration layer to coordinate existing services

### Success Criteria:
- All controllers under 200 lines
- Business logic fully tested in service unit tests
- No functionality regressions (all existing tests pass)
- Improved separation of concerns

---

## Phase 3: Medium Priority - Code Quality & Developer Experience

**Estimated Total Time:** 26 hours across 6 tasks

This phase focuses on improving code quality, consistency, and developer experience through better patterns, tooling, and test infrastructure.

---

### 3.1: Standardize JSON vs Web Response Handling (8 hours)

**Goal:** Create consistent response handling across all controllers that serve both web and API requests.

#### Step 1: Create RespondsWithHttpStatus Trait (2 hours)

Create `app/Http/Traits/RespondsWithHttpStatus.php`:

```php
<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithHttpStatus
{
    /**
     * Return success response based on request type
     */
    protected function respondWithSuccess(
        Request $request,
        string $message,
        array $data = [],
        int $jsonStatus = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $data,
            ], $jsonStatus);
        }

        session()->flash('flash_message', $message);
        return redirect()->back();
    }

    /**
     * Return error response based on request type
     */
    protected function respondWithError(
        Request $request,
        string $message,
        int $jsonStatus = 400,
        ?string $flashType = 'flash_message_warning'
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $jsonStatus);
        }

        if ($flashType) {
            session()->flash($flashType, $message);
        }
        return redirect()->back();
    }

    /**
     * Return not found response based on request type
     */
    protected function respondNotFound(
        Request $request,
        string $message = 'Resource not found'
    ): JsonResponse|RedirectResponse {
        return $this->respondWithError($request, $message, 404);
    }

    /**
     * Return unauthorized response based on request type
     */
    protected function respondUnauthorized(
        Request $request,
        string $message = 'Unauthorized'
    ): JsonResponse|RedirectResponse {
        return $this->respondWithError($request, $message, 403);
    }
}
```

#### Step 2: Apply Trait to Controllers (4 hours)

**Controllers to update (in priority order):**

1. **TasksController** - Delete, update status, update assignment methods
2. **ProjectsController** - Delete, update status, update assignment methods  
3. **LeadsController** - Delete, update deadline methods
4. **DocumentsController** - View, download methods
5. **ClientsController** - Delete methods
6. **OffersController** - Status update methods
7. **InvoiceLinesController** - CRUD operations
8. **CommentController** - Store method

**Migration pattern for each controller:**

```php
// Before:
public function destroy(Request $request, $id)
{
    $task = Task::findOrFail($id);
    $task->delete();
    
    session()->flash('flash_message', 'Task deleted');
    return redirect()->back();
}

// After:
use App\Http\Traits\RespondsWithHttpStatus;

class TasksController extends Controller
{
    use RespondsWithHttpStatus;
    
    public function destroy(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        
        return $this->respondWithSuccess(
            $request,
            __('Task deleted successfully')
        );
    }
}
```

#### Step 3: Update Tests (2 hours)

For each updated controller, verify:
- Web tests still pass (expect 302 redirects)
- JSON tests (if they exist) pass (expect 200/201/204 status codes)
- Add JSON tests where missing

**Test pattern:**
```php
public function test_it_deletes_task_via_web()
{
    $task = Task::factory()->create();
    
    $response = $this->actingAs($this->owner)
        ->delete(route('tasks.destroy', $task->external_id));
    
    $response->assertRedirect();
    $response->assertSessionHas('flash_message');
}

public function test_it_deletes_task_via_json()
{
    $task = Task::factory()->create();
    
    $response = $this->actingAs($this->owner)
        ->json('DELETE', route('tasks.destroy', $task->external_id));
    
    $response->assertStatus(200);
    $response->assertJson(['message' => 'Task deleted successfully']);
}
```

---

### 3.2: Consolidate Permission Checks into Middleware (6 hours)

**Goal:** Move scattered permission checks from controllers into reusable middleware.

#### Step 1: Create Permission Middleware (1 hour)

Create `app/Http/Middleware/EnsureUserCan.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class EnsureUserCan
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // Convert string to enum if needed
        $permissionEnum = PermissionName::tryFrom($permission);
        
        if (!$permissionEnum) {
            abort(500, "Invalid permission: {$permission}");
        }
        
        if (!auth()->check() || !auth()->user()->can($permissionEnum->value)) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized');
            }
            
            session()->flash('flash_message_warning', __('You do not have permission to perform this action'));
            return redirect()->back();
        }

        return $next($request);
    }
}
```

#### Step 2: Register Middleware (15 minutes)

In `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    // ... existing middleware
    'can' => \App\Http\Middleware\EnsureUserCan::class,
];
```

#### Step 3: Update Controllers (3 hours)

**Controllers with inline permission checks:**
- LeadsController (delete permission check in constructor)
- TasksController (various permission checks)
- ProjectsController (permission checks)
- DocumentsController (view/download permissions)
- ClientsController (delete permissions)
- UsersController (admin checks)
- RolesController (admin checks)
- SettingsController (admin checks)

**Migration pattern:**

```php
// Before:
public function __construct()
{
    $this->middleware(function ($request, $next) {
        if (!auth()->check() || !auth()->user()->can(PermissionName::LEAD_DELETE->value)) {
            if ($request->expectsJson()) {
                abort(403);
            }
            session()->flash('flash_message_warning', __('You do not have permission to delete leads'));
            return redirect()->back();
        }
        return $next($request);
    }, ['only' => ['destroy', 'destroyJson']]);
}

// After:
public function __construct()
{
    $this->middleware('can:' . PermissionName::LEAD_DELETE->value, [
        'only' => ['destroy', 'destroyJson']
    ]);
}
```

#### Step 4: Update Routes (1 hour)

For some routes, apply middleware directly in route definition:

```php
// In routes/web.php:
Route::middleware(['auth', 'can:' . PermissionName::CLIENT_DELETE->value])
    ->delete('/clients/{id}', [ClientsController::class, 'destroy'])
    ->name('clients.destroy');
```

#### Step 5: Test Coverage (45 minutes)

Verify for each updated controller:
- Authorized users can perform actions
- Unauthorized users get 403 (JSON) or redirect with flash message (web)
- Permission checks work consistently

---

### 3.3: Add Test Metadata Attributes (4 hours)

**Goal:** Improve test clarity and code coverage reporting with PHP 8 attributes.

#### Step 1: Add Attributes to Test Classes (3 hours)

For each test class in `tests/Feature/` and `tests/Unit/`:

```php
<?php

namespace Tests\Feature\Controllers\Task;

use App\Models\Task;
use App\Models\User;
use Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(TasksController::class)]
#[UsesClass(Task::class)]
#[UsesClass(Status::class)]
class TasksControllerTest extends AbstractTestCase
{
    // ... tests
}
```

**Mapping guide:**
- `#[CoversClass]` - The primary class being tested
- `#[UsesClass]` - Models, services, or other classes used but not directly tested

#### Step 2: Verify Coverage Reports (1 hour)

Run with coverage:
```bash
php artisan test --coverage --min=80
```

Benefits:
- More accurate coverage reporting
- IDE support for "jump to test"
- Better test organization

---

### 3.4: Standardize Test Naming Conventions (3 hours)

**Goal:** Convert remaining tests to modern `it_*` naming pattern.

#### Current Status
- 48 tests already use `it_*` pattern
- 41 tests still use old `test*` pattern

#### Migration Pattern

```php
// Before:
public function testUserCanCreateTask()
{
    // ...
}

// After:
public function it_allows_authorized_user_to_create_task()
{
    // ...
}
```

#### Naming Guidelines

- Use snake_case after `it_`
- Be descriptive and specific
- Include actor: "authorized_user", "owner", "admin"
- Include action: "create", "update", "delete", "view"
- Include expected outcome: "successfully", "with_validation_error", "returns_404"

**Examples:**
```php
it_allows_owner_to_delete_client()
it_prevents_unauthorized_user_from_viewing_document()
it_returns_validation_error_when_title_is_missing()
it_updates_task_status_successfully()
it_calculates_invoice_total_with_tax()
```

#### Affected Test Files (41 files):

Search and update:
```bash
grep -r "public function test" tests/ --include="*.php" | grep -v "it_"
```

---

### 3.5: Improve AbstractTestCase Helpers (3 hours)

**Goal:** Reduce test boilerplate with better helper methods.

#### Step 1: Add Permission Helper (1 hour)

In `tests/AbstractTestCase.php`:

```php
/**
 * Grant specific permissions to a user
 */
protected function grantPermissions(User $user, PermissionName ...$permissions): User
{
    foreach ($permissions as $permission) {
        $permissionModel = Permission::firstOrCreate([
            'name' => $permission->value,
            'display_name' => $permission->label(),
        ]);
        
        // Ensure user's role has the permission
        $role = $user->roles->first();
        if ($role && !$role->permissions->contains($permissionModel)) {
            $role->permissions()->attach($permissionModel);
        }
    }
    
    // Reload user to clear cached permissions
    return $user->fresh();
}
```

Usage in tests:
```php
public function it_allows_user_with_permission_to_delete_client()
{
    $user = User::factory()->create();
    $user = $this->grantPermissions($user, PermissionName::CLIENT_DELETE);
    
    $client = Client::factory()->create();
    
    $response = $this->actingAs($user)
        ->delete(route('clients.destroy', $client->external_id));
    
    $response->assertSuccessful();
}
```

#### Step 2: Add Resource Creation Helpers (1 hour)

```php
/**
 * Create a task with status
 */
protected function createTaskWithStatus(string $statusTitle = 'Open', array $attributes = []): Task
{
    $status = Status::factory()->create([
        'title' => $statusTitle,
        'source_type' => Task::class,
    ]);
    
    return Task::factory()->create(array_merge([
        'status_id' => $status->id,
    ], $attributes));
}

/**
 * Create a lead with status
 */
protected function createLeadWithStatus(string $statusTitle = 'Open', array $attributes = []): Lead
{
    $status = Status::factory()->create([
        'title' => $statusTitle,
        'source_type' => Lead::class,
    ]);
    
    return Lead::factory()->create(array_merge([
        'status_id' => $status->id,
    ], $attributes));
}
```

#### Step 3: Add Assertion Helpers (1 hour)

```php
/**
 * Assert JSON response has success message
 */
protected function assertJsonSuccess($response, string $expectedMessage = null): void
{
    $response->assertStatus(200);
    $response->assertJsonStructure(['message']);
    
    if ($expectedMessage) {
        $response->assertJson(['message' => $expectedMessage]);
    }
}

/**
 * Assert redirect with flash message
 */
protected function assertRedirectWithFlash($response, string $expectedMessage = null): void
{
    $response->assertRedirect();
    $response->assertSessionHas('flash_message');
    
    if ($expectedMessage) {
        $this->assertEquals($expectedMessage, session('flash_message'));
    }
}
```

---

### 3.6: Standardize Status Validation ✅ (COMPLETED)

Already implemented in commit 54b8829. Added `Status::isValidForType()` static method.

---

## Phase 4: Low Priority - Polish & Consistency

**Estimated Total Time:** 11 hours across 4 tasks

This phase addresses final polish items, consistency improvements, and technical debt cleanup.

---

### 4.1: Update PHPStorm Region Syntax (2 hours)

**Goal:** Modernize region syntax from `//region` to `#region` for better IDE support.

#### Current Usage
- 48 test files use `//region` syntax
- 22 model files use `//region` syntax

#### Migration Script

Create `scripts/update-regions.sh`:
```bash
#!/bin/bash

# Find all PHP files with //region and convert to #region
find tests/ app/Models/ -name "*.php" -type f -exec sed -i 's|//region|#region|g' {} \;
find tests/ app/Models/ -name "*.php" -type f -exec sed -i 's|//endregion|#endregion|g' {} \;

echo "Updated region syntax in test and model files"
```

#### Manual Review (1 hour)

After running script:
1. Review changes in Git
2. Ensure no false positives (e.g., in comments or strings)
3. Verify IDE still recognizes regions

#### Benefits
- Modern PHP 8+ syntax
- Better PHPStorm/IDE integration
- Consistent with Laravel 10+ conventions

---

### 4.2: Role Constants to Enums ✅ (COMPLETED)

Already implemented in commit 54b8829. Created `RoleType` enum.

#### Remaining Work: Replace Usage (1 hour)

Find remaining string literal usage:
```bash
grep -r "Role::OWNER_ROLE\|Role::ADMIN_ROLE" app/ --include="*.php"
```

Update to use enum:
```php
// Before:
if ($role->name === Role::OWNER_ROLE) {
    // ...
}

// After:
if (RoleType::fromString($role->name) === RoleType::OWNER) {
    // ...
}
```

---

### 4.3: Remove Duplicate Response Headers (2 hours)

**Goal:** Ensure flash messages only set for web requests, clean up redundant header setting.

#### Step 1: Audit Flash Message Usage (1 hour)

Find all `session()->flash()` calls:
```bash
grep -r "session()->flash\|Session::flash" app/Http/Controllers/ --include="*.php"
```

Identify controllers that:
- Set flash messages for JSON requests
- Set flash messages before `expectsJson()` check
- Set multiple flash messages for same action

#### Step 2: Fix Controllers (1 hour)

**Pattern to follow:**
```php
// Before:
session()->flash('flash_message', 'Success');
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}
return redirect()->back();

// After:
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}
session()->flash('flash_message', 'Success');
return redirect()->back();
```

**Controllers to check:**
- TasksController
- ProjectsController
- LeadsController
- ClientsController
- DocumentsController
- SettingsController

---

### 4.4: Audit Critical Bug Patterns (4 hours)

**Goal:** Systematically check for known bug patterns discovered during test fixes.

#### Pattern 1: Relationship Object vs String Comparisons (1 hour)

**Search for potential issues:**
```bash
grep -r "->status ==" app/Models/ --include="*.php"
grep -r "->role ==" app/Models/ --include="*.php"
```

**Check pattern:**
```php
// WRONG: Comparing relationship object to string
if ($model->status == 'closed') { }

// CORRECT: Access relationship property
if ($model->status && $model->status->title == 'closed') { }

// BETTER: Use enum helper
if ($model->status && TaskStatus::isClosed($model->status->title)) { }
```

#### Pattern 2: Double Division in Calculations (1 hour)

**Search for potential issues:**
```bash
grep -r "/ 100.*/ 100\|/ 10000" app/ --include="*.php"
```

**Check pattern:**
```php
// WRONG: Double division
$rate = ($percentage / 100) / 100;

// CORRECT: Single division for percentage stored as integer
$rate = $percentage / 10000; // 2100 / 10000 = 0.21
```

**Files to audit:**
- InvoiceCalculator
- Tax model methods
- Any percentage calculations

#### Pattern 3: Null Checks in Traits (1 hour)

**Audit all traits:**
```bash
find app/Traits/ -name "*.php" -type f
```

For each trait method that accesses model properties:
- Check if property is optional (nullable in database)
- Ensure null check before comparison/operation
- Add return early pattern

**Pattern:**
```php
// In DeadlineTrait.php:
public function isOverDeadline(): bool
{
    // Early returns for null/closed cases
    if ($this->isClosed() || !$this->deadline) {
        return false;
    }
    
    return $this->deadline < Carbon::now();
}
```

#### Pattern 4: Cached Permissions in Tests (1 hour)

**Audit test files:**
```bash
grep -r "attachPermission\|detachPermission" tests/ --include="*.php" -A 5
```

Ensure pattern:
```php
// WRONG: Permissions cached, user not reloaded
$user->roles->first()->attachPermission($permission);
$this->actingAs($user); // Uses cached permissions

// CORRECT: Reload user after permission change
$user->roles->first()->attachPermission($permission);
$user = $user->fresh(); // Clear cache
$this->actingAs($user);
```

**Create test helper** (if not exists):
```php
// In AbstractTestCase:
protected function attachPermissionAndReload(User $user, Permission $permission): User
{
    $user->roles->first()->attachPermission($permission);
    return $user->fresh();
}
```

---

## Phase 2.2 Reminder: Service Extraction (Paused)

This 40-hour task has been paused and documented above as an AI agent prompt. When ready to implement:

1. Use the AI agent prompt in the Phase 2.2 section
2. Implement controllers in priority order (largest first)
3. Follow the service extraction pattern
4. Ensure all tests pass after each extraction

---

## Priority Order

1. **High Priority** (Do first - affects all controllers):
   - #1: Standardize JSON vs Web Response Handling
   - #2: Consolidate Permission Checks in Middleware
   - #8: Missing FormRequest Validation
   - #11: Service Extraction (largest controllers first)

2. **Medium Priority** (Improves code quality significantly):
   - #3: Complete PermissionName Enum Migration
   - #4: Improve Test Isolation and Setup
   - #9: Model Constants → Enum Migration
   - #10: Status Model Enum Refactoring
   - #12: Test Organization

3. **Low Priority** (Nice to have):
   - #5: Standardize Status Validation
   - #6: Extract Document Authorization Logic
   - #7: Remove Duplicate Response Headers

## Estimated Impact

| Refactoring | Files Affected | LOC Reduced | Complexity Reduced | Bug Risk Reduced | Time Estimate |
|-------------|----------------|-------------|-------------------|------------------|---------------|
| #1 - Response Handling | ~10 | ~200 | High | High | 8 hours |
| #2 - Permission Middleware | ~15 | ~300 | High | High | 12 hours |
| #3 - Enum Migration | ~20 | ~50 | Medium | Medium | 6 hours |
| #4 - Test Helpers | ~30 | ~400 | Medium | Low | 4 hours |
| #5 - Status Validation | ~4 | ~30 | Low | Low | 2 hours |
| #6 - Document Policy | ~2 | ~50 | Medium | Medium | 4 hours |
| #7 - Remove Duplication | ~10 | ~100 | Low | Low | 2 hours |
| #8 - FormRequest Creation | ~15 | +500/-100 | High | High | 8 hours |
| #9 - Model Enums | ~8 | ~200 | Medium | Medium | 6 hours |
| #10 - Status Enum | ~25 | ~300 | High | Medium | 12 hours |
| #11 - Service Extraction | ~8 | -800/+1000 | High | Medium | 40 hours |
| #12 - Test Migration | 39 | ~50 | Low | Low | 4 hours |
| **TOTAL** | ~140+ files | ~2000+ | High | High | **~108 hours** |

## Testing Strategy

After each refactoring:
1. Run the full test suite to ensure no regressions
2. Test both web and API endpoints manually
3. Verify authorization works correctly for both JSON and web requests
4. Check that flash messages appear correctly in web UI
5. Verify JSON responses have correct structure and status codes
