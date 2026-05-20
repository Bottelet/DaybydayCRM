# Security Fixes Changelog

## [Unreleased] - 2026-04-08

### Critical Security Fixes

#### 1. Arbitrary Class Instantiation Prevention - SearchController
**Issue**: URL parameter used directly as class name allowing arbitrary class instantiation
**Fix**: Added allowlist validation for search types
- Only allows known model types: Client, Task, Project, Lead, User (singular and plural forms)
- Returns 400 error for invalid types
- Prevents potential remote code execution via arbitrary class instantiation
**Impact**: CRITICAL - Prevents remote code execution
**Files Changed**: `app/Http/Controllers/SearchController.php`
**Tests Added**: `tests/Unit/Controllers/Search/SearchControllerSecurityTest.php`

#### 2. Missing Authorization Checks - AppointmentsController
**Issue**: Update endpoint had no permission validation
**Fix**: Added `appointment-update` permission check to the update endpoint
**Impact**: CRITICAL - Prevents unauthorized appointment modifications
**Files Changed**: `app/Http/Controllers/AppointmentsController.php`
**Tests Added**: `tests/Unit/Controllers/Appointment/AppointmentSecurityTest.php`

### High Severity Security Fixes

#### 3. Missing Admin Checks - SettingsController
**Issue**: Critical settings endpoints (`updateOverall`, `updateFirstStep`) accessible to non-admin users
**Fix**: Extended admin middleware to cover all settings modification methods
**Impact**: HIGH - Prevents non-admin users from modifying company settings, VAT, currency, etc.
**Files Changed**: `app/Http/Controllers/SettingsController.php`
**Tests Added**: `tests/Unit/Controllers/Settings/SettingsSecurityTest.php`

#### 4. Missing Permission Checks - UsersController
**Issue**: Any authenticated user could edit any other user's data including passwords
**Fix**: Added `user.update` middleware to edit and update methods
**Impact**: HIGH - Ensures only authorized users can modify user accounts
**Files Changed**: `app/Http/Controllers/UsersController.php`
**Tests Added**: `tests/Unit/Controllers/User/UserSecurityTest.php`

#### 5. Mass Assignment Vulnerabilities
**Issue**: Using `fill($request->all())` allows attackers to modify unintended fields
**Fix**: Replaced with `fill($request->only([...]))` with explicit field allowlists
**Impact**: HIGH - Prevents mass assignment attacks (CWE-915)
**Files Changed**: 
- `app/Http/Controllers/TasksController.php` - Only allows `status_id` in updateStatus
- `app/Http/Controllers/ProjectsController.php` - Only allows `status_id` in updateStatus
- `app/Http/Controllers/LeadsController.php` - Only allows `user_assigned_id` in updateAssign, `status_id` in updateStatus
**Tests Added**: 
- `tests/Unit/Controllers/Task/TaskSecurityTest.php`
- `tests/Unit/Controllers/Project/ProjectSecurityTest.php`
- `tests/Unit/Controllers/Lead/LeadSecurityTest.php`

#### 6. Missing Permission Checks on Destroy Endpoints
**Issue**: No authorization checks on delete operations
**Fix**: Added permission checks to destroy methods
**Impact**: HIGH - Ensures only authorized users can delete resources
**Files Changed**:
- `app/Http/Controllers/TasksController.php` - Added `task-delete` permission check
- `app/Http/Controllers/ProjectsController.php` - Added `project-delete` permission check
- `app/Http/Controllers/LeadsController.php` - Added `lead-delete` permission check (both destroy and destroyJson)
**Tests Added**: Permission tests in security test files

#### 7. Commented Out Permission Checks - DocumentsController
**Issue**: Permission checks were commented out, allowing unauthorized file uploads
**Fix**: Uncommented and enabled permission checks
**Impact**: HIGH - Prevents unauthorized file uploads
**Files Changed**: `app/Http/Controllers/DocumentsController.php`
**Permissions Required**:
- `task-upload-files` for uploadToTask
- `project-upload-files` for uploadToProject
**Tests Added**: `tests/Unit/Controllers/Document/DocumentSecurityTest.php`

### Additional Improvements

#### 8. Null Safety and Error Handling
**Changes**:
- Added null checks for Status lookups to prevent null pointer exceptions
- Fixed AJAX error handling to return proper JSON responses instead of redirects
- Added proper resource existence checks before permission validation
- Used `$request->has()` instead of `isset()` for reliable request parameter validation

#### 9. Internationalization
**Changes**:
- Added translation helpers to all error messages
- Ensured consistent error message formatting across controllers

## Testing

All security fixes include comprehensive PHPUnit tests:
- **11 new test files** added with complete coverage of security fixes
- **6 existing test files** repaired (removed `markTestIncomplete()`)
- All tests tagged with `#[Group('security')]` for easy identification
- Tests cover both positive (authorized access) and negative (unauthorized access blocked) scenarios
- Mass assignment protection tests verify only allowed fields are modified

### Running Security Tests

```bash
# Run all security tests
vendor/bin/phpunit --group=security

# Run specific controller security tests
vendor/bin/phpunit --group=search-controller
vendor/bin/phpunit --group=appointment-controller
vendor/bin/phpunit --group=task-controller
vendor/bin/phpunit --group=project-controller
vendor/bin/phpunit --group=lead-controller
vendor/bin/phpunit --group=settings-controller
vendor/bin/phpunit --group=user-controller
vendor/bin/phpunit --group=document-controller
```

## Permissions

New permissions required for proper authorization:
- `appointment-update` - Update appointments
- `appointment-delete` - Delete appointments
- `task-delete` - Delete tasks
- `task-upload-files` - Upload files to tasks
- `project-delete` - Delete projects
- `project-upload-files` - Upload files to projects
- `lead-delete` - Delete leads
- `user-update` - Update user accounts

## Migration Notes

These fixes are backward compatible. Existing functionality is preserved while adding proper security controls. No database migrations are required, but permissions should be assigned to appropriate roles.

## References

- CWE-915: Improperly Controlled Modification of Dynamically-Determined Object Attributes
- OWASP A01:2021 - Broken Access Control
- OWASP A03:2021 - Injection
