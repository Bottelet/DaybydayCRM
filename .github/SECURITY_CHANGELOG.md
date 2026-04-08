# Changelog

## Security Fixes - Authorization Vulnerabilities (2026-04-08)

### Critical: Document IDOR Vulnerability Fix
**Impact**: Any authenticated user could view/download any document by guessing external_id

**Changes**:
- Added ownership validation to `DocumentsController::view()` method
- Added ownership validation to `DocumentsController::download()` method
- Implemented `canAccessDocument()` helper method that verifies access based on source entity ownership
- Access is granted only if user:
  - Created the source entity (Task/Project/Lead)
  - Is assigned to the source entity (Task/Project/Lead)
  - Owns the associated Client

**Files Modified**:
- `app/Http/Controllers/DocumentsController.php`

**Tests Added**:
- `tests/Unit/Controllers/Document/DocumentsControllerAuthorizationTest.php` (15 tests)
- `tests/Unit/Controllers/Document/DocumentAccessHelperTest.php` (4 tests)
- `database/factories/DocumentFactory.php`

### Medium-High: Missing Permission Checks in Assignment Methods
**Impact**: Any authenticated user could reassign tasks/projects/leads without proper permissions

**Changes**:
- Added `can('can-assign-new-user-to-task')` check to `TasksController::updateAssign()`
- Added `can('can-assign-new-user-to-project')` check to `ProjectsController::updateAssign()`
- Added `can('can-assign-new-user-to-lead')` check to `LeadsController::updateAssign()`

All permission checks align with existing permissions defined in `PermissionsTableSeeder.php` and match the pattern used by sibling `updateStatus()` methods.

**Files Modified**:
- `app/Http/Controllers/TasksController.php`
- `app/Http/Controllers/ProjectsController.php`
- `app/Http/Controllers/LeadsController.php`

**Tests Added**:
- `tests/Unit/Controllers/Task/TaskAssignmentAuthorizationTest.php` (2 tests)
- `tests/Unit/Controllers/Project/ProjectAssignmentAuthorizationTest.php` (2 tests)
- `tests/Unit/Controllers/Lead/LeadAssignmentAuthorizationTest.php` (2 tests)

### Code Quality Improvements
- Used Eloquent `sourceable` morphTo relationship for better code clarity
- Added eager loading to prevent N+1 query issues
- Removed trailing whitespace
- Added comprehensive test coverage (25 new tests total: 15 document authorization + 4 helper + 6 assignment)

### Security Impact
**Before**: 
- Any authenticated user could access ANY document (CWE-639, CWE-862)
- Any authenticated user could reassign ANY task/project/lead

**After**:
- Users can only access documents they own or are related to
- Users need proper permissions to reassign resources
- All changes follow principle of least privilege

### References
- CWE-639: Authorization Bypass Through User-Controlled Key
- CWE-862: Missing Authorization
