# Error Repair Plan - DaybydayCRM Tests

This document serves as a guide for repairing common test errors and failures in the DaybydayCRM project.

## 1. SQLSTATE[HY000]: General error: 1364 Field 'X' doesn't have a default value
- **Common Fields:** `external_id`, `ip_address`, `color`, `status`.
- **Structural Fixes:**
    - **UUIDs:** Ensure models using `external_id` have a `boot` method or use a Trait that automatically generates a UUID on the `creating` event.
    - **Activity Log:** The `Activity` model must automatically capture `ip_address`. Use `request()->ip() ?: '127.0.0.1'` in the model's `boot` method.
    - **Factories:** Update legacy factories in `database/factories/` to include all required fields with sensible defaults (e.g., Faker data).
- **Test-Level Fixes:**
    - Explicitly provide missing fields in `factory(Model::class)->create([...])` or `Model::create([...])` calls within the test.

## 2. SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'X-Y' for key 'PRIMARY'
- **Context:** Usually occurs in the `role_user` table when a test attempts to attach a role to a user that already has it (often due to seeders or `TestCase` setup).
- **Structural Fixes:**
    - **EntrustUserTrait:** The `attachRole` method (or equivalent) should check for the existence of the relationship before calling `attach()`.
    - **Example Implementation:**
      ```php
      if (!$this->roles()->where('id', $role->id)->exists()) {
          $this->roles()->attach($role);
      }
      ```
- **Test-Level Fixes:**
    - Avoid re-attaching roles in test methods if they are already handled in `TestCase::setUp()`.

## 3. PHPUnit 10+ Compatibility Issues
- **Issue:** `assertObjectHasAttribute` is removed in PHPUnit 10.
- **Fix:** Replace with `$this->assertTrue(property_exists($object, 'propertyName'))`.
- **Issue:** `toDate()` calls on strings.
- **Fix:** Ensure objects are cast to Carbon instances or use `Carbon::parse($string)->toDateString()` for consistent comparisons.

## 4. Permission & Authorization (403 Forbidden)
- **Issue:** Tests fail because the acting user lacks the required role (e.g., 'owner', 'administrator') or specific permission.
- **Fix:** In the test's `setUp()` or before the request, assign the necessary role or permission:
  ```php
  $role = Role::where('name', 'owner')->first();
  $this->user->attachRole($role);
  ```
- **Authorization Testing Pattern:** When testing authorization:
  1. Create a role with the required permission(s)
  2. Attach permission(s) to the role using `attachPermission()`
  3. Create a user and attach the role using `attachRole()`
  4. Use `actingAs($user)` to authenticate as that user
  5. Test both authorized and unauthorized scenarios
- **Note:** Ensure the `RolesTableSeeder` and `PermissionsTableSeeder` have run (usually via `Artisan::call('db:seed')` in `TestCase`).

## 5. "Call to a member function X() on null"
- **Cause:** Missing relationships in test setup (e.g., `$client->primaryContact` is null).
- **Fix:** Ensure dependent models are created. For `primaryContact`, a `Contact` must be created with `is_primary => true` and the correct `client_id`.

## 6. Mass Assignment Protection Testing
- **Pattern:** When testing that controllers properly filter input fields:
  1. Capture original values of protected fields
  2. Submit request with both allowed and disallowed fields
  3. Refresh the model and verify only allowed fields were updated
  4. Assert protected fields remain unchanged
- **Example:**
  ```php
  $originalTitle = $model->title;
  $response = $this->json('PATCH', route('model.update'), [
      'status_id' => $newStatus->id,  // Allowed
      'title' => 'Malicious Change',  // Should be blocked
  ]);
  $model->refresh();
  $this->assertEquals($newStatus->id, $model->status_id);
  $this->assertEquals($originalTitle, $model->title); // Unchanged
  ```

## 7. Junie's Repair Workflow
When repairing a test:
1.  Add the `#[Group('junie_repaired')]` attribute to the test method.
2.  Add `$this->markTestIncomplete('error repaired by junie');` (or 'failure repaired by junie').
3.  Implement the fix.
4.  Remove the `markTestIncomplete()` marker once the test passes consistently.

## 8. Authorization Fix Testing Standards (Added 2026-04-08)
When adding authorization tests for new permission checks:
1.  Use `#[Group('authorization-fix')]` attribute to group related tests
2.  Test both positive (with permission) and negative (without permission) cases
3.  Verify the actual operation succeeds/fails (not just HTTP status)
4.  For delete operations, use `assertSoftDeleted()` or `assertDatabaseHas()`
5.  For update operations, verify the specific field was or wasn't changed
6.  Create dedicated test roles with only the permissions being tested
7.  Always use `withoutMiddleware(VerifyCsrfToken::class)` in setup
8.  Test real functionality, not just `->ok()` assertions

