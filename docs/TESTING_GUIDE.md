# Test Writing Guidelines

## Quick Reference for DaybydayCRM Tests

### Modern Test Template

```php
<?php

namespace Tests\Unit\Controllers\Example;

use App\Models\Example;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleControllerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_example()
    {
        /* arrange */
        $data = ['name' => 'Test'];

        /* act */
        $response = $this->json('POST', route('examples.store'), $data);

        /* assert */
        $response->assertSuccessful();
    }
}
```

### Key Patterns

✅ **DO**: Use modern factory syntax
```php
$user = User::factory()->create();
```

❌ **DON'T**: Use old factory syntax
```php
$user = factory(User::class)->create();  // OUTDATED
```

✅ **DO**: Use DatabaseTransactions
```php
use DatabaseTransactions;
```

❌ **DON'T**: Use WithoutMiddleware
```php
use WithoutMiddleware;  // AVOID
```

✅ **DO**: Use #[Test] attributes
```php
#[Test]
public function it_can_do_something()
```

❌ **DON'T**: Use old annotations
```php
/** @test */  // OUTDATED
```

### Available Helpers

```php
$this->user;              // Authenticated user with owner role
$this->getUser();         // Get authenticated user
$this->setUser($user);    // Change authenticated user
$this->assertDatesEqual($expected, $actual);  // Compare dates
```

### Running Tests

```bash
./vendor/bin/phpunit                    # All tests
./vendor/bin/phpunit --filter=test_name # Specific test
./vendor/bin/phpunit --group=lead       # Group of tests
```

See existing tests for more examples.
