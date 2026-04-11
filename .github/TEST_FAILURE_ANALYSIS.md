# Comprehensive Test Failure Analysis
## DaybydayCRM - Failures #38-82 (45 failures analyzed)

This document groups the 45 test failures by root application file and provides consolidated TODOs for each.

---

## Grouped by Root File

### 1. **app/Models/Lead.php** (6 failures)

**Tests Failing:**
- Lead has many appointments (#38)
- Lead has many offers (#39)
- Lead has many documents (#40)
- Lead has many projects (#41)

**Root Causes:**
- Missing `documents()` relationship method
- Missing `projects()` relationship method
- Polymorphic relationships (`appointments`, `offers`) not returning data (factory/test setup issue)

**Consolidated TODOs:**
1. ✅ Add `documents()` morphMany relationship to Lead model
2. ✅ Add `projects()` hasMany relationship to Lead model
3. ✅ Update LeadFactory to support relationship states (->hasAppointments(), ->hasOffers(), ->hasDocuments(), ->hasProjects())
4. ⚠️ Verify polymorphic `source_id` and `source_type` are being set correctly in factories
5. ⚠️ Consider if Lead-to-Project should be 1-to-1 (state transition) or hasMany

---

### 2. **app/Models/Offer.php** (3 failures)

**Tests Failing:**
- Offer has correct lead relation (#42)
- Offer has correct status relation (#43)
- Offer has many lines (#44)

**Root Causes:**
- No `lead()` or `source()` inverse polymorphic relationship
- `status` is a string field, not a relationship (should be migrated to belongsTo Status or use Enum)
- `lines()` method missing (has `invoiceLines()` but test expects `lines()`)

**Consolidated TODOs:**
1. ✅ Add `source()` morphTo relationship for Lead association
2. ✅ Add `lines()` as alias for `invoiceLines()` relationship
3. 🔴 **GLOBAL REFACTOR**: Migrate `status` string to StatusEnum or add `status()` belongsTo relationship
4. ✅ Add non-nullable constraint to `lead_id` or ensure polymorphic `source_id`/`source_type` are always set
5. ✅ Update OfferFactory to always create with a valid lead/source and default status

---

### 3. **app/Models/Task.php** (6 failures)

**Tests Failing:**
- Task has correct status relation (#45)
- Task has correct user relation (#46)
- Task has correct creator relation (#47)
- Task has correct client relation (#48)
- Task has correct invoice relation (#49)
- Task has many activity log (#50)

**Root Causes:**
- `status_id`, `user_assigned_id`, `user_created_id`, `client_id`, `invoice_id` are null in test data
- Activity logging not triggered (model events suppressed in tests or trait not configured)

**Consolidated TODOs:**
1. ✅ Implement **Blameable trait** for automatic `user_created_id` population
2. ✅ Implement **Statusable trait** if statuses are shared across models
3. ✅ Add **TaskAssignment service** for user assignment with validation and logging
4. ✅ Update TaskFactory to provide defaults for all foreign keys
5. ✅ Verify LogsActivity trait is enabled and working in test environment
6. ✅ Add database constraints to make required foreign keys non-nullable

---

### 4. **app/Models/User.php** (8 failures)

**Tests Failing:**
- User has many tasks (#51)
- User has many leads (#52)
- User has many clients (#53)
- User has many absences (#54)
- User has many departments (#55)
- User has many integrations (#56)
- User has many roles (#57)
- User has many settings (#58)

**Root Causes:**
- Relationships return empty collections due to foreign key mismatches or missing pivot entries
- `integrations()` relationship method doesn't exist
- `settings()` relationship method doesn't exist  
- `roles()` likely exists via Entrust trait but factory doesn't create role associations
- `department()` uses belongsToMany but may need sync() in tests

**Consolidated TODOs:**
1. ✅ Add `integrations()` hasMany relationship to User model
2. ✅ Add `settings()` hasMany relationship to User model
3. ✅ Update UserFactory with `->withRole()`, `->hasClients()`, `->hasLeads()`, `->hasTasks()` states
4. ⚠️ Standardize foreign key naming: `user_id` for ownership vs `assigned_user_id` for responsibility
5. 🔴 **GLOBAL DECISION**: Use JSON column for user settings instead of separate table (if settings are simple key-value pairs)
6. ✅ Fix date scopes on relationships (e.g., absences with `onlyCurrentMonth` scope may filter out test data)

---

### 5. **app/Models/Invoice.php** (3 failures)

**Tests Failing:**
- Invoice has many payments (#79)
- Invoice has correct client relation (#80)
- Invoice has many lines (#81)

**Root Causes:**
- `client_id` is null (not set in factory or test)
- Payments and invoice lines not being created with factory
- Invoice lines relationship working but empty collection

**Consolidated TODOs:**
1. ✅ Add database-level non-nullable constraint to `client_id`
2. ✅ Use Laravel factory relationship methods: `Invoice::factory()->hasPayments(1)->hasLines(1)->create()`
3. ✅ Implement **InvoiceTotal service** that prevents finalization without line items
4. ✅ Update InvoiceFactory to require client_id and provide default values

---

### 6. **app/Repositories/Client/ClientRepository.php** (4 failures)

**Tests Failing:**
- Get all clients (#59)
- Find client (#60)
- Create client (#61)
- Update client (#62)

**Root Causes:**
- Global scopes (Tenant, SoftDeletes) filtering records
- Repository methods returning null instead of using `findOrFail()`
- Creation/update logic not persisting due to missing fillable attributes or transaction rollbacks

**Consolidated TODOs:**
1. ✅ Implement `withoutGlobalScopes()` flag in repository methods
2. ✅ Change `find()` to `findOrFail()` for consistent exception handling
3. ✅ Use model `fill()` and `isDirty()` to track changes before persisting
4. ✅ Verify `$fillable` includes all necessary fields
5. 🔴 **GLOBAL PATTERN**: Implement explicit Criteria classes for repository queries

---

### 7. **app/Repositories/Lead/LeadRepository.php** (4 failures)

**Tests Failing:**
- Get all leads (#63)
- Find lead (#64)
- Create lead (#65)
- Update lead (#66)

**Root Causes:**
- Same as ClientRepository (global scopes, null returns, persistence failures)

**Consolidated TODOs:**
1. ✅ Apply same fixes as ClientRepository (withoutGlobalScopes, findOrFail, etc.)
2. ✅ Transition from `find()` to `findOrFail()`
3. ✅ Ensure repository uses model `create()` and verifies `$fillable` attributes
4. ✅ Return refreshed model instance after update to verify state

---

### 8. **app/Repositories/Task/TaskRepository.php** (4 failures)

**Tests Failing:**
- Get all tasks (#67)
- Find task (#68)
- Create task (#69)
- Update task (#70)

**Root Causes:**
- Repository applying default filters (e.g., "where active" or "where not completed")
- Same persistence and retrieval issues as other repositories

**Consolidated TODOs:**
1. ✅ Check for implicit "where active" or "where not completed" scope
2. ✅ Pass explicit filter criteria instead of relying on implicit defaults
3. ✅ Add validation inside repository or use FormRequest
4. ✅ Use model `update()` method directly to ensure event firing

---

### 9. **app/Repositories/User/UserRepository.php** (4 failures)

**Tests Failing:**
- Get all users (#71)
- Find user (#72)
- Create user (#73)
- Update user (#74)

**Root Causes:**
- Repository applying default filter (e.g., `where('active', true)`)
- User creation missing password hashing or required fields

**Consolidated TODOs:**
1. ✅ Implement explicit Criteria classes for transparent filtering
2. ✅ Use `findOrFail` to avoid null returns
3. ✅ Verify password hashing handled in repository or UserCreationAction
4. ✅ Return refreshed model instance after update

---

### 10. **app/Services/Client/ClientService.php** (1 failure)

**Tests Failing:**
- Create client logic (#75)

**Root Causes:**
- Service layer failing to persist record (silent exception in transaction or failed validation)

**Consolidated TODOs:**
1. 🔴 **GLOBAL PATTERN**: Throw custom Domain Exceptions when business rules violated
2. ✅ Refactor service to not return boolean false or null
3. ✅ Log all service-level failures for debugging

---

### 11. **app/Services/Lead/LeadService.php** (1 failure)

**Tests Failing:**
- Transition lead status (#76)

**Root Causes:**
- Status transition logic not saving final state or skipping steps

**Consolidated TODOs:**
1. 🔴 **GLOBAL REFACTOR**: Use State Machine pattern for status transitions
2. ✅ Ensure status changes are validated and logged centrally
3. ✅ Define allowed transitions in configuration

---

### 12. **app/Services/Task/TaskService.php** (1 failure)

**Tests Failing:**
- Assign task to user (#77)

**Root Causes:**
- Service adding new assignment record instead of replacing existing one

**Consolidated TODOs:**
1. ✅ Use `sync()` method on relationship to ensure single owner
2. ✅ If multiple assignees allowed, use `syncWithoutDetaching()`
3. ✅ Log assignment changes for audit trail

---

### 13. **app/Services/Payment/PaymentService.php** (1 failure)

**Tests Failing:**
- Process invoice payment (#78)

**Root Causes:**
- Floating-point precision issues in balance calculation
- Comparison logic incorrect (comparing total vs remaining balance)

**Consolidated TODOs:**
1. 🔴 **GLOBAL REFACTOR**: Introduce Money Value Object (brick/money package)
2. ✅ Avoid floating-point arithmetic for currency
3. ✅ Use Money object for all currency calculations

---

### 14. **app/Http/Controllers/Document/DocumentsController.php** (1 failure)

**Tests Failing:**
- Can download document (#82)

**Root Causes:**
- File not found on testing disk (500 error)
- `FileNotFoundException` not caught

**Consolidated TODOs:**
1. ✅ Use Laravel's `Storage::fake()` in tests
2. ✅ Add `Storage::exists()` check before download attempt
3. ✅ Return 404 instead of 500 when file missing
4. 🔴 **GLOBAL PATTERN**: Use Model Observers for file lifecycle management

---

## Global Refactoring Patterns (Recurring Across Multiple Files)

### 🔴 **Pattern 1: Status Management**
**Affected Files:** Lead.php, Task.php, Offer.php, Invoice.php, LeadService.php

**Current Problem:** Status is sometimes a string field, sometimes a foreign key to `statuses` table, inconsistent across models.

**Solution:**
1. Migrate all status logic to PHP native Enums (Laravel 10+)
2. Create `App\Enums\LeadStatus`, `App\Enums\TaskStatus`, etc.
3. Use enum casting in models: `protected $casts = ['status' => LeadStatus::class];`
4. Implement State Machine for status transitions

**Benefits:**
- Type safety
- IDE autocomplete
- Centralized status logic
- Easy to extend with methods

---

### 🔴 **Pattern 2: Repository Null Returns**
**Affected Files:** All Repository classes (Client, Lead, Task, User)

**Current Problem:** Repositories return null instead of throwing exceptions, causing downstream null pointer issues.

**Solution:**
1. Replace all `find()` with `findOrFail()`
2. Implement custom `ModelNotFoundException` handlers
3. Use explicit Criteria classes for filtering
4. Add `withoutGlobalScopes()` option to all retrieval methods

**Benefits:**
- Consistent error handling
- Better stack traces
- Explicit filter logic

---

### 🔴 **Pattern 3: Currency/Decimal Calculations**
**Affected Files:** PaymentService.php, InvoiceCalculator.php

**Current Problem:** Floating-point precision errors in payment calculations.

**Solution:**
1. Add `brick/money` package
2. Create Money Value Object wrapper
3. Use Money object for all currency operations
4. Store amounts as integers (cents) in database

**Benefits:**
- Precise currency calculations
- Multi-currency support
- Business logic clarity

---

### 🔴 **Pattern 4: Creator/Owner Tracking**
**Affected Files:** Task.php, Lead.php, and other models with `user_created_id`

**Current Problem:** `user_created_id` is often null, inconsistent population.

**Solution:**
1. Create `Blameable` trait
2. Automatically populate `user_created_id` and `user_updated_id` in model boot
3. Use global scope or observer

**Benefits:**
- Automatic audit trail
- DRY principle
- Consistent behavior

---

### 🔴 **Pattern 5: File/Document Management**
**Affected Files:** DocumentsController.php, Document model

**Current Problem:** File operations fail in tests, missing file existence checks.

**Solution:**
1. Always use `Storage::fake()` in tests
2. Create Document Observer for file lifecycle (created, deleted)
3. Add existence checks before operations
4. Implement soft delete with file retention policy

**Benefits:**
- Testable file operations
- Automatic cleanup
- Consistent error handling

---

### 🔴 **Pattern 6: Notification Testing**
**Affected Files:** Various controllers and services

**Current Problem:** Tests query database for notifications instead of using fakes.

**Solution:**
1. Use `Notification::fake()` in all notification tests
2. Assert with `assertSentTo()` instead of DB queries
3. Create notification factory for testing

**Benefits:**
- Faster tests
- No database pollution
- Better assertions

---

### 🔴 **Pattern 7: Relationship Consistency**
**Affected Files:** All models with relationships

**Current Problem:** Missing relationships, inconsistent foreign key naming.

**Solution:**
1. Standardize foreign keys: `user_id` for ownership, `assigned_user_id` for assignment
2. Add all missing relationship methods
3. Use factory states for relationship creation (`->hasClients()`, `->withLineItems()`)
4. Add database constraints (non-nullable where required)

**Benefits:**
- Predictable relationships
- Better data integrity
- Easier testing

---

## Implementation Priority

### Phase 1 (Critical - Blocking Many Tests)
1. Add missing relationships to models (Lead, Offer, User)
2. Fix repository null returns (`findOrFail`)
3. Update factories to create required associations

### Phase 2 (High Impact - Pattern Fixes)
4. Implement Status Enums
5. Add Money Value Object
6. Create Blameable trait
7. Fix Storage/Document handling

### Phase 3 (Service Layer)
8. Fix service layer logic (Client, Lead, Task, Payment)
9. Implement State Machine for status transitions
10. Add proper exception handling

### Phase 4 (Infrastructure)
11. Add database constraints
12. Update test infrastructure
13. Implement Model Observers

### Phase 5 (Documentation)
14. Update .github/todo.md
15. Update AGENTS.md
16. Update .github/copilot-instructions.md

---

## Next Steps

1. ✅ Create this analysis document
2. ⏭ Start with Phase 1: Add missing relationships
3. ⏭ Run tests to verify each fix
4. ⏭ Move to Phase 2: Implement global patterns
5. ⏭ Continue iteratively until all 119 failures are resolved

