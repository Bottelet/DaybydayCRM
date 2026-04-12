# Error Repair Guidelines

Refer to **[.github/TESTING.md](../.github/TESTING.md)** for detailed isolation standards and common fix patterns.

## Quick Fix Summary
- **SQLSTATE 1364 (Missing Default):** Ensure model uses `HasExternalId` or update the factory.
- **SQLSTATE 1062 (Duplicate Entry):** Flush cache and reload user (`$user->fresh()`) after role/permission changes.
- **Member function on null:** Ensure related models are correctly setup in test (e.g., `primaryContact`).
- **PHPUnit 10 Compatibility:** Use attributes (`#[Test]`, `#[Group]`) and native PHP property checks.
- **VAT/Tax Calculation Errors:** Check for double division - VAT stored as `percentage × 100`, divide by 10000 not 100.
- **Expected 302 got 200/403:** JSON requests return different status codes (200/403) vs web (302).
- **Status Validation Failures:** Use full class names (`Task::class`) not strings (`'task'`) for `source_type`.
- **Null Trait Methods:** Add null checks before accessing optional properties in traits (e.g., DeadlineTrait).
- **Storage/File Tests:** Storage services need test doubles returning fake content in testing environment.

## Junie's Workflow
1. Add `#[Group('junie_repaired')]` attribute.
2. Fix the error/failure following the isolation rules.
3. Verify the fix in isolation.
4. Document the fix in the session summary.
