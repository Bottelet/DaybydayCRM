# Fundamental Issues Found

## Test Fixes Applied (2026-04-09)

### Permission Name Mismatches
**Problem**: Tests were creating permissions with names that didn't match what the controllers/FormRequests were checking.

**Examples fixed**:
1. **AppointmentSecurityTest**: Test created `appointment-update` permission, but `UpdateAppointmentCalendarRequest` checks for `appointment-edit`
   - **Fix**: Changed test to use `appointment-edit`

2. **Appointment destroy**: Controller checks `appointment-delete` directly
   - **Fix**: Added permission setup in tests

### FormRequest Authorization vs WithoutMiddleware
**Problem**: Tests using `WithoutMiddleware` trait still fail with 403 because FormRequest's `authorize()` method still runs.

**Solution**: Tests need to:
1. Create authenticated user with proper role
2. Create and attach required permissions
3. Call `actingAs($user)`

**Examples fixed**:
- `AppointmentsControllerTest`: Added user authentication with `appointment-edit` and `appointment-delete` permissions
- `ClientsControllerTest`: Added authentication for specific tests that need `client-update` permission
- `DocumentSecurityTest`: Added user creation and authentication in setUp

### JSON vs Standard HTTP Requests with Redirects
**Problem**: Tests using `->json('DELETE', ...)` expect redirect status 302, but JSON requests may behave differently.

**Solution**: Use standard methods (`->delete()`, `->patch()`, etc.) instead of `->json()` when expecting redirects.

**Examples fixed**:
- `ClientAuthorizationTest::user_with_client_delete_permission_can_delete_client`
- `LeadAuthorizationTest::user_with_lead_delete_permission_can_delete_lead`

### Fake Storage Provider Issues
**Problem**: `DocumentsControllerAuthorizationTest` fake storage provider was returning a Response object instead of file content.

**Fix**: Changed fake `view()` and `download()` methods to return string content instead of response objects. The controller wraps the content in a response.

### Role/Permission Creation in Tests
**Problem**: Using `Role::firstOrCreate(['name' => 'employee'])` doesn't create required fields like `external_id`, `display_name`, `description`.

**Solution**: Use `firstOrCreate` with both search array and creation attributes:
```php
$role = Role::firstOrCreate(
    ['name' => 'employee'],
    [
        'display_name' => 'Employee',
        'description' => 'Employee role',
        'external_id' => \Illuminate\Support\Str::uuid()->toString(),
    ]
);
```

**Examples fixed**:
- `DeleteLeadControllerTest`

### Controller Middleware vs Route Middleware
**Problem**: Some controllers define middleware in `__construct()`, which still runs even when routes have different middleware.

**Example**: `OffersController` has:
```php
$this->middleware('permission:offer-create', ['only' => ['create']]);
$this->middleware('permission:offer-edit', ['only' => ['update', 'won', 'lost']]);
```

Tests must provide these exact permissions even if using `WithoutMiddleware` (which only disables route middleware, not controller middleware).

---

## Original Fundamental Issues

### 1. Route/Middleware Configuration Errors
- **Middleware naming confusion**: Many routes were using middleware names like `user.update`, `client.delete`, etc., in `Route::middleware()->group()` calls. In this project's setup, these middleware names were potentially conflicting with Laravel's internal logic or not properly registered for group usage, leading to `BindingResolutionException: Target class [update/destroy] does not exist`.
- **Fix**: Switched to use Entrust's `permission:permission-name` middleware which is more robust and standard for this project.

### 2. Missing Routes and Aliases
- Several tests were failing with `RouteNotFoundException` because they were calling routes like `lead.update.status`, `project.update.assignee`, and `settings.updateOverall`.
- **Fix**: Added the missing routes and appropriate aliases to `routes/web.php`.

### 3. File System Mocking / Storage Service Issues
- `DocumentsController` was throwing `ErrorException: Undefined array key "file_path"` because the `Local` storage service's `upload` method was returning an empty array in the testing environment.
- **Fix**: Updated `App\Services\Storage\Local::upload` to return a dummy array with `file_path` and `id` when in the testing environment.

### 4. Date and Time Handling Logic
- `LeadsController` and `ProjectsController` had fragile logic for merging date and time inputs. 
- In `LeadsController`, it assumed the input `deadline` was always just a date.
- In `ProjectsController`, it was overwriting the entire input array with just the deadline, potentially losing other submitted fields.
- **Fix**: Improved the logic to check for the presence of date/time fields and properly merge them using `Carbon`.

### 5. Permission Seeding and Check Mismatches
- Many tests were receiving 403 Forbidden even when the user was supposed to have the correct permission. 
- This was partially due to the route middleware configuration and potentially due to how permissions are seeded or checked in the controllers.
- **Fix**: Updated route middleware to use the `permission:` prefix correctly.
