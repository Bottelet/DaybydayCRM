# Comprehensive Test Failure Analysis  
**Total Failures Analyzed:** 45  
**Focus:** Root-cause grouping and consolidated remediation strategy

---

# Executive Summary

This document consolidates 45 failing tests into root-level application components.  
Rather than addressing failures individually, this analysis identifies recurring structural patterns and defines grouped remediation tasks.

Primary issue categories:

- Missing or incomplete model relationships
- Repository return inconsistencies
- Factory data integrity gaps
- Status management inconsistency
- Currency calculation precision issues
- File lifecycle reliability problems

---

# Root Cause Analysis by Application Component

---

# 1. `app/Models/Lead.php`

**Failure Count:** 6

## Affected Tests

- Lead has many appointments
- Lead has many offers
- Lead has many documents
- Lead has many projects

## Root Causes

- Missing `documents()` relationship
- Missing `projects()` relationship
- Incorrect polymorphic factory configuration
- Potential incorrect polymorphic key assignments

## Required Actions

**Model**

- Add `documents()` morphMany relationship
- Add `projects()` hasMany relationship

**Factories**

- Implement:


->hasAppointments()
->hasOffers()
->hasDocuments()
->hasProjects()


**Verification**

- Validate polymorphic:


source_id
source_type


**Architectural Review**

Evaluate whether:


Lead → Project


should be:

- hasMany  
or  
- hasOne (state transition model)

---

# 2. `app/Models/Offer.php`

**Failure Count:** 3

## Root Causes

- Missing inverse polymorphic relationship
- Status stored as string instead of structured state
- Relationship alias mismatch (`lines()` expected)

## Required Actions

- Add:


source() → morphTo
lines() → alias for invoiceLines()


- Standardize status handling:

**Global Refactor Required**

Choose one:

- Native PHP Enum  
or  
- Status relationship table

- Ensure valid lead/source creation in factory
- Enforce non-nullable polymorphic linkage

---

# 3. `app/Models/Task.php`

**Failure Count:** 6

## Root Causes

- Null foreign keys in factory output
- Activity logging not triggered
- Missing automatic user tracking

## Required Actions

- Implement:


Blameable trait
Statusable trait


- Create:


TaskAssignment service


- Update TaskFactory defaults
- Ensure logging traits function during tests
- Enforce database-level constraints

---

# 4. `app/Models/User.php`

**Failure Count:** 8

## Root Causes

- Missing relationships
- Empty relationship collections
- Pivot table setup incomplete
- Foreign key naming inconsistencies

## Required Actions

- Add:


integrations()
settings()


- Extend factory states:


->withRole()
->hasClients()
->hasLeads()
->hasTasks()


- Standardize naming:


user_id
assigned_user_id


**Architectural Decision Required**

Evaluate replacing relational settings storage with:


JSON-based configuration


---

# 5. `app/Models/Invoice.php`

**Failure Count:** 3

## Root Causes

- Missing client assignment
- Missing payments and lines during factory creation

## Required Actions

- Enforce:


client_id NOT NULL


- Add factory defaults:


->hasPayments()
->hasLines()


- Implement:


InvoiceTotal service


Prevent finalization without line items.

---

# Repository Layer Failures

Affected Components:

- ClientRepository
- LeadRepository
- TaskRepository
- UserRepository

## Root Causes

- Silent null returns
- Global scopes filtering records
- Incorrect persistence logic

## Required Actions

- Replace:


find()


with:


findOrFail()


- Implement:


withoutGlobalScopes()


option

- Introduce:


Criteria classes


- Validate:


fillable attributes


- Return refreshed model after update

---

# Service Layer Failures

Affected:

- ClientService
- LeadService
- TaskService
- PaymentService

## Root Causes

- Silent transaction failures
- Improper assignment logic
- Floating-point arithmetic errors

## Required Actions

- Replace boolean failure returns with:


Domain Exceptions


- Standardize assignment logic:


sync()
syncWithoutDetaching()


- Implement:


Money Value Object


---

# Controller-Level Failure

## `DocumentsController`

## Root Causes

- File missing during test execution
- Exception not handled

## Required Actions

- Use:


Storage::fake()


- Add:


Storage::exists()


validation

- Return:


404


instead of:


500


---

# Global Refactoring Patterns

These patterns affect multiple components.

---

# Pattern 1 — Status Management

## Problem

Status logic varies between:

- Strings
- Foreign keys
- Mixed usage

## Standardization Strategy

- Implement:


Native PHP Enums


- Add:


State Machine logic


## Benefits

- Type safety
- Centralized transition control
- Predictable workflows

---

# Pattern 2 — Repository Reliability

## Problem

Null returns propagate runtime failures.

## Solution

- Mandatory:


findOrFail()


- Add:


Criteria-based querying


---

# Pattern 3 — Currency Precision

## Problem

Floating-point arithmetic causes rounding errors.

## Solution

- Implement:


brick/money


- Store:


Integer cents


---

# Pattern 4 — Creator Tracking

## Problem

`user_created_id` inconsistently populated.

## Solution

Implement:


Blameable trait


---

# Pattern 5 — File Lifecycle Integrity

## Problem

File persistence unreliable.

## Solution

Use:


Model Observers


---

# Implementation Roadmap

---

# Phase 1 — Blocking Fixes

Highest priority:

1. Add missing relationships
2. Fix repository null returns
3. Update factory relationships

---

# Phase 2 — Structural Standardization

4. Implement status enums
5. Add Money value object
6. Implement Blameable trait
7. Standardize document handling

---

# Phase 3 — Service Integrity

8. Repair service workflows
9. Implement State Machines
10. Standardize exception handling

---

# Phase 4 — Infrastructure Hardening

11. Add database constraints
12. Improve test infrastructure
13. Register observers

---

# Phase 5 — Documentation

14. Update TODO documentation
15. Update agent configuration
16. Align coding standards
