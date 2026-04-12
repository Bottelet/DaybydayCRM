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
