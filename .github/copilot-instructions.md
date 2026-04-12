# DaybydayCRM — AI Agent Instructions

## Documentation Overview
Refer to the following core documentation for detailed guidance:
- **[AGENTS.md](../AGENTS.md):** High-level system architecture, modular design, and domain organization.
- **[.github/ARCHITECTURE.md](ARCHITECTURE.md):** Deep dive into technical debt, model behavior (Traits/Observers), and service layer.
- **[.github/TESTING.md](TESTING.md):** Critical test isolation rules, normalization, and common fix patterns.
- **[.github/ROADMAP.md](ROADMAP.md):** Project modernization status and refactoring goals.

---

## Critical Development Guidelines

### 1. Test Isolation (MANDATORY)
Tests must be **self-contained**. The "Cascade Problem" (tests depending on side effects of other tests) is prohibited.
- Create own data via factories.
- Use `RefreshDatabase` or `DatabaseTransactions`.
- Exactly one HTTP request per test (unless testing a workflow sequence).
- Normalize Carbon objects to ISO strings (`toISOString()`) before comparison.

### 2. Business Logic Location
- **Actions:** Encapsulate single-purpose logic in `app/Actions/{Domain}/{ActionName}Action.php`.
- **Services:** Complex workflows belong in `app/Services/`.
- **Controllers:** Must remain thin, delegating to Services/Actions.
- **Threshold:** Controllers exceeding 200 lines are candidates for service extraction.

### 3. Request Validation
- **ALWAYS use FormRequests** for controller input validation.
- **NEVER use** `$request->input()` directly without a FormRequest.
- **NEVER use** inline validation with `$this->validate()`.
- If a FormRequest exists but code uses `$request->input()`, use `$request->validated()` instead.

**Controllers needing FormRequests:**
- `LeadsController`: `store()` method
- `TasksController`: `updateAssign()`, `updateDeadline()`, `updateStatus()`
- `ProjectsController`: `updateAssign()`, `updateDeadline()`, `updateStatus()`
- `RolesController`: `update()` method
- `CommentController`: `store()` method

### 4. Model Consistency
- Use **HasExternalId** trait for UUID routing.
- Use **Blameable** trait for automatic tracking of `user_created_id`.
- Use **Statusable** trait for standardized status handling.
- Use **Observers** for side effects (e.g., file deletion on record delete).

### 5. Enum Usage
- **ALWAYS use enums** for fixed value sets (statuses, roles, permissions).
- **NEVER use string constants** in models for values that should be type-safe.

**Enum Migration Targets:**
- `Task::TASK_STATUS_CLOSED` → `TaskStatus` enum
- `Lead::LEAD_STATUS_CLOSED` → `LeadStatus` enum  
- `Project::PROJECT_STATUS_CLOSED` → `ProjectStatus` enum
- `Role::OWNER_ROLE`, `Role::ADMIN_ROLE` → `RoleType` enum
- `Invoice::STATUS_SENT` → Complete migration to existing `InvoiceStatus` enum

### 6. Service Layer
- **Controllers > 200 lines** → Extract to service.
- **Controllers must remain thin** — business logic belongs in services.

**Controllers Needing Services:**
- `ClientsController` (448 lines) → `ClientService`
- `TasksController` (418 lines) → `TaskService`
- `DocumentsController` (382 lines) → `DocumentStorageService`
- `ProjectsController` (369 lines) → `ProjectService`
- `UsersController` (362 lines) → `UserService`
- `LeadsController` (330 lines) → `LeadService`

### 7. Code Standards
- **Routing:** Prefer tuple-based syntax `[Controller::class, 'method']`.
- **Currency:** Normalize inputs in `prepareForValidation()` of FormRequests.
- **Exceptions:** Throw specific types like `InvalidArgumentException` instead of generic `Exception`.

### 8. Response Handling
- **ALWAYS check** `$request->expectsJson()` for mixed web/API endpoints.
- **JSON requests:** Return JSON with proper status codes (200, 201, 400, 403, 404).
- **Web requests:** Use redirects (302) with flash messages.
- **Never** set flash messages for JSON requests.

**Pattern:**
```php
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}
session()->flash('flash_message', 'Success');
return redirect()->back();
```

### 9. Test Organization
- **HTTP tests** (using `$this->get()`, `$this->post()`) → `tests/Feature/`
- **Unit tests** (testing single classes) → `tests/Unit/`
- **CRITICAL:** All controller tests MUST be in `tests/Feature/Controllers/`

**Currently Misplaced:**
- 39 test files in `tests/Unit/Controllers/` should be in `tests/Feature/Controllers/`

---

## Quick Reference: Common Fixes
- **General error 1364 (Field 'X' doesn't have default):** Ensure `HasExternalId` is used or update factory.
- **Duplicate entry 1062:** Always call `$user->fresh()` after attaching permissions and before `actingAs($user)`.
- **403 Forbidden in tests:** Use `asOwner()` or `asAdmin()` helpers in `TestCase`.
- **VAT/Tax calculation errors:** VAT stored as `percentage × 100` (e.g., 2100 for 21%), requires division by 10000 to get decimal rate.
- **Status validation failures:** Use full class names (`Task::class`) not strings (`'task'`) in `source_type` field.
- **Expected 302 got 200/403:** Check if test uses JSON requests (`$this->json()`) - they return different status codes than web requests.
- **Null pointer in trait methods:** Add null checks before accessing optional properties (e.g., `$this->deadline` in DeadlineTrait).
- **Document view/download failures in tests:** Storage services need to return fake content in testing environment.
- **Missing FormRequest validation:** Create FormRequest for any controller method using `$request->input()` directly.
- **Controller too large (>200 LOC):** Extract business logic to a dedicated service class.
- **Model constants for fixed values:** Convert to type-safe enums instead of string constants.

