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

## Security & Authorization (Added 2026-04-08)

### Document Access Control
- **CRITICAL FIX:** Document view/download now requires ownership validation
- The `DocumentsController::canAccessDocument()` method validates access based on:
    - Task ownership: user created/assigned to task, or owns client
    - Project ownership: user created/assigned to project, or owns client
    - Lead ownership: user created/assigned to lead, or owns client
    - Client ownership: user assigned to client
- Use `sourceable` morphTo relationship for accessing polymorphic source entities
- Eager load with `Document::with('sourceable')` to prevent N+1 queries

### Assignment Permission Checks
- **TasksController::updateAssign()**: Requires `can('can-assign-new-user-to-task')`
- **ProjectsController::updateAssign()**: Requires `can('can-assign-new-user-to-project')`
- **LeadsController::updateAssign()**: Requires `can('can-assign-new-user-to-lead')`
- Pattern matches existing `updateStatus()` methods
- All permissions defined in `PermissionsTableSeeder.php`

### Security Test Groups
- Use `#[Group('security')]` for security-related tests
- Use `#[Group('document_authorization')]` for document access tests
- Use `#[Group('assignment_authorization')]` for assignment permission tests
- Test both authorized and unauthorized scenarios
- Verify proper flash messages and redirects on denial
