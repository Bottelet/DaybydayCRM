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
