# Test Suite Refactor - Quick Reference Guide

## For Developers Working on Tests

### ✅ New Features Available

#### 1. Test Authorization Helpers
No more manually attaching roles in every test!

```php
// OLD WAY ❌
public function test_something() {
    $this->user->attachRole(Role::whereName('owner')->first());
    // test code...
}

// NEW WAY ✅
public function test_something() {
    $this->asOwner();
    // test code...
}

// Also available
$this->asAdmin();
```

#### 2. Date Comparison Helper
Stop worrying about date formats!

```php
// OLD WAY ❌ - Brittle, fails on format differences
$this->assertEquals($model->created_at, $json['created_at']);

// NEW WAY ✅ - Format agnostic
$this->assertDatesEqual($model->created_at, $json['created_at']);
```

#### 3. Automatic UUID Generation
Models with `external_id` now auto-generate UUIDs!

```php
// OLD WAY ❌ - Manual UUID in every factory/test
$client = factory(Client::class)->create([
    'external_id' => Str::uuid(),
    // other fields...
]);

// NEW WAY ✅ - Automatic via HasExternalId trait
$client = factory(Client::class)->create([
    // external_id generated automatically
    // other fields...
]);
```

### 🚫 Test Isolation Rules (CRITICAL)

#### Rule #1: One HTTP Request Per Test
```php
// BAD ❌
public function test_feature() {
    $this->get('/setup');  // Side effect
    $response = $this->post('/action');  // Actual test
}

// GOOD ✅
public function test_feature() {
    // Set up state explicitly, not via HTTP
    session(['key' => 'value']);
    $response = $this->post('/action');
}
```

#### Rule #2: Create Your Own Test Data
```php
// BAD ❌ - Depends on seeder or another test
public function test_update_client() {
    $client = Client::first();  // What if none exist?
}

// GOOD ✅
public function test_update_client() {
    $client = factory(Client::class)->create();
}
```

#### Rule #3: One Behavior Per Test
```php
// BAD ❌
public function test_payment_workflow() {
    $this->post('/create', [...]);
    $this->post('/update', [...]);
    $this->get('/list');
}

// GOOD ✅ - Split into 3 tests
public function test_can_create_payment() {
    $response = $this->post('/create', [...]);
    $response->assertStatus(201);
}

public function test_can_update_payment() {
    $payment = factory(Payment::class)->create();
    $response = $this->post('/update', [...]);
    $response->assertOk();
}

public function test_can_list_payments() {
    Payment::factory()->count(3)->create();
    $response = $this->get('/list');
    $response->assertJsonCount(3);
}
```

### 📋 Test Writing Checklist

Before committing a test, verify:

- [ ] Does this test create its own data?
- [ ] Does this test make only ONE HTTP request?
- [ ] Can this test run alone? (in any order?)
- [ ] Does this test clean up after itself?
- [ ] Am I using the new helper methods where appropriate?
- [ ] Are date comparisons using assertDatesEqual()?
- [ ] Do I need to manually set external_id? (probably not!)

### 🔧 Models with HasExternalId Trait

These models automatically generate external_id:
- Absence
- Activity
- Appointment
- Client
- Contact
- Department
- Document
- Invoice
- InvoiceLine
- Lead
- Offer
- Payment
- Permission
- Product
- Project
- Role
- Task
- User

**You don't need to set external_id when creating these models!**

### 🐛 Common Issues & Solutions

#### Issue: "Field 'external_id' doesn't have a default value"
**Solution:** Model is missing HasExternalId trait. Add it:
```php
use App\Traits\HasExternalId;

class YourModel extends Model {
    use HasExternalId;
}
```

#### Issue: Date comparison failing
**Solution:** Use the new helper:
```php
$this->assertDatesEqual($expected, $actual);
```

#### Issue: Test fails when run alone but passes in suite
**Solution:** Test has isolation problem. Check:
1. Are you creating your own test data?
2. Are you making multiple HTTP requests?
3. Are you depending on another test's side effects?

### 📚 Additional Resources

- **Full Refactor Plan:** `.github/refactor_plan.md`
- **Status Tracking:** `.github/REFACTOR_STATUS.md`
- **Test Isolation Guide:** `.github/test_isolation_refactor.md`
- **AGENTS.md:** Architecture and conventions

### ⚠️ Important Notes

1. **DatabaseTransactions trait** is used in most tests - this rolls back changes after each test
2. **TestCase already creates an owner user** - available as `$this->user`
3. **Don't use migrate:fresh in individual tests** - TestCase handles DB setup
4. **Always use factories** for test data creation when available

### 🎯 Best Practices

1. **Test names should describe behavior**
   ```php
   // ✅ Good
   public function test_admin_can_delete_inactive_users()
   
   // ❌ Bad
   public function test_delete()
   ```

2. **Use arrange-act-assert pattern**
   ```php
   public function test_something() {
       // Arrange
       $user = factory(User::class)->create();
       
       // Act
       $response = $this->post('/action', [...]);
       
       // Assert
       $response->assertOk();
   }
   ```

3. **Keep tests focused and simple**
   - One test = one behavior
   - One assertion per test (when possible)
   - Clear setup and expectations

4. **Use descriptive variable names**
   ```php
   // ✅ Good
   $adminUser = factory(User::class)->create();
   $unpaidInvoice = factory(Invoice::class)->create(['status' => 'unpaid']);
   
   // ❌ Bad
   $u = factory(User::class)->create();
   $i = factory(Invoice::class)->create();
   ```

---

**Questions?** Check `.github/REFACTOR_STATUS.md` for detailed information or refer to the examples in existing tests.
