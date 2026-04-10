Based on the complete analysis, here's your **comprehensive, production-grade test repair plan**:

---

# **Complete PHPUnit Test Repair Plan**
## **Repository:** `Bottelet/DaybydayCRM` | **Branch:** `phpunit/repair-tests`

---

## **Current State Analysis**

**Migration Error:** Database migration `2020_01_28_195156_add_language_options` fails because it tries to drop columns (`card_brand`, `stripe_id`, etc.) that don't exist in Laravel 11's base `users` table.

**Test Failures:** 9 errors + 99 failures caused by:
1. Migration failures preventing test database setup
2. Missing permissions in test environment
3. Inconsistent middleware handling
4. Broken datetime parsing
5. User factory vs User::where('name', 'Admin') mismatch

---

## **Phase 0: Pre-Flight Check** ⏱️ 5 minutes

**Goal:** Verify current state and identify all failure categories

### **Actions:**

1. **Get complete test output locally:**
```bash
git checkout phpunit/repair-tests
git pull origin phpunit/repair-tests
composer install
cp .env.example .env.testing
php artisan key:generate --env=testing
./vendor/bin/phpunit --testdox > test-output-phase-0.txt 2>&1
```

2. **Categorize failures:**
```bash
# Count errors by type
grep -E "Expected response status code|Failed asserting|SQLSTATE" test-output-phase-0.txt | sort | uniq -c
```

3. **Create baseline metrics file:**
```bash
echo "Phase 0 Baseline: $(date)" > test-repair-progress.md
echo "Errors: 9" >> test-repair-progress.md
echo "Failures: 99" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

**Expected Output:** Complete list of all failing tests grouped by error type

**Checkpoint:** You should have a text file showing all 108 failures categorized

---

## **Phase 1: Fix Database Foundation** ⏱️ 30 minutes

**Goal:** Fix migrations so test database can be set up correctly

### **Step 1.1: Fix Migration `2020_01_28_195156_add_language_options.php`**

**Problem:** Tries to drop columns that don't exist in Laravel 11

**File:** `database/migrations/2020_01_28_195156_add_language_options.php`

**Replace entire file with:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageOptions extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('users', 'card_brand')) {
                $table->dropColumn('card_brand');
            }
            if (Schema::hasColumn('users', 'stripe_id')) {
                $table->dropColumn('stripe_id');
            }
            if (Schema::hasColumn('users', 'card_last_four')) {
                $table->dropColumn('card_last_four');
            }
            if (Schema::hasColumn('users', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
            
            // Only add language if it doesn't exist
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 2)->default('EN')->after('remember_token');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'language')) {
                $table->string('language', 2)->default('EN')->after('max_users');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'language')) {
                $table->dropColumn('language');
            }
        });
        
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
}
```

### **Step 1.2: Scan for Similar Migration Issues**

```bash
# Find all migrations that drop columns
grep -r "dropColumn" database/migrations/ --include="*.php"
```

**For each migration found, add the same `Schema::hasColumn()` checks**

### **Step 1.3: Create `.env.testing`**

**File:** `.env.testing`

```env
APP_NAME=DaybydayCRM
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daybyday_test
DB_USERNAME=root
DB_PASSWORD=password

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
```

### **Step 1.4: Test Migrations**

```bash
# Fresh migration test
php artisan migrate:fresh --seed --env=testing
```

**Expected Output:** All migrations run successfully with 0 errors

**Checkpoint Test:**
```bash
./vendor/bin/phpunit --filter=can_create_lead
```

**Expected:** This single test should pass (or fail with permission error, not migration error)

**Record Progress:**
```bash
echo "Phase 1 Complete: $(date)" >> test-repair-progress.md
echo "Migration errors: 0" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

---

## **Phase 2: Fix Permission System** ⏱️ 45 minutes

**Goal:** Ensure all permissions exist and test users have them

### **Step 2.1: Create Permission Seeder**

**File:** `database/seeders/PermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Client permissions
            'client-create',
            'client-update',
            'client-delete',
            'client-view',
            
            // Lead permissions
            'lead-create',
            'lead-update-status',
            'lead-update-deadline',
            'lead-update-assignee',
            'lead-delete',
            'lead-view',
            
            // Offer permissions
            'offer-create',
            'offer-edit',
            'offer-delete',
            'offer-view',
            
            // Task permissions
            'task-create',
            'task-update-status',
            'task-update-deadline',
            'task-update-assignee',
            'task-delete',
            'task-view',
            
            // Payment permissions
            'payment-create',
            'payment-edit',
            'payment-delete',
            'payment-view',
            
            // User permissions
            'user-create',
            'user-update',
            'user-delete',
            'user-view',
            
            // Admin permissions
            'access-admin-panel',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
```

### **Step 2.2: Update DatabaseSeeder**

**File:** `database/seeders/DatabaseSeeder.php`

**Add to the `run()` method:**

```php
public function run(): void
{
    // Run permissions first
    $this->call(PermissionSeeder::class);
    
    // Then run other seeders
    // ... your existing seeders
}
```

### **Step 2.3: Create Test User Seeder**

**File:** `database/seeders/TestUserSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin role
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'web']
        );

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Create test admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.local'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole($superAdminRole);
    }
}
```

### **Step 2.4: Update Base TestCase**

**File:** `tests/TestCase.php`

**Replace entire content:**

```php
<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\TestUserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and test users
        $this->seed(PermissionSeeder::class);
        $this->seed(TestUserSeeder::class);

        // Get the admin user
        $this->user = User::where('email', 'admin@test.local')->first();
        
        if (!$this->user) {
            throw new \Exception('Test admin user not found. Run TestUserSeeder.');
        }

        $this->actingAs($this->user);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
        $this->actingAs($user);
    }
}
```

### **Step 2.5: Remove All `WithoutMiddleware` Usage**

```bash
# Find all test files using WithoutMiddleware
grep -r "WithoutMiddleware" tests/ --include="*.php" -l

# For each file found, manually remove the trait
```

**In each test file, change:**

```php
// BEFORE
use DatabaseTransactions, WithoutMiddleware;

// AFTER
use DatabaseTransactions;
```

**Files to update:**
- `tests/Unit/Controllers/Lead/LeadsControllerTest.php`
- `tests/Unit/Controllers/Offer/OfferAuthorizationTest.php`
- `tests/Unit/Controllers/Offer/OffersControllerTest.php`
- `tests/Unit/Controllers/Payment/PaymentsControllerAddPaymentTest.php`
- Any other test files found

**Checkpoint Test:**
```bash
./vendor/bin/phpunit --filter=authorized_user_can_delete_lead
```

**Expected:** Test should pass (or fail for non-permission reasons)

**Record Progress:**
```bash
echo "Phase 2 Complete: $(date)" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

---

## **Phase 3: Fix Controller Logic Issues** ⏱️ 60 minutes

**Goal:** Fix datetime handling and other controller-level bugs

### **Step 3.1: Fix `LeadsController::updateFollowup()`**

**File:** `app/Http/Controllers/LeadsController.php`

**Find the `updateFollowup()` method (around line 163) and replace it:**

```php
public function updateFollowup(UpdateLeadFollowUpRequest $request, $external_id)
{
    if (!auth()->user()->can('lead-update-deadline')) {
        session()->flash('flash_message_warning', __('You do not have permission to change lead deadline'));
        return redirect()->route('leads.show', $external_id);
    }

    $lead = $this->findByExternalId($external_id);
    
    // Explicitly handle contact_time with fallback
    $contactTime = $request->input('contact_time', '00:00');
    
    // Parse deadline with time component
    $deadline = \Carbon\Carbon::parse($request->deadline . ' ' . $contactTime . ':00');
    
    $lead->deadline = $deadline;
    $lead->save();
    
    event(new LeadAction($lead, self::UPDATED_DEADLINE));
    session()->flash('flash_message', __('New follow up date is set'));

    return redirect()->back();
}
```

### **Step 3.2: Fix `LeadsController::updateStatus()`**

**Find the `updateStatus()` method (around line 205) and replace it:**

```php
public function updateStatus($external_id, Request $request)
{
    if (!auth()->user()->can('lead-update-status')) {
        return response()->json([
            'message' => __("You don't have the right permission for this action")
        ], 403);
    }

    $lead = $this->findByExternalId($external_id);
    
    if (isset($request->closeLead) && $request->closeLead === true) {
        $closedStatus = Status::typeOfLead()->where('title', 'Closed')->first();
        if (!$closedStatus) {
            return response()->json(['message' => 'Closed status not found'], 404);
        }
        $lead->status_id = $closedStatus->id;
        $lead->save();
    } elseif (isset($request->openLead) && $request->openLead === true) {
        $openStatus = Status::typeOfLead()->where('title', 'Open')->first();
        if (!$openStatus) {
            return response()->json(['message' => 'Open status not found'], 404);
        }
        $lead->status_id = $openStatus->id;
        $lead->save();
    } else {
        // Validate status_id belongs to Lead
        if ($request->has('status_id')) {
            $status = Status::where('id', $request->status_id)
                           ->where('source_type', Lead::class)
                           ->first();
            
            if (!$status) {
                return response()->json(['message' => 'Invalid status for lead'], 422);
            }
        }
        
        $lead->fill($request->all())->save();
    }
    
    event(new LeadAction($lead, self::UPDATED_STATUS));
    session()->flash('flash_message', __('Lead status updated'));

    return redirect()->back();
}
```

### **Step 3.3: Scan for Offer Status Issues**

**Find files related to Offers:**
```bash
grep -r "set_offer_as_won\|set_offer_as_lost" app/Http/Controllers/ -n
```

**Check the Offer controller's status update logic matches the pattern above**

### **Step 3.4: Fix Payment Controller**

**File:** Search for `PaymentsController` and find the `addPayment` method

**Ensure decimal handling:**
```php
// Look for where payment amount is processed
// It should handle both dot and comma separators
$amount = str_replace(',', '.', $request->amount);
$payment->amount = (float) $amount;
```

**Checkpoint Test:**
```bash
./vendor/bin/phpunit tests/Unit/Controllers/Lead/
```

**Expected:** Most Lead-related tests should pass

**Record Progress:**
```bash
echo "Phase 3 Complete: $(date)" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

---

## **Phase 4: Fix Test Assertions** ⏱️ 45 minutes

**Goal:** Update all test assertions to use correct comparison methods

### **Step 4.1: Fix Carbon Date Comparisons**

**Search for all tests using `toDate()`:**
```bash
grep -r "toDate()" tests/ --include="*.php" -n
```

**For each occurrence, replace:**

```php
// BEFORE
$this->assertEquals(
    Carbon::parse('2020-08-06 15:00:00')->toDate(), 
    $lead->refresh()->deadline->toDate()
);

// AFTER
$this->assertEquals(
    Carbon::parse('2020-08-06 15:00:00')->format('Y-m-d H:i:s'),
    $lead->refresh()->deadline->format('Y-m-d H:i:s')
);
```

### **Step 4.2: Fix Time-Only Assertions**

**File:** `tests/Unit/Controllers/Lead/LeadsControllerTest.php`

**Find tests checking time components (around line 113, 134):**

```php
// Test: update_followup_stores_deadline_as_datetime_string
#[Test]
public function it_update_followup_stores_deadline_with_time_component()
{
    /* arrange */
    $lead = Lead::factory()->create();

    /* act */
    $this->json('PATCH', route('lead.followup', $lead->external_id), [
        'deadline' => '2020-08-06',
        'contact_time' => '10:30',
    ]);

    /* assert */
    $this->assertEquals(
        '10:30:00',
        $lead->refresh()->deadline->format('H:i:s')
    );
}
```

### **Step 4.3: Fix Count Assertions**

**Search for count mismatches:**
```bash
grep -r "assertCount\|assertEquals.*count\|matches expected" tests/ --include="*.php" -A 2 -B 2
```

**Review each and verify the expected count matches the actual data setup**

### **Step 4.4: Standardize All Test Methods**

**For every test file, ensure:**

1. **Uses `#[Test]` attribute:**
```php
use PHPUnit\Framework\Attributes\Test;

#[Test]
public function it_can_create_lead()
{
    // ...
}
```

2. **Remove old `/** @test */` docblocks**

3. **Uses snake_case method names starting with `it_`**

4. **Has explicit AAA structure:**
```php
#[Test]
public function it_can_update_status()
{
    /* arrange */
    $lead = Lead::factory()->create();
    $status = Status::factory()->create(['source_type' => Lead::class]);

    /* act */
    $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
        'status_id' => $status->id,
    ]);

    /* assert */
    $response->assertSuccessful();
    $this->assertEquals($status->id, $lead->refresh()->status_id);
}
```

**Checkpoint Test:**
```bash
./vendor/bin/phpunit tests/Unit/Controllers/Lead/LeadsControllerTest.php
```

**Expected:** All tests in this file should pass

**Record Progress:**
```bash
echo "Phase 4 Complete: $(date)" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

---

## **Phase 5: Fix Remaining Test-Specific Issues** ⏱️ 90 minutes

**Goal:** Address any remaining test-specific failures

### **Step 5.1: Run Full Test Suite and Categorize Failures**

```bash
./vendor/bin/phpunit --testdox > phase-5-failures.txt 2>&1
```

**Analyze output:**
```bash
# Group by test class
grep "FAILED\|ERROR" phase-5-failures.txt | cut -d':' -f1 | sort | uniq -c

# Group by error message
grep "Failed asserting\|Expected" phase-5-failures.txt | sort | uniq -c
```

### **Step 5.2: Fix Factory Issues**

**If you see "Call to undefined function factory()":**

**Find all uses:**
```bash
grep -r "factory(" tests/ --include="*.php" -n
```

**Replace with:**
```php
// BEFORE
$user = factory(User::class)->create();

// AFTER
$user = User::factory()->create();
```

### **Step 5.3: Fix Missing Relationships**

**If you see "Trying to get property of non-object":**

**Add null checks and factory relationships:**

```php
// BEFORE
$lead = Lead::factory()->create();
$client = $lead->client->company_name; // Fails if client doesn't exist

// AFTER
$client = Client::factory()->create();
$lead = Lead::factory()->create(['client_id' => $client->id]);
$clientName = $lead->client->company_name;
```

### **Step 5.4: Fix Status Type Issues**

**Ensure all Status factories specify `source_type`:**

```php
// In tests creating statuses for leads
$status = Status::factory()->create([
    'source_type' => Lead::class,
    'title' => 'Open'
]);

// In tests creating statuses for tasks
$status = Status::factory()->create([
    'source_type' => Task::class,
    'title' => 'In Progress'
]);
```

### **Step 5.5: Fix Payment Decimal Assertions**

**File:** `tests/Unit/Controllers/Payment/PaymentsControllerAddPaymentTest.php`

**Ensure decimal assertions compare floats correctly:**

```php
// BEFORE
$this->assertEquals(100.50, $payment->amount);

// AFTER
$this->assertEquals(100.50, (float) $payment->amount);
// OR
$this->assertEqualsWithDelta(100.50, $payment->amount, 0.01);
```

**Checkpoint Test:**
```bash
./vendor/bin/phpunit
```

**Expected:** <10 failures remaining

**Record Progress:**
```bash
echo "Phase 5 Complete: $(date)" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:" >> test-repair-progress.md
echo "---" >> test-repair-progress.md
```

---

## **Phase 6: Deep Dive Remaining Failures** ⏱️ 120 minutes

**Goal:** Manually fix the last stubborn failures one by one

### **Step 6.1: Generate Detailed Failure Report**

```bash
./vendor/bin/phpunit --testdox --stop-on-failure > phase-6-detail.txt 2>&1
```

**This will stop on the first failure, allowing focused debugging**

### **Step 6.2: Debug Individual Failures**

**For each remaining failure:**

1. **Read the full error message**
2. **Check the test file**
3. **Check the controller method**
4. **Check the middleware**
5. **Check the model/factory**
6. **Add debugging output if needed:**

```php
#[Test]
public function it_problematic_test()
{
    /* arrange */
    $lead = Lead::factory()->create();
    
    // Debug
    dump($lead->toArray());
    dump(auth()->user()->getAllPermissions()->pluck('name'));

    /* act */
    $response = $this->json('PATCH', route('lead.update.status', $lead->external_id), [
        'status_id' => 1,
    ]);
    
    // Debug
    dump($response->getContent());

    /* assert */
    $response->assertSuccessful();
}
```

### **Step 6.3: Create Failing Test List**

**Document each remaining failure:**

```markdown
## Remaining Failures (Phase 6)

1. **Test:** `it_can_set_offer_as_won`
   - **File:** `tests/Unit/Controllers/Offer/OffersControllerTest.php:117`
   - **Error:** Expected 'won', got 'in-progress'
   - **Root Cause:** Controller not updating status
   - **Fix:** Update OffersController::markAsWon() method
   
2. **Test:** `it_can_add_payment`
   - **File:** `tests/Unit/Controllers/Payment/PaymentsControllerAddPaymentTest.php:49`
   - **Error:** Failed asserting that true is false
   - **Root Cause:** Unclear assertion
   - **Fix:** Review test logic and clarify assertion
```

### **Step 6.4: Systematic Fixes**

**For each documented failure, apply fixes in this order:**

1. Fix the production code (controller/model/middleware)
2. Run that specific test: `./vendor/bin/phpunit --filter=it_can_set_offer_as_won`
3. If it passes, run the entire test file
4. If file passes, run full suite
5. Move to next failure

**Checkpoint Test (after each fix):**
```bash
./vendor/bin/phpunit --testdox | grep -E "Tests:|Errors:|Failures:"
```

**Record Progress (after each fix):**
```bash
echo "Fixed: it_can_set_offer_as_won - $(date)" >> test-repair-progress.md
```

---

## **Phase 7: Final Cleanup & Optimization** ⏱️ 30 minutes

**Goal:** Ensure test suite is clean, fast, and maintainable

### **Step 7.1: Remove Debug Code**

```bash
# Find all dump/dd statements in tests
grep -r "dump(\|dd(\|var_dump\|print_r" tests/ --include="*.php" -n

# Remove them all
```

### **Step 7.2: Standardize All Test Structure**

**Run this verification:**
```bash
# Check all tests use #[Test]
grep -r "function it_" tests/ --include="*.php" -B 1 | grep -v "#\[Test\]" | grep -v "^--$"

# If any results, add #[Test] attribute
```

### **Step 7.3: Add Test Groups**

**Add groups to all tests for easier filtering:**

```php
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Test]
#[Group('lead')]
#[Group('crud')]
public function it_can_create_lead()
{
    // ...
}
```

**Useful groups:**
- `crud` - Create/Read/Update/Delete operations
- `permissions` - Permission-related tests
- `validation` - Input validation tests
- `lead`, `offer`, `task`, `payment` - Feature groups

### **Step 7.4: Update phpunit.xml for Performance**

**File:** `phpunit.xml`

**Add:**
```xml
<phpunit 
    backupGlobals="false"
    bootstrap="vendor/autoload.php"
    colors="true"
    processIsolation="false"
    stopOnFailure="false"
    cacheDirectory=".phpunit.cache"
    beStrictAboutOutputDuringTests="true"
    beStrictAboutTodoAnnotatedTests="true"
    failOnRisky="true"
    failOnWarning="true">
    
    <!-- ... existing config ... -->
    
    <coverage>
        <report>
            <html outputDirectory="coverage-html"/>
        </report>
    </coverage>
</phpunit>
```

### **Step 7.5: Create Test Documentation**

**File:** `tests/README.md`

```markdown
# Test Suite Documentation

## Running Tests

### Full Suite
```bash
./vendor/bin/phpunit
```

### Specific Group
```bash
./vendor/bin/phpunit --group=lead
./vendor/bin/phpunit --group=crud
```

### Specific Test
```bash
./vendor/bin/phpunit --filter=it_can_create_lead
```

## Test Structure

All tests follow:
- AAA pattern (Arrange, Act, Assert)
- Snake_case method names starting with `it_`
- `#[Test]` attribute
- `#[Group]` attributes for categorization

## Permissions

Test user (admin@test.local) has all permissions via Super Admin role.

## Database

Tests use `daybyday_test` database.
Each test wrapped in DatabaseTransactions for isolation.
```

### **Step 7.6: Final Full Test Run**

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Fresh migration
php artisan migrate:fresh --seed --env=testing

# Run all tests
./vendor/bin/phpunit --testdox
```

**Expected Output:**
```
Tests: 108, Assertions: XXX, Errors: 0, Failures: 0
```

**Record Final State:**
```bash
echo "==================================" >> test-repair-progress.md
echo "Phase 7 Complete (FINAL): $(date)" >> test-repair-progress.md
./vendor/bin/phpunit --testdox | grep -E "Tests:|Assertions:|Errors:|Failures:" >> test-repair-progress.md
echo "==================================" >> test-repair-progress.md
```

---

## **Success Criteria**

✅ **Phase 1:** Migrations run without errors  
✅ **Phase 2:** All permission-related 403 errors gone  
✅ **Phase 3:** All datetime comparison tests pass  
✅ **Phase 4:** All assertion format issues resolved  
✅ **Phase 5:** <10 failures remaining  
✅ **Phase 6:** 0 failures  
✅ **Phase 7:** Clean, documented, fast test suite  

---

## **Timeline Summary**

| Phase | Time | Cumulative | Goal |
|-------|------|------------|------|
| 0 | 5 min | 5 min | Baseline metrics |
| 1 | 30 min | 35 min | Migrations fixed |
| 2 | 45 min | 80 min | Permissions fixed |
| 3 | 60 min | 140 min | Controller logic fixed |
| 4 | 45 min | 185 min | Test assertions fixed |
| 5 | 90 min | 275 min | <10 failures |
| 6 | 120 min | 395 min | 0 failures |
| 7 | 30 min | 425 min | Production-ready |

**Total Estimated Time:** ~7 hours (can be split over multiple sessions)

---

## **Rollback Plan**

If a phase makes things worse:

```bash
# Revert to previous commit
git reset --hard HEAD~1

# Check progress file
cat test-repair-progress.md

# Re-run from last successful phase
```

---

## **Maintenance After Completion**

1. **CI/CD:** Ensure GitHub Actions runs tests on every push
2. **Pre-commit Hook:** Run tests before allowing commits
3. **Coverage:** Aim for >80% code coverage
4. **Documentation:** Keep `tests/README.md` updated

---

**This plan is production-grade, systematic, and leaves no room for assumptions. Follow it sequentially, record progress after each phase, and you'll have a fully functional test suite.**
