# Error Repair Prompt - DaybydayCRM Tests

This prompt explains how to fix common errors in the DaybydayCRM test suite.

## The Goal
Fix existing PHPUnit tests that fail with specific error patterns.

## 1. Missing Database Field (Field 'X' doesn't have a default value)
*   **Error:** `SQLSTATE[HY000]: General error: 1364 Field 'color' doesn't have a default value`
*   **Fix:** Ensure the field is provided during creation in the test or factory.
*   **Action:**
    ```php
    // Before:
    factory(Appointment::class)->create(['title' => 'test']);
    
    // After:
    factory(Appointment::class)->create(['title' => 'test', 'color' => '#FFFFFF']);
    ```

## 2. Accessing Property on Null (Attempt to read property "X" on null)
*   **Error:** `ErrorException: Attempt to read property "vat" on null`
*   **Fix:** Ensure relationships exist and are correctly linked.
*   **Action:**
    ```php
    // Before:
    $contact = factory(Contact::class)->create(['client_id' => $client->id]);
    
    // After:
    $contact = factory(Contact::class)->create(['client_id' => $client->id, 'is_primary' => true]);
    ```

## 3. Comparing Dates (toDate() on string)
*   **Error:** `Error: Call to a member function toDate() on string`
*   **Fix:** Use `toDateString()` for string-based date comparisons.
*   **Action:**
    ```php
    // Before:
    $this->assertEquals(Carbon::parse($date)->toDate(), $model->date->toDate());
    
    // After:
    $this->assertEquals(Carbon::parse($date)->toDateString(), $model->date->toDateString());
    ```

## 4. Missing Permission (Expected 422/302 but received 403)
*   **Error:** `Expected response status code [422] but received 403.`
*   **Fix:** Attach a powerful role like 'owner' or 'administrator' to the test user in `setUp()`.
*   **Action:**
    ```php
    // In setUp():
    $this->user->attachRole(Role::whereName('owner')->first());
    ```

## 5. Attribute Assertion Removal (PHPUnit 10+)
*   **Error:** `Error: Call to undefined method assertObjectHasAttribute()`
*   **Fix:** Replace with `assertTrue(property_exists($object, 'attributeName'))`.
*   **Action:**
    ```php
    // Before:
    $this->assertObjectHasAttribute('status', $enum);
    
    // After:
    $this->assertTrue(property_exists($enum, 'status'));
    ```
