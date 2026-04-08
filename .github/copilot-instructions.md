# DaybydayCRM AI Agent Instructions

## Core Principles & Documentation
- **Refer to .junie/*.md:** For detailed structural analysis, fundamental architectural problems, error repair plans, and refactoring strategies, always consult the files in the `.junie/` directory.
- **Detailed Instructions:**
  - `.junie/error_repair_plan.md`: Common test failures and their specific fixes.
  - `.junie/refactor_plan.md`: Roadmap for modernizing the codebase.
  - `.junie/structural_analysis.md`: Analysis of current code/test suite weaknesses.
  - `.junie/fundamental_analysis.md`: Deep architectural issues and technical debt.

## Testing & Database Guidelines

### Common Test Issues & Solutions

- **Missing Default Values (SQLSTATE[HY000]: 1364 Field 'X' doesn't have a default value):**
  - **Activity Model:** Always ensure `ip_address` and `external_id` (UUID) are set. The `Activity` model has a `boot()` method that handles this automatically if these fields are missing.
  - **Factories:** When creating models in tests, ensure all NOT NULL fields without defaults are provided or handled in the factory/boot method.

- **Unique Constraint Violations (SQLSTATE[23000]: 1062 Duplicate entry):**
  - **Roles/Permissions:** Use `attachRole()` or `attachPermission()` from `EntrustUserTrait`. This trait has been modified to check for existing associations before attaching to prevent duplicate entry errors in the `role_user` table.

- **PHPUnit 10+ Compatibility:**
  - Avoid `assertObjectHasAttribute`. Use `assertTrue(property_exists($object, 'attribute'))` instead.
  - Use `#[Test]` and `#[Group('...')]` attributes instead of `@test` and `@group` annotations.

- **Date Comparisons:**
  - Prefer `toDateString()` over `toDate()` or other methods that might return objects when strings are expected, especially when comparing with database values.

### Model Boot Methods

- Many models in this project use UUIDs for `external_id`. Ensure any new models follow this pattern using a `boot()` method or a reusable trait to generate UUIDs on creation.

## Role & Permission Management

- The project uses a custom implementation of Entrust (`app/Zizaco/Entrust/`).
- Use `owner` or `administrator` roles in tests when high-level permissions are required.
- Always check if a user has a role before attaching it if not using the modified `attachRole()` method.

## Authorization & Security Patterns (Added 2026-04-08)

### Controller Authorization

- **Always enforce permissions via middleware** in controller constructors:
  ```php
  public function __construct()
  {
      $this->middleware('can:resource-delete', ['only' => ['destroy']]);
      $this->middleware('can:resource-create', ['only' => ['create', 'store']]);
  }
  ```

- **Use Laravel's built-in `can` middleware** for permission checks rather than custom middleware when possible.

- **Admin-only operations** should use `user.is.admin` middleware:
  ```php
  $this->middleware('user.is.admin', ['only' => ['sensitiveMethod']]);
  ```

### Mass Assignment Protection

- **Never use `fill($request->all())`** in update operations. Always explicitly filter fields:
  ```php
  // BAD
  $model->fill($request->all())->save();
  
  // GOOD
  $model->fill($request->only(['status_id']))->save();
  ```

- **Use FormRequests** for complex validation and authorization, but still filter fields in controllers for status/assignment updates.

### Testing Authorization

- **Create dedicated authorization test classes** with `#[Group('authorization-fix')]` attribute
- **Test both positive and negative cases:**
  - User with permission CAN perform action
  - User without permission CANNOT perform action
- **Verify actual operations**, not just HTTP status codes:
  ```php
  // GOOD
  $response->assertStatus(403);
  $this->assertDatabaseHas('table', ['id' => $id, 'deleted_at' => null]);
  
  // NOT ENOUGH
  $response->assertStatus(403);
  ```

- **Test mass assignment protection** by attempting to modify protected fields and verifying they remain unchanged.

### Permission Seeder Pattern

- All permissions must be defined in `database/seeders/PermissionsTableSeeder.php`
- Permission naming convention: `resource-action` (e.g., `task-delete`, `client-update`)
- Group permissions by resource type using the `grouping` field
- Always include `external_id` for permissions (auto-generated if not provided)

