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

## Security Guidelines (Added 2026-04-08)

### Critical Security Requirements

1. **Class Instantiation Protection**
   - Never use user input directly as class names or in dynamic instantiation
   - Always validate against an allowlist of permitted classes
   - Return 400 errors for invalid class types
   - Example: SearchController validates search types before instantiation

2. **Permission & Authorization Checks**
   - **Controller-level:** Use middleware for entire controller or specific methods
     ```php
     $this->middleware('permission.name', ['only' => ['method']]);
     ```
   - **Method-level:** Add inline permission checks for granular control
     ```php
     if (! auth()->user()->can('permission-name')) {
         session()->flash('flash_message_warning', __('Permission denied'));
         return redirect()->back();
     }
     ```
   - **Required for:** All delete operations, settings modifications, user management, file uploads

3. **Mass Assignment Protection**
   - **Never use:** `$request->all()` with `fill()` or `update()`
   - **Always use:** `$request->only(['field1', 'field2'])` with explicit allowlists
   - Verify model `$fillable` arrays contain only safe fields
   - Example fixes: TasksController, ProjectsController, LeadsController all use `only()`

4. **Input Validation**
   - Use Laravel Form Requests for complex validation
   - Validate all user input before use
   - Use `$request->has()` instead of `isset()` for request parameter checks
   - Add null safety checks for database lookups (e.g., Status::whereExternalId()->first())

5. **Error Handling**
   - Return JSON errors for AJAX requests (not redirects)
   - Use internationalization (`__()` helper) for all error messages
   - Check resource existence before permission validation

### Security Testing Standards

- **Test Coverage:** All security fixes must include comprehensive tests
- **Test Structure:**
  - Positive tests: Verify authorized users can perform actions
  - Negative tests: Verify unauthorized users are blocked
  - Mass assignment tests: Verify only allowed fields are modified
  - Edge cases: Invalid IDs, missing resources, permission boundaries

- **Test Attributes:**
  - Use `#[Group('security')]` on all security-related test classes
  - Use specific groups: `#[Group('search-controller')]`, `#[Group('task-controller')]`, etc.
  - Tests must NOT use `markTestIncomplete()` when complete

- **Test Quality:**
  - Don't just assert `->ok()` - test the actual functionality
  - Verify database state after operations
  - Test both successful and failed scenarios
  - Check session messages and response codes

### New Security Permissions

When implementing new features, consider these permission patterns:
- `{resource}-view` - View resource
- `{resource}-create` - Create resource
- `{resource}-update` - Update resource
- `{resource}-delete` - Delete resource
- `{resource}-upload-files` - Upload files to resource

### Security Test Files

New security test files added:
- `tests/Unit/Controllers/Search/SearchControllerSecurityTest.php`
- `tests/Unit/Controllers/Appointment/AppointmentSecurityTest.php`
- `tests/Unit/Controllers/Task/TaskSecurityTest.php`
- `tests/Unit/Controllers/Project/ProjectSecurityTest.php`
- `tests/Unit/Controllers/Lead/LeadSecurityTest.php`
- `tests/Unit/Controllers/Settings/SettingsSecurityTest.php`
- `tests/Unit/Controllers/User/UserSecurityTest.php`
- `tests/Unit/Controllers/Document/DocumentSecurityTest.php`

### Changelog

All security changes are documented in `.github/CHANGELOG_SECURITY_FIXES.md`

