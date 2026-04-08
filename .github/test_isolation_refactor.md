# Test Isolation Refactor Plan - DaybydayCRM

**Priority: CRITICAL**  
**Status: Planning Phase**  
**Goal: Eliminate test interdependencies and achieve true test isolation**

---

## Executive Summary

This document outlines a comprehensive refactoring plan to eliminate the "cascade problem" - where tests depend on side effects from other tests, causing mysterious failures when tests are disabled. The plan addresses deep architectural issues with database seeding, factory design, default values, and test infrastructure.

---

## The Cascade Problem: Root Cause Analysis

### Current State
Tests currently fail in unexpected ways because they:
1. Depend on database state created by other tests
2. Rely on side effects from HTTP requests to setup routes (e.g., `GET /client/create`)
3. Share session state between tests
4. Make multiple sequential HTTP requests within a single test method
5. Assume data from seeders will always be present

### Why This Is Critical
When a test is marked `markTestIncomplete()` to temporarily disable it:
- Other tests that unknowingly depend on its side effects mysteriously fail
- Debugging becomes extremely difficult because the dependency is hidden
- The test suite becomes brittle and unreliable
- CI failures become unpredictable

---

## Phase 1: Migrate to RefreshDatabase Trait

**Timeline: After achieving green test suite**  
**Impact: HIGH - Eliminates database state sharing**

### Current Problem
`TestCase::setUp()` runs `Artisan::call('migrate:fresh')` and `Artisan::call('db:seed')` before EVERY test. This:
- Is extremely slow (seconds per test)
- Shares seeded data across tests
- Makes tests dependent on seeder state

### Solution
```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase trait handles database setup
        // Each test runs in a transaction that's rolled back

        // Ensure Faker\Generator is bound for legacy factories
        $this->app->singleton(Generator::class, function () {
            return Factory::create();
        });

        // Create test user explicitly - don't rely on seeders
        $this->user = $this->createTestUser();
        $this->actingAs($this->user);
    }

    /**
     * Create a test user with owner role
     * Each test gets its own isolated user
     */
    protected function createTestUser(): User
    {
        // Create owner role if it doesn't exist
        $ownerRole = Role::firstOrCreate(
            ['name' => 'owner'],
            [
                'external_id' => (string) Str::uuid(),
                'display_name' => 'Owner',
                'description' => 'Owner role for testing'
            ]
        );

        $user = factory(User::class)->create([
            'name' => 'Test User',
            'email' => 'test-' . Str::random(10) . '@test.com',
        ]);

        $user->attachRole($ownerRole);

        return $user;
    }
}
```

### Benefits
- Each test runs in an isolated database transaction
- Tests can be run in any order
- Parallel test execution becomes possible
- Test execution speed improves dramatically
- No more shared state between tests

### Migration Steps
1. **Wait for green tests** - Don't apply until current test suite passes
2. **Replace migrate:fresh with RefreshDatabase** - One-line change
3. **Remove db:seed from setUp()** - Tests must create their own data
4. **Update createTestUser()** - Create user and role programmatically
5. **Verify all tests still pass** - Run full suite multiple times

---

## Phase 2: Fix External ID Generation

**Timeline: Immediate (can start now)**  
**Impact: HIGH - Prevents "field doesn't have default value" errors**

### Current Problem
Models like `Activity`, `Appointment`, `Offer`, etc. have `external_id` columns that are NOT NULL but:
- No database default value
- Inconsistent model boot() methods
- Factory definitions missing the field
- Tests randomly fail with "Field 'external_id' doesn't have a default value"

### Root Causes Identified
1. **Activity Model**: Has boot() method to generate external_id, BUT:
   ```php
   // app/Models/Activity.php - WORKS but inconsistent
   protected static function boot()
   {
       parent::boot();
       self::creating(function ($model) {
           $model->external_id = $model->external_id ?: (string) Str::uuid();
           $model->ip_address = $model->ip_address ?: (request()->ip() ?: '127.0.0.1');
       });
   }
   ```

2. **Other Models**: Don't have boot() methods, rely on:
   - Factories to provide external_id (but factories don't always)
   - Manual assignment in tests (brittle)
   - Database defaults (which don't exist)

### Solution: Unified Trait Approach

```php
// app/Models/Concerns/HasExternalId.php
<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasExternalId
{
    protected static function bootHasExternalId(): void
    {
        static::creating(function ($model) {
            if (empty($model->external_id)) {
                $model->external_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     * Use external_id for routing instead of auto-increment id
     */
    public function getRouteKeyName(): string
    {
        return 'external_id';
    }
}
```

### Apply to All Models
```php
// app/Models/Activity.php
class Activity extends Model
{
    use HasExternalId;
    
    // Remove custom boot() method - trait handles it
    // protected static function boot() { ... } // DELETE THIS
}

// app/Models/Appointment.php
class Appointment extends Model
{
    use HasExternalId;
}

// app/Models/Client.php, Lead.php, Project.php, Task.php, etc.
// Add trait to ALL models with external_id column
```

### Update Factories
All factories should STILL provide external_id for explicitness:
```php
// database/factories/AppointmentFactory.php
$factory->define(Appointment::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'color' => '#000000',  // Already fixed
        // ... other fields
    ];
});
```

### Migration Considerations
**User Concern: "All users have already migrated and I hate migrating to fix migrations"**

**Solution**: We DON'T need new migrations! Instead:
1. Add the trait to models (application layer, not database)
2. Models automatically generate external_id on create()
3. Existing records already have external_id values
4. No database schema changes needed

### Benefits
- Consistent external_id generation across all models
- Eliminates random "field doesn't have default value" errors
- Single source of truth for UUID generation logic
- Works with existing database schema
- No migrations required

---

## Phase 3: Fix Default Value Issues

**Timeline: Immediate (can start now)**  
**Impact: MEDIUM - Prevents factory/migration drift**

### Current Problems

1. **Appointment.color**: Required field, no default, factory didn't provide it
   - Status: ✅ **FIXED** - Factory now provides `#000000`

2. **Offer.status**: Required field, no default, factory doesn't provide it
   - Status: ❌ **NOT FIXED** - Needs attention

3. **Activity.ip_address**: Required field, boot() handles it
   - Status: ✅ **WORKING** - boot() method provides default

### Solution Strategy

For each required field without a database default:

**Option A: Add Model Boot Logic** (Preferred for application-level defaults)
```php
// app/Models/Offer.php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        if (empty($model->status)) {
            $model->status = 'pending'; // or whatever default makes sense
        }
    });
}
```

**Option B: Update Factory** (Preferred for test-specific values)
```php
// database/factories/OfferFactory.php
$factory->define(Offer::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'status' => 'pending',  // ADD THIS
        // ... other fields
    ];
});
```

**Option C: Add Database Migration** (⚠️ AVOID - user doesn't want this)
```sql
-- DON'T DO THIS - users already migrated
ALTER TABLE offers MODIFY status VARCHAR(255) DEFAULT 'pending';
```

### Audit All Models

Run this script to find all NOT NULL fields without defaults:
```bash
# Find all migration files with NOT NULL columns
cd /home/runner/work/DaybydayCRM/DaybydayCRM
find database/migrations -name "*.php" -exec grep -l "nullable(false)\|->integer(\|->string(" {} \; \
  | xargs grep -A 2 -B 2 "nullable(false)"
```

Then for each:
1. Check if model has boot() method handling it
2. Check if factory provides it
3. Add boot() method OR update factory if missing

---

## Phase 4: Fix Brittle Date Comparisons

**Timeline: Immediate (can start now)**  
**Impact: MEDIUM - Prevents type mismatch failures**

### Current Problem
Tests compare Carbon objects with JSON-serialized strings:
```php
// tests/Unit/Controllers/Appointment/AppointmentsControllerTest.php
// BRITTLE - comparing Carbon object to JSON string
$this->assertEquals($this->appointmentsWithInTime->start_at, $correctAppointment['start_at']);
```

This fails because:
- `$this->appointmentsWithInTime->start_at` is a Carbon instance
- `$correctAppointment['start_at']` is a string from JSON response
- Equality check fails due to type mismatch

### Solution 1: Normalize to ISO Strings (Preferred)
```php
// Status: ✅ **FIXED** in AppointmentsControllerTest.php
$this->assertEquals(
    $this->appointmentsWithInTime->start_at->toISOString(),
    $correctAppointment['start_at']
);
```

### Solution 2: Add Custom Assertion to TestCase
```php
// tests/TestCase.php
protected function assertDateEquals($expected, $actual, string $message = ''): void
{
    $expectedDate = $expected instanceof \Carbon\Carbon 
        ? $expected->toISOString() 
        : \Carbon\Carbon::parse($expected)->toISOString();
        
    $actualDate = $actual instanceof \Carbon\Carbon 
        ? $actual->toISOString() 
        : \Carbon\Carbon::parse($actual)->toISOString();
        
    $this->assertEquals($expectedDate, $actualDate, $message);
}
```

Usage:
```php
// In tests
$this->assertDateEquals($expectedCarbon, $jsonResponseDate);
$this->assertDateEquals('2024-01-01', $model->created_at);
```

### Audit All Date Comparisons
```bash
# Find all assertEquals with date fields
grep -r "assertEquals.*->.*_at" tests/
grep -r "assertEquals.*Carbon" tests/
```

Update each to use normalized comparison or custom assertion.

### Add to AI Instructions
```markdown
## Date Comparison in Tests

**NEVER** directly compare Carbon objects with strings:
```php
// ❌ BAD - brittle, will fail
$this->assertEquals($model->created_at, $jsonResponse['created_at']);

// ✅ GOOD - normalized to same format
$this->assertEquals($model->created_at->toISOString(), $jsonResponse['created_at']);

// ✅ GOOD - custom assertion
$this->assertDateEquals($model->created_at, $jsonResponse['created_at']);
```

---

## Phase 5: Fix Status Factory Source Type

**Timeline: Immediate (can start now)**  
**Impact: LOW - Improves developer experience**

### Current Problem
PR description claims "Phase 2 updates the Status factory to include `source_type`" but:
- `database/factories/StatusFactory.php` does NOT include `source_type` field
- Tests manually pass `source_type` on each factory call:
  ```php
  $status = factory(Status::class)->create(['source_type' => Lead::class]);
  ```

### Two Options

**Option A: Keep Current Approach** (Recommended)
- Status factory remains generic
- Tests explicitly provide source_type based on context
- Benefits: Flexibility, tests are explicit about intent
- Update PR description to clarify this is the approach

**Option B: Add Factory States**
```php
// database/factories/StatusFactory.php
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;

$factory->define(Status::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'title' => $faker->word,
        'color' => '#000',
        // Don't set source_type in base definition
    ];
});

$factory->state(Status::class, 'lead', function (Faker $faker) {
    return [
        'source_type' => Lead::class,
    ];
});

$factory->state(Status::class, 'project', function (Faker $faker) {
    return [
        'source_type' => Project::class,
    ];
});

$factory->state(Status::class, 'task', function (Faker $faker) {
    return [
        'source_type' => Task::class,
    ];
});
```

Usage:
```php
// Before
$status = factory(Status::class)->create(['source_type' => Lead::class]);

// After (with states)
$status = factory(Status::class)->states('lead')->create();
```

**Recommendation**: Keep Option A (current approach), just update documentation to clarify.

---

## Phase 6: Eliminate Test Interdependencies

**Timeline: After Phase 1 complete**  
**Impact: CRITICAL - Solves the cascade problem**

### Audit Script
```bash
#!/bin/bash
# Find tests making multiple HTTP requests

echo "=== Tests with multiple HTTP requests ==="
grep -r "->json(" tests/ | grep -v "response =" | cut -d: -f1 | sort | uniq -c | grep -v "   1 "

echo "=== Tests making GET requests before POST/PATCH ==="
grep -B5 "->json('POST\|PATCH\|PUT" tests/ | grep "->get(" 

echo "=== Tests with markTestIncomplete ==="
grep -r "markTestIncomplete" tests/

echo "=== Tests making sequential requests in one method ==="
for file in $(find tests -name "*Test.php"); do
    count=$(grep -c "->json(" "$file" 2>/dev/null || echo 0)
    if [ "$count" -gt 1 ]; then
        methods=$(grep -B10 "->json(" "$file" | grep "public function test_" | sort -u)
        echo "$file: $count requests"
        echo "$methods"
        echo "---"
    fi
done
```

### Refactor Examples

#### Example 1: PaymentsControllerAddPaymentTest::adding_wrong_amount_parameter_return_error()

**Current (BAD):**
```php
public function adding_wrong_amount_parameter_return_error()
{
    $this->actingAs($this->user)->get('/client/create');  // WHY?!
    $this->assertEquals('unpaid', $this->invoice->status);
    $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => 'a string',
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'A random description',
    ]);

    $response->assertStatus(422);
}
```

**Refactored (GOOD):**
```php
public function adding_wrong_amount_parameter_return_error()
{
    // Remove the mysterious GET /client/create - it serves no purpose
    // The test should be completely isolated
    
    $this->assertEquals('unpaid', $this->invoice->status);
    $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => 'a string',
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'A random description',
    ]);

    $response->assertStatus(422);
    
    // If that GET was setting up session state, do it explicitly:
    // session(['key' => 'value']);
}
```

#### Example 2: PaymentsControllerAddPaymentTest::can_add_negative_payment_with_separator()

**Current (BAD):**
```php
public function can_add_negative_payment_with_separator()
{
    $this->assertTrue($this->invoice->payments->isEmpty());
    
    // FIRST request
    $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => -5000, 234,  // Invalid PHP syntax!
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'A random description',
    ]);

    // SECOND request - depends on first!
    $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => -5000.234,
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'A random description',
    ]);

    $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    $response->assertStatus(302);
}
```

**Refactored (GOOD):**
```php
// Split into TWO separate tests

public function can_add_negative_payment_with_comma_separator()
{
    $this->assertTrue($this->invoice->payments->isEmpty());
    
    $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => '-5000,234',  // String representing locale format
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'Payment with comma separator',
    ]);

    $response->assertStatus(302);
    $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    // Optionally assert the exact amount stored
    $this->assertEquals(-500023400, $this->invoice->payments->first()->amount); // cents
}

public function can_add_negative_payment_with_dot_separator()
{
    $this->assertTrue($this->invoice->payments->isEmpty());
    
    $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
        'amount' => '-5000.234',  // Dot separator
        'payment_date' => '2020-01-01',
        'source' => 'bank',
        'description' => 'Payment with dot separator',
    ]);

    $response->assertStatus(302);
    $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    $this->assertEquals(-500023400, $this->invoice->payments->first()->amount);
}
```

### Code Review Checklist for Test Isolation

Before merging any test:

- [ ] Does this test make any HTTP requests not directly related to what it's testing?
- [ ] Does this test make more than one HTTP request?
  - If yes, is it testing a workflow/sequence? (Document this clearly)
  - If no, split into separate tests
- [ ] Does this test depend on database state from other tests?
  - Each test must create its own data in setUp() or the test method
- [ ] Does this test depend on session state from other tests?
  - Use session() helper to set state explicitly
- [ ] Can this test run in isolation? Test it:
  ```bash
  vendor/bin/phpunit --filter testMethodName
  ```
- [ ] Can this test run first? Last? Out of order?
  ```bash
  vendor/bin/phpunit --order-by=random
  ```

---

## Phase 7: Update AI Documentation

**Timeline: Immediate**  
**Impact: MEDIUM - Prevents future issues**

### Add to copilot-instructions.md

```markdown
## Test Isolation Requirements

### PROHIBITED: Brittle Tests

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
       $this->get('/client/create'); // Side effect setup
       $response = $this->post('/payment', [...]);
   }
   
   // ✅ REQUIRED - Direct setup
   public function test_payment_validation() {
       // If you need session data:
       session(['key' => 'value']);
       $response = $this->post('/payment', [...]);
   }
   ```

4. **Make multiple requests in one test** (unless testing a workflow)
   ```php
   // ❌ PROHIBITED - Second request depends on first
   public function test_feature() {
       $this->post('/create', [...]);
       $response = $this->get('/list'); // Depends on POST
   }
   
   // ✅ REQUIRED - Separate tests
   public function test_can_create() {
       $response = $this->post('/create', [...]);
       $response->assertStatus(201);
   }
   
   public function test_can_list() {
       factory(Model::class)->create(); // Own data
       $response = $this->get('/list');
       $response->assertOk();
   }
   ```

### Test Isolation Checklist

Every test MUST:
- ✅ Create its own test data (no dependencies on seeders or other tests)
- ✅ Use RefreshDatabase trait (or DatabaseTransactions for legacy)
- ✅ Be runnable in any order (random, first, last, alone)
- ✅ Clean up after itself (trait handles this automatically)
- ✅ Have ONE clear purpose (test one behavior)
- ✅ Have ONE HTTP request (unless explicitly testing a sequence)
- ✅ Normalize data types before assertions (dates, numbers, etc.)

### Date Comparison Standards

```php
// For date assertions, ALWAYS normalize:
$this->assertDateEquals($expected, $actual); // Custom helper

// Or explicitly:
$this->assertEquals(
    $model->date_field->toISOString(),
    $response->json('date_field')
);
```

### External ID Pattern

All models with external_id MUST use the HasExternalId trait:
```php
use App\Models\Concerns\HasExternalId;

class MyModel extends Model
{
    use HasExternalId;
}
```

This ensures:
- Automatic UUID generation on create
- Consistent routing behavior
- No "field doesn't have default value" errors
```

---

## Implementation Timeline

### Immediate (Can Start Now)
1. ✅ Fix brittle date comparisons (AppointmentsControllerTest)
2. ✅ Fix invalid PHP syntax in documentation
3. ✅ Add language identifiers to markdown code blocks
4. ⏳ Create HasExternalId trait
5. ⏳ Apply HasExternalId to all models
6. ⏳ Audit and fix default value issues (Offer.status, etc.)
7. ⏳ Update AI documentation with test isolation requirements

### After Green Test Suite
1. ⏳ Migrate to RefreshDatabase trait
2. ⏳ Remove db:seed from TestCase::setUp()
3. ⏳ Refactor all tests with multiple HTTP requests
4. ⏳ Remove mysterious GET requests from payment tests
5. ⏳ Add custom assertDateEquals helper to TestCase
6. ⏳ Run full test suite with --order-by=random to verify isolation

### Ongoing
1. ⏳ Code review checklist enforcement
2. ⏳ Quarterly audit for test isolation violations
3. ⏳ Update this document as new patterns emerge

---

## Success Metrics

1. **All tests pass in random order**
   ```bash
   vendor/bin/phpunit --order-by=random
   ```

2. **All tests pass in isolation**
   ```bash
   for test in $(phpunit --list-tests); do
       vendor/bin/phpunit --filter "$test" || echo "FAILED: $test"
   done
   ```

3. **No tests marked incomplete**
   ```bash
   grep -r "markTestIncomplete" tests/ && echo "FOUND INCOMPLETE TESTS" || echo "ALL TESTS ACTIVE"
   ```

4. **No unexpected HTTP requests in tests**
   ```bash
   # Should return no results
   grep -r "->get(\|->post(\|->patch(" tests/ | grep -v "response =" | grep -v "setUp"
   ```

5. **Test suite runtime improvement**
   - Before RefreshDatabase: ~5 minutes
   - After RefreshDatabase: ~30 seconds (target)

---

## Maintenance

This document should be updated:
- When new test isolation patterns are discovered
- When new models are added (add to HasExternalId checklist)
- After each phase is completed (update status from ⏳ to ✅)
- When AI instructions are updated

**Last Updated**: 2026-04-08  
**Next Review**: After achieving green test suite
