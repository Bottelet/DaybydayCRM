# `todo.md`

## ✅ Completed Refactoring Patterns (Updated 2026-04-11)

### 0. Critical Bug Fixes (2026-04-11)
**Status:** ✅ COMPLETE

#### Bug Fix 1: isClosed() Methods in Lead and Task Models
**Problem:** Methods were comparing status relationship object to string constant
**Root Cause:** `$this->status` returns a Status model, not a string
**Fix:** Updated to check `$this->status->title` like Project model does
**Files Modified:**
- `app/Models/Lead.php` - Fixed `isClosed()` method
- `app/Models/Task.php` - Fixed `isClosed()` method
**Impact:** DeadlineTrait::isOverDeadline() now works correctly
**Pattern:** Always access relationship properties, don't compare object to string

#### Bug Fix 2: VAT Calculation Double Division
**Problem:** Invoice totals calculated incorrectly (e.g., 100.21 instead of 121.00 for 21% VAT)
**Root Cause:** `integerToVatRate()` divided percentage by 100 twice
**Calculation Flow:**
- Setting stores VAT as integer (e.g., 21)
- `percentage()` converts to decimal: 21 / 100 = 0.21
- `integerToVatRate()` was dividing again: 0.21 / 100 = 0.0021 ❌
- Should be: 0.21 (already correct) ✅
**Fix:** Removed double division in `integerToVatRate()`
**Files Modified:**
- `app/Repositories/Tax/Tax.php` - Fixed `integerToVatRate()` method
**Impact:** All invoice calculations now include correct VAT amount
**Example:** 100 subtotal + 21% VAT = 121.00 (was 100.21)
**Pattern:** Be careful with percentage/decimal conversions - only convert once

This document defines:

* Refactoring priorities
* Completed architectural patterns
* Active implementation tasks
* Testing and infrastructure goals
* Migration strategy phases

It functions as the **single source of truth** for ongoing modernization work.

---

# Priority Roadmap

## High Priority (Blocking Architecture Consistency)

### 1. Status Enum Migration

**Status:** Planned
**Priority:** HIGH

#### Problem

Status handling is inconsistent:

* Some models use `Status` relationships
* Some use string-based statuses
* Some use custom enum classes

#### Target State

Use **native PHP 8.1+ enums** across all models.

#### Tasks

```markdown
- [ ] Create LeadStatus enum
- [ ] Create TaskStatus enum
- [ ] Convert OfferStatus to native enum
- [ ] Convert InvoiceStatus to native enum
- [ ] Add enum casting in models
- [ ] Implement transition validation logic
```

#### Benefits

* Type safety
* IDE auto-completion
* Safer refactoring
* Standardized transitions

---

### 2. Money Value Object Implementation

**Status:** Planned
**Priority:** HIGH

#### Problem

Currency logic currently:

* Uses floating-point values
* Causes rounding errors
* Lacks multi-currency support

#### Target State

Use a **Money Value Object** system.

#### Tasks

```markdown
- [ ] Verify existing Money implementation
- [ ] Add Money library if missing
- [ ] Convert PaymentService to Money
- [ ] Convert InvoiceCalculator to Money
- [ ] Store currency as integer cents
- [ ] Create migration for decimal → integer conversion
```

#### Benefits

* Precision-safe arithmetic
* Currency-aware operations
* Stable financial calculations

---

# Medium Priority Work

## 3. Repository Layer Standardization

**Status:** Pending
**Priority:** MEDIUM

#### Objective

Introduce repository abstraction across all domains.

#### Tasks

```markdown
- [ ] Create repositories for major models
- [ ] Move complex queries to repositories
- [ ] Refactor controllers to use repositories
- [ ] Update services to depend on repository interfaces
```

#### Benefits

* Improved testability
* Separation of concerns
* Clear data access boundaries

---

## 4. Trait Standardization

**Status:** In Progress
**Priority:** MEDIUM

Traits to standardize across models:

```markdown
Blameable
Statusable
HasExternalId
```

#### Tasks

```markdown
- [ ] Apply Blameable to Task
- [ ] Apply Blameable to Lead
- [ ] Apply Blameable to Project
- [ ] Apply Statusable to Task
- [ ] Apply Statusable to Lead
- [ ] Apply Statusable to Project
```

#### Benefits

* Reduced duplication
* Consistent behavior
* Standardized ownership tracking

---

## 5. Sequence Number Generator

**Status:** Pending
**Priority:** MEDIUM

#### Objective

Standardize number generation patterns.

#### Target Features

```text
INV-{YEAR}-{ID}
PRJ-{YEAR}-{ID}
CLI-{YEAR}-{ID}
```

#### Tasks

```markdown
- [ ] Create SequenceGenerator service
- [ ] Refactor InvoiceNumberService
- [ ] Support tenant-specific formats
- [ ] Ensure thread-safe increments
```

---

# Low Priority Work

## 6. Document Lifecycle Observer

**Status:** Planned
**Priority:** LOW

#### Objective

Automate file cleanup.

#### Tasks

```markdown
- [ ] Create DocumentObserver
- [ ] Add file existence checks
- [ ] Delete files on model deletion
- [ ] Improve missing-file handling
```

---

## 7. Search Infrastructure Stabilization

**Status:** Planned
**Priority:** LOW

#### Notes

* Elasticsearch already configured
* Disabled during tests

#### Tasks

```markdown
- [ ] Mock search functionality in tests
- [ ] Implement database fallback search
- [ ] Improve test compatibility
```

---

# Completed Patterns

These patterns are already implemented and verified.

---

## Observer Pattern — Model Side Effects

**Status:** Implemented

#### Example

Document file deletion handled via:

```php
DocumentObserver
```

#### Benefits

* Decoupled file logic
* Consistent cleanup
* Reduced controller responsibility

---

## Blameable Trait

**Status:** Implemented

#### Functionality

Tracks:

```text
user_created_id
user_updated_id
```

#### Benefits

* Automatic audit tracking
* Consistent ownership metadata

---

## Exception Type Standardization

**Status:** Implemented

#### Change

All custom enums now throw:

```php
InvalidArgumentException
```

Instead of:

```php
Exception
```

#### Benefits

* More predictable error handling
* Improved debugging

---

## Action Classes

**Status:** Implemented

#### Pattern

Controllers delegate logic to:

```text
app/Actions/{Domain}/
```

#### Benefits

* Reusable business logic
* Improved testing

---

## Currency Input Normalization

**Status:** Implemented

Handled via:

```php
prepareForValidation()
```

#### Benefits

* Consistent number parsing
* Locale-safe validation

---

## Soft Delete Assertions

**Status:** Implemented

Uses:

```php
assertSoftDeleted()
```

Instead of:

```php
assertNull()
```

---

# Infrastructure Enhancements

## Model Factory Improvements

**Status:** Ongoing

#### Tasks

```markdown
- [ ] Add relationship factory states
- [ ] Support polymorphic factory setups
- [ ] Ensure nested model generation
```

---

## Database Constraints

**Status:** Partial

#### Completed

```markdown
- [x] projects.lead_id foreign key
- [x] offers.status_id foreign key
```

#### Remaining

```markdown
- [ ] invoices.client_id non-null constraint
- [ ] offers.client_id non-null constraint
- [ ] tasks.status_id non-null constraint
- [ ] leads.status_id non-null constraint
- [ ] Default status values
```

---

# Testing Infrastructure

## Test Isolation Enforcement

**Status:** Ongoing

#### Tasks

```markdown
- [ ] Audit controller tests
- [ ] Remove side-effect HTTP requests
- [ ] Split multi-purpose tests
- [ ] Enforce RefreshDatabase usage
```

---

## Test Utility Development

**Status:** Planned

#### Tasks

```markdown
- [ ] Create date normalization helper
- [ ] Implement assertDateEquals helper
- [ ] Create reusable test setup helpers
```

---

# API Architecture

## API Resource Standardization

**Status:** Planned

#### Tasks

```markdown
- [ ] Create ClientResource
- [ ] Create InvoiceResource
- [ ] Create CalendarResource
```

#### Benefits

* Consistent API output
* Easier versioning
* Predictable serialization

---

# Domain Architecture Improvements

## Domain Events & Listeners

**Status:** Planned

#### Examples

```markdown
- [ ] PaymentCreated → UpdateInvoiceStatus
- [ ] LeadStatusChanged → LogStatusHistory
- [ ] AbsenceCreated → NotifyManagers
```

#### Benefits

* Event-driven architecture
* Reduced coupling
* Easier extension

---

## State Machine Integration

**Status:** Planned

#### Target

Implement controlled transitions:

```text
draft → active → closed
```

#### Benefits

* Valid transition enforcement
* Predictable workflow behavior

---

# Migration Strategy

Refactoring is divided into controlled phases.

---

## Phase 1 — Low Risk (Completed)

```markdown
- [x] Exception standardization
- [x] Action class introduction
- [x] Currency normalization
- [x] Soft delete testing
```

---

## Phase 2 — Medium Risk (In Progress)

```markdown
- [ ] Observer standardization
- [ ] Domain event integration
- [ ] API resource rollout
```

---

## Phase 3 — High Risk (Planned)

```markdown
- [ ] Enum migration
- [ ] Foreign key enforcement
- [ ] Money value object conversion
```

---

# Model Relationship Conventions

All models must:

```markdown
- Use Blameable where applicable
- Define inverse polymorphic relationships
- Apply foreign key constraints
- Support factory relationships
```

---

# Documentation Maintenance

Documentation must be updated whenever:

```markdown
- New pattern introduced
- Schema changes applied
- Architecture refactored
- Testing behavior modified
```

---

# Reference Documents

```text
.github/refactor_plan.md
.github/test_isolation_refactor.md
.github/error_repair_plan.md
.github/copilot-instructions.md
AGENTS.md
```

---

# Notes for Future Contributors

Always:

```markdown
- Run full test suite after refactoring
- Update factories after schema changes
- Document breaking changes
- Use feature flags for major rollouts
```
