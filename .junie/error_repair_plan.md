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
- **Issue:** Tests fail because the acting user lacks the required role (e.g., 'owner', 'administrator').
- **Fix:** In the test's `setUp()` or before the request, assign the necessary role:
  ```php
  $role = Role::where('name', 'owner')->first();
  $this->user->attachRole($role);
  ```
- **Note:** Ensure the `RolesTableSeeder` and `PermissionsTableSeeder` have run (usually via `Artisan::call('db:seed')` in `TestCase`).

## 5. "Call to a member function X() on null"
- **Cause:** Missing relationships in test setup (e.g., `$client->primaryContact` is null).
- **Fix:** Ensure dependent models are created. For `primaryContact`, a `Contact` must be created with `is_primary => true` and the correct `client_id`.

## 6. Junie's Repair Workflow
When repairing a test:
1.  Add the `#[Group('junie_repaired')]` attribute to the test method.
2.  Add `$this->markTestIncomplete('error repaired by junie');` (or 'failure repaired by junie').
3.  Implement the fix.

## 7. Authorization & Security Tests
- **Document Access Control (CWE-639, CWE-862):**
    - Document view/download operations require ownership validation via `canAccessDocument()` method
    - Access granted if user:
        - Created the source entity (Task/Project/Lead)
        - Is assigned to the source entity
        - Owns the associated Client
    - Test coverage in `tests/Unit/Controllers/Document/DocumentsControllerAuthorizationTest.php`
    
- **Assignment Permission Checks:**
    - Tasks: Require `can-assign-new-user-to-task` permission
    - Projects: Require `can-assign-new-user-to-project` permission
    - Leads: Require `can-assign-new-user-to-lead` permission
    - Test coverage in respective `*AssignmentAuthorizationTest.php` files
    
- **Testing Authorization:**
    - Create users with and without required permissions
    - Use `actingAs($user)` to simulate authenticated requests
    - Test both authorized and unauthorized access scenarios
    - Verify session flash messages for denied access
4.  Remove the `'error incomplete by junie'` marker if it was previously added during the investigation phase.
