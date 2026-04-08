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

## 3. Standardize Date Comparisons
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

## 4. Fix Legacy Model Factories
*   **Problem:** Some factories are missing required fields or use outdated syntax.
*   **Solution:** Review all files in `database/factories/` and ensure they include all fields that don't have database defaults.
*   **Priority:** `AppointmentFactory.php`, `ActivityFactory.php`, `OfferFactory.php`.

## 5. Mock Problematic Guards
*   **Problem:** Tests fail when environment-specific drivers (like `passport`) are missing.
*   **Solution:** In `TestCase::setUp()`, mock or swap drivers for testing.
*   **Implementation:**
    ```php
    Config::set('auth.guards.api.driver', 'session');
    ```
