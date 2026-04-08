# URL Generation Tests for Subdirectory Installations

This directory contains comprehensive tests for verifying that URL generation works correctly when DaybydayCRM is installed in a subdirectory.

## Test Suites

### 1. SubdirectoryUrlGenerationTest.php
**Purpose**: Tests URL generation in blade templates for subdirectory installations.

**Test Coverage**:
- URL helper generates correct absolute URLs with subdirectory paths
- URL helper works correctly at domain root
- Task/Project/Lead/Product pages contain correct URLs for:
  - Document upload modals
  - Client creation redirects
  - Product creator modals
  - User deletion AJAX calls
- Master layout contains correct DayByDay.baseUrl configuration
- JS assets (manifest.js, vendor.js) load with correct paths
- Calendar page assets and configuration
- Tests both HTTP and HTTPS with various port configurations

**Edge Cases Tested**:
- Subdirectory installations (e.g., `/daybydaycrm/public`)
- Root installations (e.g., `/`)
- HTTPS with subdirectories
- Custom ports with subdirectories (e.g., `:8080/crm`)
- Asset loading in both master layout and calendar view

### 2. UrlGenerationEdgeCasesTest.php
**Purpose**: Tests edge cases and unusual URL configurations.

**Test Coverage**:
- Multiple subdirectory levels (e.g., `/projects/crm/public`)
- Trailing slashes in configuration
- Empty paths
- Paths without leading slashes
- IPv4 addresses
- Non-standard ports
- Special characters in external IDs
- Subdomains with and without paths
- Query parameters and URL fragments
- Deeply nested paths
- URL concatenation with variables

### 3. DocumentsControllerUploadModalTest.php
**Purpose**: Unit tests for the DocumentsController upload modal functionality.

**Test Coverage**:
- Modal view is returned correctly for task, client, and project types
- Correct type is passed to the view
- Route mapping contains correct task→tasks, client→clients, project→projects mappings
- Base URL is correctly set in both subdirectory and root installations
- Fail-fast error handling exists for invalid types
- No fallback exists that could mask invalid type errors

## Running the Tests

### Run all URL tests
```bash
php artisan test --testsuite=Feature --filter=Url
```

### Run specific test suite
```bash
php artisan test tests/Feature/Url/SubdirectoryUrlGenerationTest.php
php artisan test tests/Feature/Url/UrlGenerationEdgeCasesTest.php
php artisan test tests/Unit/Controllers/Document/DocumentsControllerUploadModalTest.php
```

### Run a specific test
```bash
php artisan test --filter=url_helper_generates_absolute_urls_with_subdirectory
```

## What These Tests Validate

1. **Laravel's url() Helper**: Confirms that using `url()` instead of hardcoded paths generates correct absolute URLs based on `app.url` configuration.

2. **Blade Template Output**: Verifies that blade templates render URLs correctly when the application is configured for subdirectory installation.

3. **JavaScript Configuration**: Ensures that the DayByDay.baseUrl is correctly set in the master layout for axios to use.

4. **Route Mapping**: Validates that the explicit route mapping in `_uploadFileModal.blade.php` correctly maps entity types to their plural route names.

5. **Error Handling**: Confirms that invalid type parameters fail fast rather than silently using a fallback.

## Test Scenarios

### Subdirectory Installation Examples
- `http://localhost/daybydaycrm/public`
- `http://example.com/crm/public`
- `https://example.com:8443/crm`
- `http://192.168.1.1/crm`

### Root Installation Examples
- `http://localhost`
- `http://example.com`
- `https://crm.example.com`

## Expected Behavior

When `app.url` is set to `http://localhost/daybydaycrm/public`:
- `url('/tasks')` returns `http://localhost/daybydaycrm/public/tasks`
- Blade templates contain `http://localhost/daybydaycrm/public/...` URLs
- JavaScript `DayByDay.baseUrl` is set to `http://localhost/daybydaycrm/public`
- Axios requests automatically use the base URL

When `app.url` is set to `http://localhost`:
- `url('/tasks')` returns `http://localhost/tasks`
- All URLs work as before (backward compatible)

## Related Files

The tests validate changes made to these files:
- `resources/views/tasks/show.blade.php`
- `resources/views/projects/show.blade.php`
- `resources/views/products/index.blade.php`
- `resources/views/tasks/create.blade.php`
- `resources/views/projects/create.blade.php`
- `resources/views/leads/create.blade.php`
- `resources/views/users/index.blade.php`
- `resources/views/documents/_uploadFileModal.blade.php`
- `resources/views/layouts/master.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/assets/js/bootstrap.js`
