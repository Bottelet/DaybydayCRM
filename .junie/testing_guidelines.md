# Testing Guidelines for DaybydayCRM

**Also see:** [.github/TESTING.md](../.github/TESTING.md) for comprehensive testing documentation.

## Quick Rules

### MUST Follow
1. **Test Isolation:** Every test MUST be self-contained
2. **Data Creation:** Use factories, never rely on seeders or other tests
3. **Permissions:** Always call `$user->fresh()` after attaching permissions
4. **Normalization:** Convert Carbon to strings before comparison: `->toISOString()`
5. **One Purpose:** One HTTP request per test (unless testing workflows)

### NEVER Do
1. Share state between tests
2. Depend on test execution order
3. Compare Carbon objects directly to strings
4. Use seeders for test data
5. Access `$user->roles` before attaching permissions (loads into memory!)

---

## Test Organization

### Feature vs Unit Tests

**Feature Tests** (`tests/Feature/`)
- Make HTTP requests (`$this->get()`, `$this->post()`, etc.)
- Test full stack: routes → middleware → controllers → services → models
- Test user workflows and integrations
- **CRITICAL:** All controller tests MUST be in `tests/Feature/Controllers/`

**Unit Tests** (`tests/Unit/`)
- Test single classes in isolation
- No HTTP requests
- No database (use mocks/stubs)
- Examples: Services, Formatters, Helpers, Enums

### Current Issue
- 39 HTTP test files are incorrectly in `tests/Unit/Controllers/`
- These MUST be moved to `tests/Feature/Controllers/`

---

## Critical Bug Patterns to Avoid

### 1. Relationship Object vs String Comparison
```php
// WRONG: Comparing relationship object to string
if ($this->status == 'closed') { }

// RIGHT: Access relationship property
if ($this->status && $this->status->title == 'closed') { }
```

### 2. Double Division in Percentages
```php
// VAT stored as: 2100 (representing 21%)

// WRONG: Double division
$rate = ($vat / 100) / 100; // 0.0021 instead of 0.21

// RIGHT: Single division
$rate = $vat / 10000; // 0.21
```

### 3. Null Relationship Access
```php
// WRONG: No null check
return $this->deadline < Carbon::now();

// RIGHT: Check for null first
if (!$this->deadline) {
    return false;
}
return $this->deadline < Carbon::now();
```

### 4. Cached Roles/Permissions
```php
// WRONG: Permission check will fail
$user->roles->first()->attachPermission($perm);
$this->actingAs($user);
// Permission check fails because roles loaded before attach

// RIGHT: Reload user after modifying permissions
$user->roles->first()->attachPermission($perm);
$user = $user->fresh(); // CRITICAL
$this->actingAs($user);
```

### 5. JSON vs Web Response Status
```php
// WRONG: Always redirects
session()->flash('flash_message', 'Success');
return redirect()->back();

// RIGHT: Check request type
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}
session()->flash('flash_message', 'Success');
return redirect()->back();
```

### 6. Storage Services in Tests
```php
// WRONG: Returns null in testing
return Storage::disk('local')->get($file);

// RIGHT: Return fake content for tests
if (config('app.env') === 'testing') {
    return 'fake file content';
}
return Storage::disk('local')->get($file);
```

---

## Test Structure Standards

### Test Naming
```php
// PREFERRED: BDD-style with it_ prefix
public function it_creates_client_with_valid_data()
public function it_prevents_unauthorized_access()
public function it_validates_required_fields()

// ACCEPTABLE: test_ prefix
public function test_creates_client_with_valid_data()
```

### Test Organization
```php
class ClientsControllerTest extends AbstractTestCase
{
    // region Setup
    protected function setUp(): void
    {
        parent::setUp();
        // Create test data here
    }
    // endregion
    
    // region Happy Path
    public function it_creates_client_successfully() { }
    public function it_updates_client_successfully() { }
    // endregion
    
    // region Validation
    public function it_validates_required_fields() { }
    public function it_validates_email_format() { }
    // endregion
    
    // region Authorization
    public function it_prevents_unauthorized_access() { }
    public function it_allows_admin_access() { }
    // endregion
}
```

### Test Metadata (Recommended)
```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ClientNumberService::class)]
#[UsesClass(ClientNumberConfig::class)]
class ClientNumberServiceTest extends AbstractTestCase
{
    // Tests
}
```

---

## Permission Testing Patterns

### Method 1: Using Test Helpers
```php
public function it_allows_task_deletion_with_permission()
{
    // Use helper from AbstractTestCase
    $this->asOwner(); // or asAdmin()
    
    $task = Task::factory()->create();
    $response = $this->delete(route('tasks.destroy', $task->external_id));
    
    $response->assertStatus(302);
}
```

### Method 2: Explicit Permission Grant
```php
public function it_allows_user_with_permission()
{
    $permission = Permission::firstOrCreate(['name' => 'task-delete']);
    $this->user->roles->first()->attachPermission($permission);
    $this->user = $this->user->fresh(); // CRITICAL!
    $this->actingAs($this->user);
    
    $task = Task::factory()->create(['user_created_id' => $this->user->id]);
    $response = $this->delete(route('tasks.destroy', $task->external_id));
    
    $response->assertStatus(302);
}
```

### Method 3: Create User With Permissions
```php
public function it_denies_user_without_permission()
{
    $userWithoutPerm = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'test-role']);
    $userWithoutPerm->attachRole($role);
    $userWithoutPerm = $userWithoutPerm->fresh();
    
    $this->actingAs($userWithoutPerm);
    
    $task = Task::factory()->create();
    $response = $this->delete(route('tasks.destroy', $task->external_id));
    
    $response->assertStatus(302); // Redirected back
}
```

---

## Data Setup Patterns

### Factory Usage
```php
// GOOD: Create specific test data
$client = Client::factory()->create([
    'company_name' => 'Test Company',
    'user_id' => $this->user->id,
]);

// GOOD: Create related data
$task = Task::factory()
    ->for($client)
    ->create([
        'user_created_id' => $this->user->id,
    ]);
```

### Settings Management
```php
// For calculation tests, always create Setting with known values
protected function setUp(): void
{
    parent::setUp();
    
    // Prevent random VAT from seeder affecting tests
    Setting::factory()->create(['vat' => 0]);
    
    // Now calculations are deterministic
}
```

### Status Validation
```php
// WRONG: Using string literal
Status::factory()->create(['source_type' => 'task']);

// RIGHT: Using class name
Status::factory()->create(['source_type' => Task::class]);
```

---

## Response Assertions

### Web Responses
```php
$response = $this->post(route('clients.store'), $data);

$response->assertStatus(302); // Redirect
$response->assertSessionHas('flash_message');
$response->assertRedirect(route('clients.index'));
```

### JSON Responses
```php
$response = $this->json('POST', route('api.clients.store'), $data);

$response->assertStatus(201); // Created
$response->assertJson(['message' => 'Client created']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
```

### Mixed (Controller handles both)
```php
// Web request
$response = $this->post(route('tasks.destroy', $task->external_id));
$response->assertStatus(302);

// JSON request
$response = $this->json('DELETE', route('tasks.destroy', $task->external_id));
$response->assertStatus(200);
$response->assertJson(['message' => 'Task deleted']);
```

---

## Common Test Failures & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| Field 'X' doesn't have default | Missing trait or factory definition | Add `HasExternalId` trait or update factory |
| Duplicate entry 1062 | Cached permissions not reloaded | Call `$user->fresh()` after permission changes |
| Expected 302 got 200/403 | Wrong test method (web vs JSON) | Use `$this->json()` for API tests |
| Expected 200 got 302 | Controller not checking `expectsJson()` | Add JSON response handling to controller |
| Null pointer in trait | No null check | Add null check: `if (!$this->property) return false;` |
| VAT calculation wrong | Double division | Use `$vat / 10000` not `($vat / 100) / 100` |
| File does not exist | Storage service returns null | Add test double: `if (env('APP_ENV') === 'testing') return 'fake';` |
| Permission check fails | Permission attached after roles loaded | Call `$user->fresh()` before `actingAs()` |

---

## Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/Controllers/ClientsControllerTest.php

# Specific test method
php artisan test --filter it_creates_client_successfully

# With coverage
php artisan test --coverage

# Parallel execution
php artisan test --parallel
```

---

## Test Database Best Practices

### Use RefreshDatabase
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;
    
    // Tests run on fresh database each time
}
```

### Avoid DatabaseTransactions for HTTP tests
- `RefreshDatabase` is faster for HTTP tests
- `DatabaseTransactions` better for unit tests
- Don't mix both in same test class

---

## Summary Checklist

Before committing tests, verify:

- [ ] Tests are in correct directory (Feature vs Unit)
- [ ] Tests are self-contained (no shared state)
- [ ] Permissions reloaded with `fresh()` before `actingAs()`
- [ ] Carbon objects normalized before comparison
- [ ] One HTTP request per test (unless workflow)
- [ ] Both JSON and Web responses tested (if applicable)
- [ ] Null checks in all trait methods
- [ ] Known Setting values for calculation tests
- [ ] Class names (not strings) for `source_type`
- [ ] Test naming follows convention (`it_*` or `test_*`)
