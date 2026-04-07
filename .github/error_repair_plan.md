# Error Repair Plan - DaybydayCRM Tests

This plan addresses the common errors and failures found in the PHPUnit tests for the DaybydayCRM project.

## Common Error Patterns

1.  **Missing Default Values in Database (SQLSTATE[HY000]: General error: 1364 Field 'X' doesn't have a default value):**
    *   **Cause:** Several models (`Appointment`, `Offer`, `Activity`) have fields that do not have a default value in the database and are not automatically populated by factories or model boot methods.
    *   **Solution:** 
        *   Update factories to include default values for these fields.
        *   For models that require UUIDs (`external_id`), ensure a boot method or trait is used to auto-generate them on creation.
        *   In tests, explicitly provide required fields when using `create()` or `factory()`.

2.  **Property or Method Access on Null:**
    *   **Cause:** Tests often assume a relationship exists (e.g., `$client->primaryContact`) when it hasn't been created or correctly associated in the test setup.
    *   **Solution:** 
        *   Ensure all necessary related models are created and associated in the test `setUp()` or before the assertion.
        *   Use `factory(Model::class)->create([...])` with explicit foreign keys.

3.  **PHPUnit 10 Incompatibilities:**
    *   **Cause:** Use of removed assertions like `assertObjectHasAttribute`.
    *   **Solution:** Replace with `assertTrue(property_exists($object, 'propertyName'))`.

4.  **Date/Time Comparison Failures:**
    *   **Cause:** Comparing `Carbon` objects or strings using `toDate()` which might return a `DateTime` object or a string depending on the version/context, leading to "Call to a member function toDate() on string".
    *   **Solution:** Use `toDateString()` for consistent string-based date comparison.

5.  **Permission/Authorization Failures (403 vs Expected 422/302/200):**
    *   **Cause:** Tests run as a user without the necessary roles or permissions defined in the `authorize()` method of FormRequests or in controllers.
    *   **Solution:** Assign the necessary roles (e.g., 'owner' or 'administrator') to the test user in the `setUp()` method using `$this->user->attachRole($role)`.

## Repair Steps Taken

1.  **Appointment Tests:** Added 'color' field to `factory(Appointment::class)->create()` calls.
2.  **Client Tests:** Ensured `is_primary => true` for contacts created during tests to fix `primaryContact` being null.
3.  **Lead/Offer Tests:** Added `status` to `Offer::create()` calls in test setup.
4.  **Observer Tests:** Added a `boot` method to the `Activity` model to auto-generate UUID `external_id`.
5.  **Enum/Source Tests:** Replaced `assertObjectHasAttribute` with `property_exists` checks.
6.  **Payment Tests:** Assigned the 'owner' role to the test user to bypass permission checks.
7.  **Deadline Tests:** Changed `toDate()` to `toDateString()` for date comparisons.
