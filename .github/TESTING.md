# Testing Guide & Isolation Standards

## Core Principles

### 1. Strict Test Isolation (CRITICAL)
Every test MUST be self-contained. The "Cascade Problem" (where tests depend on side effects from other tests) is prohibited.

- **Create Own Data:** Never rely on seeders or other tests. Use factories to create exactly what you need.
- **Single Purpose:** Each test should verify one specific behavior.
- **Clean State:** Use `RefreshDatabase` or `DatabaseTransactions` to ensure a clean database state for each test.
- **Random Order:** Tests must be runnable in any order.

### 2. Data Normalization
Never compare different types without normalization.
- **Dates:** Always normalize Carbon objects to ISO strings: `$model->created_at->toISOString()`.
- **Numbers:** Ensure consistent types (float vs string) before assertion.

### 3. One Request Per Test
Unless explicitly testing a multi-step workflow, each test should perform exactly one HTTP request. This prevents state leakage and makes debugging easier.

---

## Common Error Patterns & Fixes

### 1. Missing Database Defaults
- **Symptom:** `SQLSTATE[HY000]: General error: 1364 Field 'X' doesn't have a default value`
- **Cause:** Fields like `external_id`, `ip_address`, or `status` are mandatory but not set.
- **Fix:** 
    - Ensure the model uses `HasExternalId` trait for UUIDs.
    - Update factories to include required fields.
    - For `Activity` logs, ensure `ip_address` is captured in the model's `boot` method.

### 2. Unique Constraint Violations
- **Symptom:** `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry`
- **Context:** Usually happens when attaching roles/permissions that already exist.
- **Fix:** Use `attachPermission()` or `attachRole()` which now include existence checks. Reload the user using `$user->fresh()` after modification.

### 3. Null Relationship Access
- **Symptom:** `Call to a member function X() on null`
- **Fix:** Ensure all related models are created in the test setup. For example, if a test requires a `primaryContact`, create a `Contact` with `is_primary => true`.

### 4. VAT/Percentage Calculation Errors
- **Symptom:** Invoice totals off by factor of 100, status showing as 'partial_paid' instead of 'paid'
- **Cause:** VAT is stored as `percentage × 100` (e.g., 2100 = 21%) requiring division by 10000, not 100
- **Fix:** 
    - In calculations: `$vatRate = $storedVat / 10000;` (not `/100`)
    - In tests: Create explicit `Setting::factory()->create(['vat' => 0])` for deterministic calculations

### 5. JSON vs Web Response Status Codes
- **Symptom:** Test expecting 200/403 gets 302, or expecting 302 gets 200/403
- **Cause:** Test uses `$this->json()` but controller doesn't check `$request->expectsJson()`
- **Fix:**
    - Controllers must differentiate: `if ($request->expectsJson()) { return response()->json(..., 200); }`
    - JSON deletes return 200, web deletes return 302
    - JSON auth failures return 403, web auth failures return 302

### 6. Status Validation Failures
- **Symptom:** Status update returns 400 validation error
- **Cause:** Status `source_type` uses string literal ('task') but scope expects class name (Task::class)
- **Fix:** Always use full class names: `Status::factory()->create(['source_type' => Task::class])`

### 7. Null Safety in Trait Methods
- **Symptom:** Trait methods return unexpected true/false when property is null
- **Cause:** Methods don't check for null before accessing optional properties
- **Fix:** Add null guard: `if (!$this->deadline) { return false; }` before comparisons

### 8. Storage/File Tests in Testing Environment
- **Symptom:** Document view/download tests fail with "File does not exist"
- **Cause:** Storage services return null in testing environment
- **Fix:** Return test doubles: `if (config('app.env') === 'testing') { return 'fake file content'; }`

---

## Testing Patterns

### Role & Permission Setup
```php
// ✅ CORRECT PATTERN
$user = User::factory()->withRole('employee')->create();
$permission = Permission::firstOrCreate(['name' => 'absence-manage']);

$user->roles->first()->attachPermission($permission);
Cache::flush(); // Clear Entrust cache
$user = $user->fresh(); // Reload from DB to refresh in-memory roles

$this->actingAs($user);
```

### Notification & Storage Fakes
- Always use `Notification::fake()` and `Storage::fake()` at the start of relevant tests.
- Use `Notification::assertSentTo()` and `Storage::disk()->assertExists()`.

### Deterministic Test Data for Calculations
When testing calculations (invoices, taxes, payments), always create Setting records explicitly:
```php
// ✅ CORRECT PATTERN
protected function setUp(): void
{
    parent::setUp();
    
    // Create Setting with known VAT value for deterministic calculations
    Setting::factory()->create(['vat' => 0]); // or specific value like 2100 for 21%
    
    $this->invoice = Invoice::factory()->create();
}
```

This prevents tests from inheriting random VAT values from database seeders.

### JSON vs Web Request Testing
Always specify the request type explicitly:
```php
// ✅ For JSON API tests
$response = $this->json('DELETE', route('tasks.destroy', $task->external_id));
$response->assertStatus(200); // JSON deletes return 200

// ✅ For web tests  
$response = $this->delete(route('tasks.destroy', $task->external_id));
$response->assertRedirect(); // Web deletes return 302
```

### PHPUnit 10+ Attributes
Use PHPUnit 10 attributes instead of PHPDoc annotations:
- `#[Test]` instead of `@test`
- `#[Group('name')]` instead of `@group name`

---

## Isolation Checklist
- [ ] Does the test create its own data?
- [ ] Does it use `RefreshDatabase` or `DatabaseTransactions`?
- [ ] Are dates normalized before assertion?
- [ ] Is there only one HTTP request?
- [ ] Are all relationships explicitly set up?
- [ ] Does it pass when run in isolation?
