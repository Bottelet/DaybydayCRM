# Refactoring Opportunities

This document tracks refactoring opportunities discovered during test development and code review.

## Client Number Service Issues

### 1. Lack of Validation for Client Numbers

**Priority:** High  
**Discovered in:** `tests/Unit/Client/ClientNumberServiceTest.php`  
**Issue:** The `ClientNumberService::setClientNumber()` method accepts any integer value, including:
- Negative numbers (e.g., -100)
- Zero (which can lead to duplicate numbers)
- No validation or business logic constraints

**Impact:**
- Setting client number to 0 causes the first client to get 0, second gets 1 (potential confusion)
- Negative numbers create invalid sequences: -100, -99, -98, etc.
- No protection against accidental data corruption

**Proposed Solution:**
```php
// In ClientNumberService.php
public function setClientNumber(int $clientNumber): bool
{
    // Add validation
    if ($clientNumber < 1) {
        throw new InvalidArgumentException('Client number must be a positive integer');
    }
    
    $this->lockedSetting->client_number = $clientNumber;
    return $this->lockedSetting->save();
}
```

**Alternative:** Use a `ClientNumberValidator` class to centralize validation rules.

**Files to Update:**
- `app/Services/ClientNumber/ClientNumberService.php`
- `tests/Unit/Client/ClientNumberServiceTest.php` (update failure tests to expect exceptions)

---

## Test Standardization

### 2. Test Method Naming Convention

**Priority:** Medium  
**Discovered in:** Code review  
**Issue:** Test methods use various naming patterns (snake_case without prefix, camelCase, etc.)

**Proposed Standard:**
- All test methods should start with `it_`
- Method names should be grammatically correct sentences
- Example: `it_prevents_negative_client_numbers()`, `it_increments_from_starting_value()`

**Benefits:**
- Improves readability
- Test names read as specifications
- Consistent with BDD/specification-style testing

**Files to Update:**
- All test files (89 total, 48 already refactored)

### 3. Add Test Metadata Attributes

**Priority:** Medium  
**Issue:** Test classes lack proper PHPUnit attributes for coverage and traceability

**Proposed Standard:**
```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ClientNumberService::class)]
#[UsesClass(ClientNumberConfig::class)]
class ClientNumberServiceTest extends AbstractTestCase
{
    // ...
}
```

**Benefits:**
- Better IDE integration (jump to test from class)
- Code coverage reporting
- Clear documentation of what's being tested

**Files to Update:**
- All test files

### 4. PHPStorm Region Syntax

**Priority:** Low  
**Issue:** Current regions use `//region` and `//endregion`  
**Preferred:** Use `#region` and `#endregion` for better PHPStorm integration

**Example:**
```php
#region happy_path

public function it_sets_next_client_number()
{
    // test code
}

#endregion
```

**Files to Update:**
- All refactored test files (48 files)
- All model files with relationship regions (22 files)

---

## Documentation Improvements

### 5. Enhance .junie/*.md Documentation

**Priority:** Low  
**Current State:** Basic analysis documents exist
**Proposed:** Expand with:
- Common refactoring patterns
- Test writing guidelines
- Code quality standards
- Examples of good vs bad patterns

**Files to Update:**
- `.junie/refactor_plan.md`
- `.junie/error_repair_plan.md`
- Add new `.junie/testing_guidelines.md`

### 6. Improve Agent Instructions

**Priority:** Low  
**Files to Enhance:**
- `.github/copilot-instructions.md` - Add test naming conventions
- `AGENTS.md` - Add refactoring section with discovered patterns

---

## Tracking

| ID | Priority | Status | Assigned | Estimated Effort |
|----|----------|--------|----------|-----------------|
| 1  | High     | Open   | -        | 2 hours         |
| 2  | Medium   | Open   | -        | 8 hours         |
| 3  | Medium   | Open   | -        | 4 hours         |
| 4  | Low      | Open   | -        | 2 hours         |
| 5  | Low      | Open   | -        | 3 hours         |
| 6  | Low      | Open   | -        | 1 hour          |
