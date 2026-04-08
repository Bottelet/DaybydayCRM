# Refactor Plan - DaybydayCRM Tests

This plan proposes refactorings to make the test suite more robust and easier to maintain.

## 1. Unified Model Creation Trait
*   **Problem:** Standard models (Appointment, Activity, etc.) often fail because required fields like `external_id` (UUID) or default attributes are not automatically generated.
*   **Solution:** Create a trait `HasTestFactory` that provides a standard `createForTest` method that handles common setup.
*   **Implementation:**
    ```php
    trait HasTestFactory {
        public static function bootHasTestFactory() {
            static::creating(function ($model) {
                if (empty($model->external_id) && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'external_id')) {
                    $model->external_id = (string) Str::uuid();
                }
            });
        }
    }
    ```

## 2. Test Authorization Helper
*   **Problem:** Many controller tests fail because the test user lacks roles/permissions.
*   **Solution:** Add a `asOwner()` and `asAdmin()` method to `TestCase.php`.
*   **Implementation:**
    ```php
    public function asOwner() {
        $this->user->attachRole(Role::whereName('owner')->first());
        return $this;
    }
    ```

## 3. Enforce Test Isolation (CRITICAL - Addresses Cascade Problem)
*   **Problem:** Tests rely on side effects from other tests or make unrelated HTTP requests to set up state.
    - Example: `adding_wrong_amount_parameter_return_error()` calls `GET /client/create` before testing payment validation
    - Example: `can_add_negative_payment_with_separator()` makes TWO payment requests in one test
    - When a test with side effects is disabled (`markTestIncomplete()`), other tests depending on those side effects mysteriously fail
*   **Root Cause:** Poor test isolation - tests share state through database, session, or HTTP request side effects
*   **Solution:** Implement strict test isolation rules and refactor violating tests
*   **Implementation:**
    1. **Audit all tests** for unrelated HTTP requests:
       ```bash
       # Find tests making multiple HTTP requests
       grep -r "->get\|->post\|->put\|->patch\|->delete" tests/ | grep -v "response ="
       ```
    2. **Refactor payment tests** to remove dependency on `/client/create`:
       ```php
       // BEFORE (BAD - relies on side effect from GET /client/create)
       public function adding_wrong_amount_parameter_return_error() {
           $this->actingAs($this->user)->get('/client/create'); // WHY?
           $response = $this->json('POST', route('payment.add', ...));
           $response->assertStatus(422);
       }
       
       // AFTER (GOOD - explicit setup)
       public function adding_wrong_amount_parameter_return_error() {
           // If the route needs specific session data, set it explicitly:
           // session(['key' => 'value']);
           $response = $this->json('POST', route('payment.add', ...));
           $response->assertStatus(422);
       }
       ```
    3. **Split multi-request tests** into separate tests:
       ```php
       // BEFORE (BAD - two related requests in one test)
       public function can_add_negative_payment_with_separator() {
           $this->json('POST', route('payment.add', ...), ['amount' => -5000, 234]);
           $response = $this->json('POST', route('payment.add', ...), ['amount' => -5000.234]);
           // Second request depends on first!
       }
       
       // AFTER (GOOD - one assertion per test)
       public function can_add_negative_payment_with_comma_separator() {
           $response = $this->json('POST', route('payment.add', ...), ['amount' => -5000, 234]);
           $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
       }
       
       public function can_add_negative_payment_with_dot_separator() {
           $response = $this->json('POST', route('payment.add', ...), ['amount' => -5000.234]);
           $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
       }
       ```
    4. **Add test isolation check** to base TestCase:
       ```php
       protected function setUp(): void {
           parent::setUp();
           DB::beginTransaction(); // Explicit transaction control
       }
       
       protected function tearDown(): void {
           DB::rollBack(); // Ensure cleanup
           parent::tearDown();
       }
       ```
    5. **Code review checklist:**
       - [ ] Does this test make any HTTP requests not directly related to what it's testing?
       - [ ] Does this test depend on database state from other tests?
       - [ ] Does this test depend on session state from other tests?
       - [ ] Can this test run in isolation (first, last, or alone)?
*   **Priority:** HIGH - This is the root cause of "cascade failures" when tests are disabled
*   **Impact:** Prevents mysterious test failures when other tests are marked incomplete

## 4. Standardize Date Comparisons
*   **Problem:** Mix of `toDate()`, `toDateString()`, and `Carbon` objects in assertions.
*   **Solution:** Create custom assertions in `TestCase.php` to compare dates accurately.
*   **Implementation:**
    ```php
    public function assertDatesEqual($expected, $actual, $message = '') {
        $this->assertEquals(
            Carbon::parse($expected)->toDateTimeString(),
            Carbon::parse($actual)->toDateTimeString(),
            $message
        );
    }
    ```

## 5. Fix Legacy Model Factories
*   **Problem:** Some factories are missing required fields or use outdated syntax.
*   **Solution:** Review all files in `database/factories/` and ensure they include all fields that don't have database defaults.
*   **Priority:** `AppointmentFactory.php`, `ActivityFactory.php`, `OfferFactory.php`.

## 6. Mock Problematic Guards
*   **Problem:** Tests fail when environment-specific drivers (like `passport`) are missing.
*   **Solution:** In `TestCase::setUp()`, mock or swap drivers for testing.
*   **Implementation:**
    ```php
    Config::set('auth.guards.api.driver', 'session');
    ```
