# TODO - DaybydayCRM Refactoring Patterns

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
