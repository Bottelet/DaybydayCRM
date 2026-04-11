# DaybydayCRM — AI Agent Instructions

## Documentation Overview
Refer to the following core documentation for detailed guidance:
- **[AGENTS.md](../AGENTS.md):** High-level system architecture, modular design, and domain organization.
- **[.github/ARCHITECTURE.md](ARCHITECTURE.md):** Deep dive into technical debt, model behavior (Traits/Observers), and service layer.
- **[.github/TESTING.md](TESTING.md):** Critical test isolation rules, normalization, and common fix patterns.
- **[.github/ROADMAP.md](ROADMAP.md):** Project modernization status and refactoring goals.

---

## Critical Development Guidelines

### 1. Test Isolation (MANDATORY)
Tests must be **self-contained**. The "Cascade Problem" (tests depending on side effects of other tests) is prohibited.
- Create own data via factories.
- Use `RefreshDatabase` or `DatabaseTransactions`.
- Exactly one HTTP request per test (unless testing a workflow sequence).
- Normalize Carbon objects to ISO strings (`toISOString()`) before comparison.

### 2. Business Logic Location
- **Actions:** Encapsulate single-purpose logic in `app/Actions/{Domain}/{ActionName}Action.php`.
- **Services:** Complex workflows belong in `app/Services/`.
- **Controllers:** Must remain thin, delegating to Services/Actions.

### 3. Model Consistency
- Use **HasExternalId** trait for UUID routing.
- Use **Blameable** trait for automatic tracking of `user_created_id`.
- Use **Statusable** trait for standardized status handling.
- Use **Observers** for side effects (e.g., file deletion on record delete).

### 4. Code Standards
- **Routing:** Prefer tuple-based syntax `[Controller::class, 'method']`.
- **Currency:** Normalize inputs in `prepareForValidation()` of FormRequests.
- **Exceptions:** Throw specific types like `InvalidArgumentException` instead of generic `Exception`.

---

## Quick Reference: Common Fixes
- **General error 1364 (Field 'X' doesn't have default):** Ensure `HasExternalId` is used or update factory.
- **Duplicate entry 1062:** Always call `$user->fresh()` after attaching permissions and before `actingAs($user)`.
- **403 Forbidden in tests:** Use `asOwner()` or `asAdmin()` helpers in `TestCase`.
- **VAT/Tax calculation errors:** VAT stored as `percentage × 100` (e.g., 2100 for 21%), requires division by 10000 to get decimal rate.
- **Status validation failures:** Use full class names (`Task::class`) not strings (`'task'`) in `source_type` field.
- **Expected 302 got 200/403:** Check if test uses JSON requests (`$this->json()`) - they return different status codes than web requests.
- **Null pointer in trait methods:** Add null checks before accessing optional properties (e.g., `$this->deadline` in DeadlineTrait).
- **Document view/download failures in tests:** Storage services need to return fake content in testing environment.

