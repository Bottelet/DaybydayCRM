# Changelog - Authorization and Security Fixes

## [Unreleased] - 2026-04-08

### Security Fixes

#### SearchController: Arbitrary Class Instantiation Prevention
- **Issue**: URL parameter used directly as class name allowing arbitrary class instantiation.
- **Fix**: Added allowlist validation for search types.
- **Allowed types**: Client, Task, Project, Lead, User.
- **Impact**: CRITICAL - Prevents potential remote code execution.

#### Authorization Enforcement Added

All delete operations across resource types now properly enforce permission checks via middleware:

- **UsersController**: Added `user-delete` permission check to `destroy()` method
- **ClientsController**: Added `client-delete` permission check to `destroy()` method  
- **TasksController**: Added `task-delete` permission check to `destroy()` method
- **LeadsController**: Added `lead-delete` permission checks to both `destroy()` and `destroyJson()` methods
- **ProjectsController**: Added `project-delete` permission check to `destroy()` method
- **OffersController**: Added comprehensive permission checks:
  - `offer-create` for `create()` method
  - `offer-edit` for `update()`, `won()`, and `lost()` methods

#### Settings Access Control

- **SettingsController**: Extended admin-only middleware from `index` to include `updateOverall` and `updateFirstStep` methods, preventing non-admin users from modifying:
  - Company currency and VAT rate
  - Invoice and client numbering schemes
  - Business hours

#### Assignment Permission Checks

- **ProjectsController**: Added `can-assign-new-user-to-project` permission check to `updateAssign()` method
- **TasksController**: Added `task-update-linked-project` permission check to `updateProject()` method

#### File Upload Authorization

- **DocumentsController**: Enabled previously commented-out permission checks:
  - `task-upload-files` permission for `uploadToTask()` method
  - `project-upload-files` permission for `uploadToProject()` method

### Mass Assignment Protection

Fixed mass assignment vulnerabilities in status update endpoints by replacing `fill($request->all())` with explicit field filtering:

- **TasksController::updateStatus**: Now only accepts `status_id` field
- **LeadsController::updateAssign**: Now only accepts `user_assigned_id` field
- **LeadsController::updateStatus**: Now only accepts `status_id` field  
- **ProjectsController::updateStatus**: Now only accepts `status_id` field

This prevents malicious users from modifying unintended fields (title, description, assigned user, etc.) via status update requests.

### Database Schema Updates

Added missing permissions to `PermissionsTableSeeder`:
- `task-delete`: Permission to delete a task
- `lead-delete`: Permission to delete a lead
- `project-delete`: Permission to delete a project

### Code Quality Improvements

- Added null checks when resolving `Status` by external ID to prevent null pointer exceptions
- Improved error handling in status update methods across Tasks, Leads, and Projects controllers

### Testing

Added comprehensive PHPUnit authorization test suites with `#[Group('authorization-fix')]` attribute:

- **TaskAuthorizationTest**: 5 tests covering delete, project update, and mass assignment protection
- **LeadAuthorizationTest**: 4 tests covering delete and mass assignment protection
- **ProjectAuthorizationTest**: 5 tests covering delete, assignment, and mass assignment protection
- **ClientAuthorizationTest**: 2 tests covering delete authorization
- **UserAuthorizationTest**: 3 tests covering delete authorization and owner protection
- **OfferAuthorizationTest**: 8 tests covering create, edit, won/lost, and authorization
- **SettingsAuthorizationTest**: 6 tests covering admin-only access controls
- **DocumentAuthorizationTest**: 4 tests covering file upload permissions

Fixed incomplete tests:
- Removed `markTestIncomplete()` from `UsersControllerTest::owner_can_update_user_role()`
- Removed `markTestIncomplete()` from `PaymentsControllerTest::can_delete_payment()`

### Impact

**Before**: Any authenticated user could delete any resource, modify critical system settings, and exploit mass assignment to change arbitrary model fields.

**After**: All operations enforce proper role-based authorization as defined in the database permissions system.

### Migration Notes

Existing installations should run database seeders to ensure the new permissions (`task-delete`, `lead-delete`, `project-delete`) are created:

```bash
php artisan db:seed --class=PermissionsTableSeeder
```

Administrators should review and assign the new delete permissions to appropriate roles based on their organization's security policies.
