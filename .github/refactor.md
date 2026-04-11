# Refactoring Suggestions

After fixing the test failures, several patterns emerged that could benefit from refactoring to improve code quality, consistency, and maintainability.

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

## Priority Order

1. **High Priority** (Do first - affects all controllers):
   - #1: Standardize JSON vs Web Response Handling
   - #2: Consolidate Permission Checks in Middleware

2. **Medium Priority** (Improves code quality significantly):
   - #3: Complete PermissionName Enum Migration
   - #4: Improve Test Isolation and Setup

3. **Low Priority** (Nice to have):
   - #5: Standardize Status Validation
   - #6: Extract Document Authorization Logic
   - #7: Remove Duplicate Response Headers

## Estimated Impact

| Refactoring | Files Affected | LOC Reduced | Complexity Reduced | Bug Risk Reduced |
|-------------|----------------|-------------|-------------------|------------------|
| #1 - Response Handling | ~10 | ~200 | High | High |
| #2 - Permission Middleware | ~15 | ~300 | High | High |
| #3 - Enum Migration | ~20 | ~50 | Medium | Medium |
| #4 - Test Helpers | ~30 | ~400 | Medium | Low |
| #5 - Status Validation | ~4 | ~30 | Low | Low |
| #6 - Document Policy | ~2 | ~50 | Medium | Medium |
| #7 - Remove Duplication | ~10 | ~100 | Low | Low |

## Testing Strategy

After each refactoring:
1. Run the full test suite to ensure no regressions
2. Test both web and API endpoints manually
3. Verify authorization works correctly for both JSON and web requests
4. Check that flash messages appear correctly in web UI
5. Verify JSON responses have correct structure and status codes
