# Error Repair Guidelines

Refer to **[.github/TESTING.md](../.github/TESTING.md)** for detailed isolation standards and common fix patterns.

## Quick Fix Summary
- **SQLSTATE 1364 (Missing Default):** Ensure model uses `HasExternalId` or update the factory.
- **SQLSTATE 1062 (Duplicate Entry):** Flush cache and reload user (`$user->fresh()`) after role/permission changes.
- **Member function on null:** Ensure related models are correctly setup in test (e.g., `primaryContact`).
- **PHPUnit 10 Compatibility:** Use attributes (`#[Test]`, `#[Group]`) and native PHP property checks.

## Junie's Workflow
1. Add `#[Group('junie_repaired')]` attribute.
2. Fix the error/failure following the isolation rules.
3. Verify the fix in isolation.
4. Document the fix in the session summary.
