# Test Isolation Guide

## Overview

This guide explains the test isolation improvements made to the DaybydayCRM test suite to prevent cascade failures and ensure tests can run independently.

## What is Test Isolation?

Test isolation means each test can run independently without relying on side effects from other tests. Proper isolation ensures:
- Tests can run in any order
- Tests can be run individually or as part of a suite
- Disabling one test doesn't break others
- Test results are predictable and reproducible

## Common Anti-Patterns (Avoid These!)

### 1. Unrelated HTTP Requests in Tests

**BAD:**
```php
public function test_validation_error()
{
    // WHY make this unrelated GET request?
    $this->actingAs($this->user)->get('/client/create');
    
    $response = $this->json('POST', route('payment.add', $invoice->id), [
        'amount' => 'invalid',
    ]);
    
    $response->assertStatus(422);
}
```

**GOOD:**
```php
public function test_validation_error()
{
    // Only test what you're actually testing
    $response = $this->json('POST', route('payment.add', $invoice->id), [
        'amount' => 'invalid',
    ]);
    
    $response->assertStatus(422);
}
```

### 2. Multiple HTTP Requests in One Test

**BAD:**
```php
public function test_payment_formats()
{
    // First request
    $this->json('POST', route('payment.add'), ['amount' => '5000,234']);
    
    // Second request depends on first!
    $response = $this->json('POST', route('payment.add'), ['amount' => '5000.234']);
    
    $this->assertFalse($invoice->refresh()->payments->isEmpty());
}
```

**GOOD:**
```php
public function test_payment_with_comma_separator()
{
    $response = $this->json('POST', route('payment.add'), ['amount' => '5000,234']);
    
    $this->assertFalse($invoice->refresh()->payments->isEmpty());
}

public function test_payment_with_dot_separator()
{
    $response = $this->json('POST', route('payment.add'), ['amount' => '5000.234']);
    
    $this->assertFalse($invoice->refresh()->payments->isEmpty());
}
```

## Best Practices

### 1. Use DatabaseTransactions

Always use `DatabaseTransactions` trait to ensure database state is rolled back after each test:

```php
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MyTest extends TestCase
{
    use DatabaseTransactions;
    
    // Your tests here
}
```

### 2. Set Up All Required Data in setUp() or Individual Tests

Don't rely on data from other tests or seeders:

```php
protected function setUp(): void
{
    parent::setUp();
    
    // Create all necessary data for this test class
    $this->invoice = factory(Invoice::class)->create([
        'status' => 'unpaid',
    ]);
}
```

### 3. Use Factories Instead of Direct Database Inserts

Factories handle relationships and required fields automatically:

```php
// GOOD
$client = factory(Client::class)->create();

// Also acceptable with specific overrides
$client = factory(Client::class)->create(['vat' => '12345678']);
```

### 4. Test One Thing Per Test

Each test should verify one behavior:

```php
// GOOD - Clear, focused test
public function test_can_create_payment()
{
    $response = $this->json('POST', route('payment.add'), $validData);
    
    $response->assertStatus(302);
    $this->assertNotNull(Payment::first());
}

// GOOD - Another focused test
public function test_payment_validates_amount()
{
    $response = $this->json('POST', route('payment.add'), ['amount' => 'invalid']);
    
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('amount');
}
```

### 5. Explicit Session/State Setup

If your test needs specific session data, set it explicitly:

```php
public function test_requires_session_data()
{
    session(['key' => 'value']);
    
    $response = $this->get('/some-route');
    
    // assertions
}
```

## Test Isolation Checklist

Before committing a test, verify:

- [ ] Does this test make any HTTP requests not directly related to what it's testing?
- [ ] Does this test depend on database state from other tests?
- [ ] Does this test depend on session state from other tests?
- [ ] Can this test run in isolation (first, last, or alone)?
- [ ] Does this test use DatabaseTransactions or similar cleanup?
- [ ] Does this test create all its own required data?

## Tools and Helpers

### HasExternalId Trait

The `HasExternalId` trait automatically generates UUIDs for models with an `external_id` field:

```php
// No need to manually set external_id in factories or tests
$client = factory(Client::class)->create();
// external_id is automatically generated!
```

### Test Authorization Helpers

Use the authorization helpers in TestCase:

```php
public function test_owner_can_delete_user()
{
    $this->asOwner();
    
    $response = $this->delete(route('users.destroy', $user->id));
    
    $response->assertStatus(200);
}

public function test_admin_can_view_settings()
{
    $this->asAdmin();
    
    $response = $this->get(route('settings.index'));
    
    $response->assertStatus(200);
}
```

### Date Comparison Helper

Use `assertDatesEqual` for comparing dates:

```php
public function test_deadline_is_set()
{
    $task = factory(Task::class)->create(['deadline' => '2024-01-01']);
    
    $this->assertDatesEqual('2024-01-01', $task->deadline);
}
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Unit/Controllers/Payment/PaymentsControllerAddPaymentTest.php

# Run a specific test
php artisan test --filter=test_can_add_payment

# Run tests in random order to verify isolation
php artisan test --order-by=random
```

## Troubleshooting

### Test passes in suite but fails alone

This usually means the test depends on side effects from another test. Check:
1. Is setUp() creating all necessary data?
2. Are you using DatabaseTransactions?
3. Is the test making unrelated HTTP requests?

### Test fails in suite but passes alone

This usually means another test is leaving behind state. Check:
1. Are other tests using DatabaseTransactions?
2. Are other tests properly cleaning up?
3. Are you sharing class properties between tests improperly?

### Tests fail when run in different order

This is a clear sign of poor test isolation. Review all the anti-patterns above.

## References

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Best Practices](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
- Repository test isolation improvements: See commits with "test isolation" in message
