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
