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
4.  Remove the `'error incomplete by junie'` marker if it was previously added during the investigation phase.

## 7. Security Best Practices (Added 2026-04-08)

### Critical Security Checks

- **Class Instantiation:** Never use user input directly as class names. Always use allowlist validation.
  - Example: SearchController now validates search types against an allowlist before instantiation.
  
- **Permission Checks:** All sensitive operations (delete, update, create) must have proper permission checks.
  - Use middleware for controller-level protection: `$this->middleware('permission.name', ['only' => ['method']]);`
  - Use inline checks for method-level protection: `if (! auth()->user()->can('permission-name')) { ... }`
  
- **Mass Assignment Protection:** Never use `$request->all()` with `fill()` or `update()`.
  - Always use `$request->only(['field1', 'field2'])` with explicit field allowlists.
  - Verify `$fillable` arrays in models are properly configured.

### Security Test Requirements

- All security fixes must include comprehensive PHPUnit tests.
- Tests must cover both positive (authorized) and negative (unauthorized) scenarios.
- Use `#[Group('security')]` attribute on all security-related tests.
- Test mass assignment protection by attempting to modify unauthorized fields.
- Verify permission checks prevent unauthorized access.

### New Permissions Added

- `appointment-update` - Update appointments
- `appointment-delete` - Delete appointments  
- `task-delete` - Delete tasks
- `task-upload-files` - Upload files to tasks
- `project-delete` - Delete projects
- `project-upload-files` - Upload files to projects
- `lead-delete` - Delete leads
- `user-update` - Update user accounts

### Repaired Security-Related Tests

All tests previously marked with `markTestIncomplete()` have been repaired and now include `#[Group('security')]`:
- TasksControllerTest: can_create_task, can_update_status, can_update_deadline_for_task
- ProjectsControllerTest: can_create_project, can_update_status, can_update_deadline_for_project
- LeadsControllerTest: can_create_lead, can_update_status, can_update_deadline_for_lead
- UsersControllerTest: owner_can_update_user_role
- AppointmentsControllerTest: can_get_appointments_within_time_slot
- DeleteLeadControllerTest: delete_lead, delete_offers_if_flag_given, etc.

### Running Security Tests

```bash
# Run all security tests
vendor/bin/phpunit --group=security

# Run tests for specific controllers
vendor/bin/phpunit --group=search-controller
vendor/bin/phpunit --group=appointment-controller
vendor/bin/phpunit --group=task-controller
# ... etc.
```
