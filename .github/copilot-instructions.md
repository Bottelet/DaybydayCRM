# DaybydayCRM AI Agent Instructions

## Core Principles & Documentation
- **Refer to .github/*.md:** For detailed structural analysis, fundamental architectural problems, error repair plans, and refactoring strategies, always consult the files in the `.github/` directory.
- **Detailed Instructions:**
  - `.github/error_repair_plan.md`: Common test failures and their specific fixes.
  - `.github/refactor_plan.md`: Roadmap for modernizing the codebase.
  - `.github/structural_analysis.md`: Analysis of current code/test suite weaknesses.
  - `.github/fundamental_analysis.md`: Deep architectural issues and technical debt.
  - `.github/test_isolation_refactor.md`: **CRITICAL** - Comprehensive plan to eliminate test interdependencies and the cascade problem.

## Modern Laravel Patterns (Applied 2026-04)

### 1. Action Classes for Business Logic
- **Pattern:** Extract business logic from controllers into dedicated Action classes
- **Location:** `app/Actions/{Domain}/{ActionName}Action.php`
- **Example:** `StoreAbsenceAction` - handles absence creation with all business rules
- **Benefits:** Testable, reusable, single responsibility

### 2. Currency Handling in FormRequests
- **Pattern:** Use `prepareForValidation()` to normalize currency input
- **Implementation:** Convert comma separators to dots before validation
- **Example:** `PaymentRequest::prepareForValidation()` normalizes "5000,234" to "5000.234"
- **Rule:** Always use `numeric` validation after normalization, not regex

### 3. Exception Types in Custom Enums
- **Pattern:** Legacy custom enum classes should throw `\InvalidArgumentException` not `Exception`
- **Affected:** `AbsenceReason`, `InvoiceStatus`, `PaymentSource`, etc.
- **Reason:** More specific exception types for better error handling

### 4. Soft Delete Assertions
- **Pattern:** Use `$this->assertSoftDeleted($model)` instead of `assertNull()`
- **Context:** When testing models that use `SoftDeletes` trait
- **Example:** Client model deletion tests

## Testing & Database Guidelines

### Test Isolation Requirements (CRITICAL - MUST FOLLOW)

**PROHIBITED: Brittle Tests**

Tests MUST NOT:

1. **Compare different types without normalization**
   ```php
   // ❌ PROHIBITED - Carbon object vs string
   $this->assertEquals($model->created_at, $json['created_at']);
   
   // ✅ REQUIRED - Normalize to same format
   $this->assertEquals($model->created_at->toISOString(), $json['created_at']);
   ```

2. **Depend on other tests' side effects**
   ```php
   // ❌ PROHIBITED - Relies on another test creating data
   public function test_update_client() {
       $client = Client::first(); // What if no clients exist?
   }
   
   // ✅ REQUIRED - Create own data
   public function test_update_client() {
       $client = factory(Client::class)->create();
   }
   ```

3. **Make unrelated HTTP requests**
   ```php
   // ❌ PROHIBITED - Why is this here?
   public function test_payment_validation() {
       $this->get('/client/create'); // Side effect setup - NEVER DO THIS
       $response = $this->post('/payment', [...]);
   }
   
   // ✅ REQUIRED - Direct setup
   public function test_payment_validation() {
       // If you need session data, set it explicitly:
       session(['key' => 'value']);
       $response = $this->post('/payment', [...]);
   }
   ```

4. **Make multiple requests in one test** (unless explicitly testing a workflow sequence)
   ```php
   // ❌ PROHIBITED - Second request depends on first
   public function test_feature() {
       $this->post('/create', [...]);
       $response = $this->get('/list'); // Depends on POST creating data
   }
   
   // ✅ REQUIRED - Separate tests
   public function test_can_create() {
       $response = $this->post('/create', [...]);
       $response->assertStatus(201);
   }
   
   public function test_can_list() {
       factory(Model::class)->create(); // Create own test data
       $response = $this->get('/list');
       $response->assertOk();
   }
   ```

### Test Isolation Checklist

Every test MUST:
- ✅ Create its own test data (no dependencies on seeders or other tests)
- ✅ Use DatabaseTransactions trait (RefreshDatabase after test suite is green)
- ✅ Be runnable in any order (random, first, last, alone)
- ✅ Clean up after itself (trait handles this automatically)
- ✅ Have ONE clear purpose (test one behavior)
- ✅ Have ONE HTTP request (unless explicitly testing a sequence/workflow)
- ✅ Normalize data types before assertions (dates, numbers, etc.)

### Common Test Issues & Solutions

- **Missing Default Values (SQLSTATE[HY000]: 1364 Field 'X' doesn't have a default value):**
  - **Activity Model:** Always ensure `ip_address` and `external_id` (UUID) are set. The `Activity` model has a `boot()` method that handles this automatically if these fields are missing.
  - **Factories:** When creating models in tests, ensure all NOT NULL fields without defaults are provided or handled in the factory/boot method.

- **Unique Constraint Violations (SQLSTATE[23000]: 1062 Duplicate entry):**
  - **Roles/Permissions:** Use `attachRole()` or `attachPermission()` from `EntrustUserTrait`. This trait has been modified to check for existing associations before attaching to prevent duplicate entry errors in the `role_user` table.

- **PHPUnit 10+ Compatibility:**
  - Avoid `assertObjectHasAttribute`. Use `assertTrue(property_exists($object, 'attribute'))` instead.
  - Use `#[Test]` and `#[Group('...')]` attributes instead of `@test` and `@group` annotations.

- **Date Comparisons (CRITICAL - Prevents Brittle Tests):**
  - **NEVER** directly compare Carbon objects with strings
  - **ALWAYS** normalize to the same format before comparison:
    ```php
    // ✅ CORRECT
    $this->assertEquals($model->created_at->toISOString(), $response->json('created_at'));
    
    // Or use custom helper if available:
    $this->assertDateEquals($model->created_at, $response->json('created_at'));
    ```

- **Notification Testing:**
  - **ALWAYS** use `Notification::fake()` at the start of the test
  - **ALWAYS** use `Notification::assertSentTo()` to verify notifications
  - **NEVER** query the notifications table directly in tests
    ```php
    // ✅ CORRECT
    Notification::fake();
    // ... trigger notification
    Notification::assertSentTo($user, TaskAssignedNotification::class);
    ```

- **Storage/File Testing:**
  - **ALWAYS** use `Storage::fake()` for file upload/download tests
  - **NEVER** rely on actual file system operations in tests
    ```php
    // ✅ CORRECT
    Storage::fake('local');
    // ... perform file operations
    Storage::disk('local')->assertExists($path);
    ```

### Model Boot Methods

- Many models in this project use UUIDs for `external_id`. Ensure any new models follow this pattern using a `boot()` method or a reusable trait to generate UUIDs on creation.

## Model Observer Pattern

**Use Model Observers for side effects that should happen automatically when models are created, updated, or deleted.**

### When to Use Observers
- File deletion when a model is deleted (e.g., `DocumentObserver`)
- Cascade soft deletes to related models (e.g., `TaskObserver`)
- Automatic logging/auditing
- Search index updates
- Cache invalidation

### Observer Implementation
```php
// app/Observers/DocumentObserver.php
class DocumentObserver
{
    public function deleting(Document $document)
    {
        // Delete physical file when DB record is deleted
        $fileSystem = GetStorageProvider::getStorage();
        $fileSystem->delete($document);
    }
}

// Register in AppServiceProvider::boot()
Document::observe(DocumentObserver::class);
```

### Benefits
- Decouples business logic from controllers
- Ensures data consistency
- Automatic side effect handling
- Single responsibility principle

## Blameable Trait Pattern

**Use the Blameable trait to automatically track who created a model.**

### Implementation
```php
// In Model
use App\Traits\Blameable;

class Invoice extends Model
{
    use Blameable;
    
    protected $fillable = [..., 'user_created_id'];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }
}
```

### Benefits
- Automatic audit trail
- No manual setting in controllers
- Consistent across all models
- Works with authentication system

## Repository Pattern

**When creating repositories, follow this pattern:**

### Repository Structure
```php
interface RepositoryInterface
{
    public function getAll();
    public function find($id);
    public function findOrFail($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

class BaseRepository implements RepositoryInterface
{
    public function findOrFail($id)
    {
        $model = $this->model->find($id);
        
        if (!$model) {
            throw new ModelNotFoundException();
        }
        
        return $model;
    }
}
```

### Benefits
- Consistent error handling (404 responses)
- Testable business logic
- Decouples data access from controllers
- Easier to swap implementations

## Service Layer Conventions

**Business logic should live in Service classes, not Controllers.**

### Service Structure
```php
// app/Services/Invoice/InvoiceService.php
class InvoiceService
{
    public function calculateTotal(Invoice $invoice): Money
    {
        $calculator = new InvoiceCalculator($invoice);
        return $calculator->getTotalPrice(); // Includes VAT
    }
    
    public function generateInvoiceNumber(): string
    {
        $service = app(InvoiceNumberService::class);
        return $service->nextInvoiceNumber();
    }
}
```

### Tax Calculation
- Use `InvoiceCalculator` for all tax calculations
- `getSubTotal()`: Price without VAT
- `getTotalPrice()`: Price with VAT included
- `getVatTotal()`: Just the VAT amount

### Controller Usage
```php
// Controllers should be thin - delegate to Services
class InvoicesController
{
    public function store(Request $request, InvoiceService $service)
    {
        $invoice = $service->create($request->validated());
        return redirect()->route('invoices.show', $invoice);
    }
}
```

## Role & Permission Management

- The project uses a custom implementation of Entrust (`app/Zizaco/Entrust/`).
- Use `owner` or `administrator` roles in tests when high-level permissions are required.
- Always check if a user has a role before attaching it if not using the modified `attachRole()` method.

## Documentation Updates

When implementing new patterns or fixes:
1. Update `.github/todo.md` with pattern status
2. Update this file (copilot-instructions.md) with conventions
3. Update `AGENTS.md` with architectural guidance
4. Reference `.github/error_repair_plan.md` for common fixes

