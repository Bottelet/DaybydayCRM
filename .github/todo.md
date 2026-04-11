# TODO - DaybydayCRM Refactoring Patterns

## Completed Patterns

### Observer Pattern for Model Side Effects
**Status:** ✅ Implemented  
**Date:** 2026-04-11

- **Pattern:** Use Model Observers to handle side effects when models are created, updated, or deleted
- **Implementation:**
  - Created `DocumentObserver` to automatically delete physical files when Document model is soft-deleted
  - Observer registered in `AppServiceProvider::boot()`
  - File deletion happens in `deleting` event, logged with error handling
- **Benefits:**
  - Decouples file management from controllers
  - Ensures data consistency (file deleted when DB record deleted)
  - Single responsibility - controller handles HTTP, observer handles file system
- **Usage:**
  ```php
  // In Observer
  public function deleting(Document $document)
  {
      $fileSystem = GetStorageProvider::getStorage();
      $fileSystem->delete($document);
  }
  
  // In AppServiceProvider
  Document::observe(DocumentObserver::class);
  ```

### Blameable Trait for Creator Tracking
**Status:** ✅ Implemented  
**Date:** 2026-04-11

- **Pattern:** Automatically track which user created a model instance
- **Implementation:**
  - Created `Blameable` trait in `app/Traits/Blameable.php`
  - Trait uses `creating` event to set `user_created_id` from authenticated user
  - Added `creator` relationship and `user_created_id` to Invoice model
- **Benefits:**
  - Audit trail - always know who created a record
  - No need to manually set creator in controllers
  - Consistent across all models that use the trait
- **Usage:**
  ```php
  // In Model
  use Blameable;
  
  // Relationship
  public function creator()
  {
      return $this->belongsTo(User::class, 'user_created_id');
  }
  
  // Fillable
  protected $fillable = [..., 'user_created_id'];
  ```

### Tax Calculation Service
**Status:** ✅ Fixed  
**Date:** 2026-04-11

- **Pattern:** Centralized tax calculation for invoices and offers
- **Implementation:**
  - Fixed `InvoiceCalculator` to correctly calculate:
    - `getSubTotal()`: Price without VAT (line items sum)
    - `getTotalPrice()`: Price with VAT included (subtotal * multipleVatRate)
    - `getVatTotal()`: Just the VAT amount (subtotal * vatRate)
  - Used by Invoice and Offer models
- **Benefits:**
  - Single source of truth for tax calculations
  - Consistent tax logic across invoices and offers
  - Easy to test and modify VAT rates
- **Usage:**
  ```php
  $calculator = new InvoiceCalculator($invoice);
  $subTotal = $calculator->getSubTotal();     // e.g., 100.00
  $vat = $calculator->getVatTotal();          // e.g., 21.00
  $total = $calculator->getTotalPrice();      // e.g., 121.00
  ```

## Patterns to Implement

### Repository Pattern with findOrFail
**Status:** 🔄 Pending  
**Priority:** High

- **Need:** Standardize data retrieval with consistent error handling
- **Implementation Plan:**
  - Create base `Repository` class or interface
  - Implement `findOrFail()`, `getAll()`, `create()`, `update()`, `delete()` methods
  - Use in InvoiceRepository, DepartmentRepository
  - Return 404 with meaningful messages when records not found
- **Files Affected:**
  - `app/Repositories/BaseRepository.php` (new)
  - `app/Repositories/Invoice/InvoiceRepository.php` (new)
  - `app/Repositories/Department/DepartmentRepository.php` (new)

### Settings Manager Service
**Status:** 🔄 Pending  
**Priority:** High

- **Need:** Centralized settings management with automatic cache invalidation
- **Implementation Plan:**
  - Create `SettingsManager` service
  - Handle get/set operations
  - Auto-clear cache when settings updated
  - Type-cast values (bool, int, string)
  - Support for validation per setting key
- **Files Affected:**
  - `app/Services/Settings/SettingsManager.php` (new)
  - `app/Http/Controllers/SettingsController.php` (update to use service)
  - `app/Models/Setting.php` (potentially add casts)

### Invoice/Project Number Generation
**Status:** 🔄 Pending  
**Priority:** Medium

- **Need:** Standardize sequence number generation with configurable patterns
- **Implementation Plan:**
  - Create `SequenceGenerator` service
  - Support pattern strings like `INV-{YEAR}-{ID}`
  - Handle different sequence types (invoice, project, client)
  - Thread-safe incrementing
  - Allow custom formatting per tenant
- **Files Affected:**
  - `app/Services/Sequence/SequenceGenerator.php` (new)
  - `app/Services/InvoiceNumber/InvoiceNumberService.php` (refactor to use SequenceGenerator)

### State Machine for Project Status
**Status:** 🔄 Pending  
**Priority:** Medium

- **Need:** Enforce valid status transitions for projects
- **Implementation Plan:**
  - Implement state machine for Project model
  - Define allowed transitions (e.g., draft -> active, active -> closed)
  - Log state changes
  - Prevent invalid transitions
  - Consider using package like `spatie/laravel-model-states`
- **Files Affected:**
  - `app/States/ProjectState.php` (new)
  - `app/Models/Project.php` (add state machine)
  - `app/Http/Controllers/ProjectsController.php` (use state transitions)

### Search Infrastructure
**Status:** 🔄 Pending  
**Priority:** Low (Elasticsearch already configured)

- **Note:** Search is already using Elasticsearch but disabled in testing
- **Issue:** Tests need proper mocking/faking of search results
- **Recommendation:** Tests should create actual models and query database, not Elasticsearch
- **Alternative:** Use database queries with `LIKE` for testing, Elasticsearch for production

## Model Relationship Conventions

All models should define relationships explicitly:

1. **Creator Tracking:** Use `Blameable` trait + `creator()` relationship
2. **Polymorphic Relations:** Always define inverse relationships
3. **Foreign Keys:** Use database constraints for referential integrity
4. **Factory Support:** Ensure factories can create related models

## Test Writing Conventions

1. **Isolation:** Every test creates its own data
2. **Notifications:** Always use `Notification::fake()` pattern
3. **Storage:** Always use `Storage::fake()` pattern
4. **Dates:** Normalize to same format before assertions (e.g., `->toISOString()`)
5. **No side effects:** One HTTP request per test (except workflow tests)

## Documentation References

- **Full refactor plan:** `.github/refactor_plan.md`
- **Test isolation:** `.github/test_isolation_refactor.md`
- **Error repair:** `.github/error_repair_plan.md`
- **Agent instructions:** `.github/copilot-instructions.md`
- **Architecture:** `AGENTS.md`
This document tracks recurring patterns discovered during test failure analysis and refactoring efforts.

## Completed Patterns (2026-04-11)

### 1. Exception Type Standardization
**Pattern:** All custom enum classes now throw `\InvalidArgumentException` instead of generic `Exception`
- **Files Updated:** `AbsenceReason`, `InvoiceStatus`, `PaymentSource`
- **Reason:** Better error handling and more specific exception types
- **Tests Updated:** All enum tests now expect `InvalidArgumentException`

### 2. Action Classes for Business Logic
**Pattern:** Extract business logic from controllers into dedicated Action classes
- **Location:** `app/Actions/{Domain}/`
- **Example:** `StoreAbsenceAction` - decouples absence creation from HTTP layer
- **Benefits:**
  - Testable without HTTP layer
  - Reusable across controllers, console commands, jobs
  - Single Responsibility Principle
  - Easier to mock and unit test

### 3. Currency Input Normalization
**Pattern:** Use `prepareForValidation()` in FormRequests to handle localized number formats
- **Implementation:** `PaymentRequest::prepareForValidation()`
- **Details:**
  - Converts comma decimal separators to dots
  - Removes spaces from currency strings
  - Allows both "5000.23" and "5000,23" formats
- **Validation:** Changed from regex to `numeric` rule after normalization

### 4. Soft Delete Testing
**Pattern:** Use `assertSoftDeleted()` for models with SoftDeletes trait
- **Anti-pattern:** Using `assertNull()` which checks for hard deletes
- **Files Updated:** `ClientsControllerTest`
- **Reason:** Properly validates soft deletion behavior

## Patterns to Implement

### 5. Native PHP Enums (High Priority)
**Goal:** Replace custom enum classes with native PHP 8.1+ enums
- **Current:** Custom enum classes with static factory methods
- **Target:** Native backed enums
- **Benefits:**
  - Type safety
  - Auto-completion in IDEs
  - Native `from()` and `tryFrom()` methods
  - Pattern matching with `match` expressions
- **Files to Convert:**
  - [ ] `InvoiceStatus` -> `InvoiceStatusEnum`
  - [ ] `AbsenceReason` -> `AbsenceReasonEnum`
  - [ ] `PaymentSource` -> `PaymentSourceEnum`
  - [ ] Create `TaskStatusEnum` (currently uses Status model)
  - [ ] Create `LeadStatusEnum` (currently uses Status model)

### 6. Domain Events & Listeners
**Goal:** Decouple side effects from controllers using Laravel events
- **Examples:**
  - [ ] `PaymentCreated` event -> `UpdateInvoiceStatus` listener
  - [ ] `LeadStatusChanged` event -> `LogStatusHistory` listener
  - [ ] `AbsenceCreated` event -> `NotifyManagers` listener
- **Benefits:**
  - Decoupled logic
  - Easy to add new side effects without modifying controllers
  - Better testability with event fakes

### 7. Model Observers
**Goal:** Automate model lifecycle events
- **Examples:**
  - [ ] `LeadObserver` -> automatically log status changes to history table
  - [ ] `PaymentObserver` -> update invoice status on payment creation
  - [ ] `AbsenceObserver` -> audit trail for absence management
- **Benefits:**
  - Consistent behavior regardless of entry point (controller, console, API)
  - Single source of truth for model events
  - Easier to test and maintain

### 8. API Resources
**Goal:** Standardize JSON responses across all API endpoints
- **Examples:**
  - [ ] `CalendarResource` for UsersControllerCalendar
  - [ ] `InvoiceResource` with nested lines
  - [ ] `ClientResource` with relationships
- **Benefits:**
  - Consistent API responses
  - Easier to version APIs
  - Type-safe transformations

### 9. Money Value Objects
**Goal:** Use `brick/money` or similar for all currency operations
- **Current:** Manual multiplication by 100, string parsing
- **Target:** Money objects with proper currency handling
- **Benefits:**
  - Accurate decimal arithmetic
  - Currency-aware operations
  - Prevents rounding errors
  - International currency support

### 10. Foreign Key Constraints
**Goal:** Add database-level integrity constraints
- **Examples:**
  - [ ] Lead -> Status (ensure valid status_id)
  - [ ] Lead -> User (ensure valid user_assigned_id, user_created_id)
  - [ ] Task -> Status (ensure valid status_id)
- **Benefits:**
  - Data integrity at database level
  - Prevents orphaned records
  - Clearer data relationships

## Code Quality Improvements

### Relationship Methods
- **Lead Model:**
  - [x] Add `notes()` relationship (alias for comments)
  - [ ] Ensure all polymorphic relationships use proper factory setup

### Test Isolation
- [ ] Audit all controller tests for unrelated HTTP requests
- [ ] Remove side-effect setup (e.g., GET /client/create in payment tests)
- [ ] Split multi-request tests into single-purpose tests
- [ ] Ensure all tests use RefreshDatabase or DatabaseTransactions

### Documentation
- [x] Update `.github/copilot-instructions.md` with new patterns
- [ ] Document Action class pattern in AGENTS.md
- [ ] Add currency normalization pattern to TEST_GUIDE.md

## Migration Strategy

### Phase 1: Low-Risk Improvements (Completed)
- [x] Exception types in enums
- [x] Action classes for new features
- [x] Currency normalization in FormRequests
- [x] Soft delete assertions in tests

### Phase 2: Medium-Risk Refactors (In Progress)
- [ ] Add Model Observers for common patterns
- [ ] Implement Domain Events for decoupling
- [ ] Add API Resources for existing endpoints

### Phase 3: High-Risk Changes (Planned)
- [ ] Convert to native PHP enums (requires thorough testing)
- [ ] Add foreign key constraints (requires data cleanup)
- [ ] Implement Money value objects (requires widespread changes)

## Notes

- Always run full test suite after pattern implementation
- Update factories when adding new required fields
- Document breaking changes in CHANGELOG.md
- Use feature flags for gradual rollout of major refactors
