# TODO and Implemented Patterns

## ✅ Completed Refactoring Patterns

### 1. Blameable Trait (Creator/Updater Tracking)
**Location:** `app/Traits/Blameable.php`

**Purpose:** Automatically track who created and last updated records.

**Features:**
- Automatically populates `user_created_id` on model creation
- Automatically updates `user_updated_id` on model updates
- Provides `creator()` and `updater()` relationships
- Uses Laravel model events (no controller changes needed)

**Usage:**
```php
use App\Traits\Blameable;

class MyModel extends Model
{
    use Blameable;
    
    protected $fillable = [..., 'user_created_id', 'user_updated_id'];
}
```

**Database Requirements:**
```php
$table->integer('user_created_id')->unsigned()->nullable();
$table->foreign('user_created_id')->references('id')->on('users');
$table->integer('user_updated_id')->unsigned()->nullable();
$table->foreign('user_updated_id')->references('id')->on('users');
```

**Models Using Blameable:**
- Ready for: Task, Lead, Project, Invoice, Offer (add to these models next)

---

### 2. Statusable Trait (Consistent Status Handling)
**Location:** `app/Traits/Statusable.php`

**Purpose:** Provide consistent status relationship and helper methods across models.

**Features:**
- Provides `status()` belongsTo relationship
- `hasStatus(string $statusTitle)` - check if model has specific status
- `setStatus(string $statusTitle)` - set status by title
- `withStatus(string $statusTitle)` - query scope for filtering
- `withoutStatus(string $statusTitle)` - query scope for exclusion

**Usage:**
```php
use App\Traits\Statusable;

class MyModel extends Model
{
    use Statusable;
    
    protected $fillable = [..., 'status_id'];
}

// In code:
if ($model->hasStatus('Closed')) { ... }
$model->setStatus('In Progress');
$models = Model::withStatus('Open')->get();
```

**Database Requirements:**
```php
$table->integer('status_id')->unsigned();
$table->foreign('status_id')->references('id')->on('statuses');
```

**Models Using Statusable:**
- Ready for: Task, Lead, Project (replace individual status() methods)
- Needs migration: Offer (add status_id column - migration created)

---

### 3. Missing Model Relationships Added

#### Lead Model
- ✅ Added `documents()` - morphMany relationship
- ✅ Added `projects()` - hasMany relationship
- ✅ Migration created for projects.lead_id

#### Offer Model
- ✅ Added `source()` - morphTo relationship (for polymorphic lead association)
- ✅ Added `lead()` - alias for source()
- ✅ Added `status()` - belongsTo Status relationship
- ✅ Added `lines()` - alias for invoiceLines()
- ✅ Added `status_id` to fillable
- ✅ Migration created for offers.status_id

#### User Model
- ✅ Added `integrations()` - hasMany relationship
- ✅ Added `settings()` - hasMany relationship
- Note: `roles()` already provided by EntrustUserTrait

#### Project Model
- ✅ Added `lead()` - belongsTo relationship
- ✅ Added `lead_id` to fillable
- ✅ Migration created for projects.lead_id

---

## 🔄 In Progress / Next Steps

### 4. Repository Pattern Improvements
**Status:** Not yet implemented (repositories don't exist for most models)

**TODOs:**
- [ ] Create `app/Repositories/Client/ClientRepository.php`
- [ ] Create `app/Repositories/Lead/LeadRepository.php`
- [ ] Create `app/Repositories/Task/TaskRepository.php`
- [ ] Create `app/Repositories/User/UserRepository.php`

**Pattern to Follow:**
- Use `findOrFail()` instead of `find()` - throw exceptions, don't return null
- Provide `withoutGlobalScopes()` option for filtering control
- Implement Criteria classes for complex queries
- Always return refreshed models after create/update
- Use model `fill()` and `isDirty()` to track changes

**Example Structure:**
```php
interface ClientRepositoryInterface
{
    public function all(array $criteria = [], bool $withoutGlobalScopes = false);
    public function find(int $id): Client;
    public function create(array $data): Client;
    public function update(Client $client, array $data): Client;
}
```

---

### 5. Service Layer Improvements
**Status:** Not yet implemented

**TODOs:**
- [ ] Create `app/Actions/Client/StoreClientAction.php`
- [ ] Create `app/Actions/Lead/TransitionLeadStatusAction.php`
- [ ] Create `app/Actions/Task/AssignTaskAction.php`
- [ ] Create `app/Services/Payment/PaymentProcessor.php`

**Patterns:**
- Throw custom Domain Exceptions (don't return false/null)
- Use Single Action classes for discrete operations
- Log all service-level failures
- Use database transactions where needed
- Validate business rules before persisting

**Example:**
```php
class StoreClientAction
{
    public function execute(array $data): Client
    {
        // Validate business rules
        if (/* rule violated */) {
            throw new ClientCreationException('Reason...');
        }
        
        // Create with transaction
        return DB::transaction(function () use ($data) {
            $client = Client::create($data);
            // ... additional logic
            return $client->fresh();
        });
    }
}
```

---

### 6. Status Enum Migration (GLOBAL REFACTOR)
**Status:** Planned - High Priority

**Current Problem:** 
- Some models use Status model relationship (Lead, Task, Project)
- Some models use string status field (Offer uses OfferStatus enum class)
- Invoice uses InvoiceStatus enum class
- Inconsistent across codebase

**Solution:**
- [ ] Migrate all status handling to native PHP 8.1+ Enums
- [ ] Create `app/Enums/LeadStatus.php` (native enum)
- [ ] Create `app/Enums/TaskStatus.php` (native enum)
- [ ] Update existing `app/Enums/OfferStatus.php` to native enum
- [ ] Update existing `app/Enums/InvoiceStatus.php` to native enum
- [ ] Add enum casting to models: `protected $casts = ['status' => LeadStatus::class];`
- [ ] Implement State Machine for status transitions (use spatie/laravel-model-states or similar)

**Benefits:**
- Type safety (IDE autocomplete)
- Validation at the language level
- Methods on enum cases
- Easier refactoring

**Example Migration:**
```php
// Before: String or Status model
$lead->status_id = Status::where('title', 'Closed')->first()->id;

// After: Native enum
use App\Enums\LeadStatus;

protected $casts = ['status' => LeadStatus::class];

$lead->status = LeadStatus::Closed;
$lead->save();

// Enum definition:
enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Closed = 'closed';
    
    public function canTransitionTo(LeadStatus $newStatus): bool { ... }
}
```

---

### 7. Money Value Object (GLOBAL REFACTOR)
**Status:** Planned - High Priority

**Current Problem:**
- Floating-point precision errors in payment calculations
- No multi-currency support
- Balance comparisons fail due to precision

**Solution:**
- [ ] Already have `app/Repositories/Money/` directory - check if implemented
- [ ] If not, add `brick/money` package
- [ ] Create Money Value Object wrapper
- [ ] Update PaymentService to use Money object
- [ ] Update InvoiceCalculator to use Money object
- [ ] Store amounts as integers (cents) in database
- [ ] Add migrations to convert decimal columns to integer cents

**Example:**
```php
use Brick\Money\Money;

// Instead of:
$total = $invoice->amount - $payment->amount; // Floating-point issues!

// Use:
$total = Money::of($invoice->amount, 'USD')
    ->minus(Money::of($payment->amount, 'USD'));
    
if ($total->isZero()) {
    $invoice->status = InvoiceStatus::Paid;
}
```

---

### 8. File/Document Management Improvements
**Status:** Planned

**TODOs:**
- [ ] Create `app/Observers/DocumentObserver.php`
- [ ] Implement file lifecycle management (auto-delete on model delete)
- [ ] Add `Storage::exists()` checks before file operations
- [ ] Update DocumentsController to return 404 instead of 500 on missing file
- [ ] Use `Storage::fake()` in all document tests

**Observer Example:**
```php
class DocumentObserver
{
    public function deleted(Document $document)
    {
        if ($document->file_path && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }
    }
}
```

---

### 9. Model Factories Enhancement
**Status:** Planned

**TODOs:**
- [ ] Update factories to support relationship states
- [ ] Add `hasClients()`, `hasLeads()`, `hasTasks()` to UserFactory
- [ ] Add `hasAppointments()`, `hasOffers()`, `hasDocuments()`, `hasProjects()` to LeadFactory
- [ ] Add `withLineItems()` to OfferFactory and InvoiceFactory
- [ ] Add `hasPayments()` to InvoiceFactory
- [ ] Ensure all factories set required foreign keys

**Example:**
```php
// UserFactory
public function hasClients(int $count = 1)
{
    return $this->has(Client::factory()->count($count));
}

// Usage in tests:
$user = User::factory()->hasClients(3)->hasLeads(5)->create();
```

---

### 10. Database Constraints
**Status:** Partially implemented

**Completed:**
- ✅ Migration for projects.lead_id with foreign key
- ✅ Migration for offers.status_id with foreign key

**TODOs:**
- [ ] Add non-nullable constraint to invoices.client_id
- [ ] Add non-nullable constraint to offers.client_id (if not already)
- [ ] Add non-nullable constraint to tasks.status_id
- [ ] Add non-nullable constraint to leads.status_id
- [ ] Add defaults for required fields (e.g., status_id defaults to "New" status)
- [ ] Review all models for missing constraints

---

## 📚 Documentation Updates Needed

### .github/copilot-instructions.md
- [ ] Add Blameable trait usage guidelines
- [ ] Add Statusable trait usage guidelines
- [ ] Document repository pattern standards
- [ ] Add enum migration guidelines

### AGENTS.md
- [ ] Add trait conventions (Blameable, Statusable)
- [ ] Add repository pattern architecture
- [ ] Add service layer / action pattern
- [ ] Update with new status handling approach

### .junie/*.md
- [ ] Create learning document for refactoring patterns
- [ ] Document common pitfalls and solutions
- [ ] Add examples of before/after refactorings

---

## 🔍 Testing Infrastructure

### Test Conventions (from .github/copilot-instructions.md)
- ✅ Each test creates its own data (no seeder dependencies)
- ✅ Use DatabaseTransactions trait
- ✅ Only one HTTP request per test (unless testing workflow)
- ✅ Normalize data types before assertions (dates to ISO strings)
- ⚠️ Need to enforce: Use `assertSoftDeleted()` instead of `assertNull()` for soft deletes
- ⚠️ Need to enforce: Use `Notification::fake()` and `assertSentTo()` for notification tests
- ⚠️ Need to enforce: Use `Storage::fake()` for file operation tests

### TODOs:
- [ ] Create test helper for date normalization
- [ ] Create custom assertion: `assertDateEquals($expected, $actual)`
- [ ] Add test helpers for common setup (create user with role, create client with contacts, etc.)

---

## 🎯 Priority Order

1. **HIGH PRIORITY** - Complete Status Enum Migration
   - Affects: Lead, Task, Offer, Invoice, Project models
   - Blocks: Consistent status transition logic

2. **HIGH PRIORITY** - Implement Money Value Object
   - Affects: PaymentService, InvoiceCalculator
   - Blocks: Accurate payment processing

3. **MEDIUM PRIORITY** - Create Repository Layer
   - Affects: Controllers, Services
   - Improves: Testability, separation of concerns

4. **MEDIUM PRIORITY** - Apply Blameable and Statusable Traits
   - Affects: Task, Lead, Project models
   - Improves: Consistency, reduces duplication

5. **LOW PRIORITY** - Document Management Observer
   - Affects: Document model, DocumentsController
   - Improves: File lifecycle management

6. **ONGOING** - Factory Enhancements
   - Add relationship states as needed for tests

7. **ONGOING** - Documentation Updates
   - Keep docs in sync with code changes

---

## 📝 Notes for Future Agents

### When Adding New Models:
1. Check if it needs Blameable (has user_created_id/user_updated_id)
2. Check if it needs Statusable (has status_id)
3. Check if it needs HasExternalId (uses UUID routing)
4. Always create factory with relationship states
5. Add database constraints (foreign keys, non-nullable where appropriate)

### When Fixing Tests:
1. Ensure test creates its own data
2. Use factories with relationship states
3. Normalize dates before assertions
4. Use proper fakes (Notification::fake(), Storage::fake())
5. One assertion focus per test

### When Refactoring:
1. Check if pattern is already documented here
2. If creating new pattern, add to this document
3. Update affected documentation (.github/copilot-instructions.md, AGENTS.md)
4. Create migration if schema changes needed
5. Update factories if relationship changes made

---

## 🔗 Related Documentation

- [Test Failure Analysis](.github/TEST_FAILURE_ANALYSIS.md) - Detailed analysis of 45 test failures
- [Test Isolation Refactor](.github/test_isolation_refactor.md) - Test isolation guidelines
- [Error Repair Plan](.github/error_repair_plan.md) - Common test error patterns
- [Copilot Instructions](.github/copilot-instructions.md) - Core development guidelines

---

*Last Updated: 2026-04-11*
*Maintainer: Copilot Coding Agent*
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
