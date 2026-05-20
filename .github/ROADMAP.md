# Project Roadmap & Refactor Status

## Overview
This document outlines the planned refactoring efforts and tracks the current progress of modernizing the DaybydayCRM codebase and test suite.

---

## Current Status: Phase 1 - Foundation (✅ COMPLETE)

### Completed Milestones
- **HasExternalId Trait:** Applied to 18 models. Centralized UUID generation and route key handling.
- **Test Authorization Helpers:** Added `asOwner()` and `asAdmin()` to `TestCase` for easier role-based testing.
- **Date Comparison Helper:** Added `assertDatesEqual()` to `TestCase` to prevent brittle date comparisons.
- **Critical Test Isolation:** Fixed major interdependencies in Payment, Lead, and Offer tests.
- **PHPUnit 10+ Compatibility:** Transitioned to `#[Test]` attributes and fixed deprecated assertions.

### Metrics
| Metric | Status |
| :--- | :--- |
| Models with `HasExternalId` | 18 |
| Test Helper Methods | 3 |
| Critical Isolation Fixes | 5+ |
| PHPUnit 10+ Compatibility | 100% |

---

## Phase 2: Modernization (⏳ IN PROGRESS)

### 1. Class-Based Factories
- **Goal:** Convert legacy closure-based factories to Laravel 8+ class-based factories.
- **Status:** Pending.
- **Priority:** High.

### 2. Service & Action Layer Transition
- **Goal:** Move business logic from controllers into `app/Services` and `app/Actions`.
- **Status:** In Progress.
- **Priority:** High.

### 3. Native Laravel Authorization
- **Goal:** Transition from Entrust to native Laravel Gates and Policies.
- **Status:** Pending.
- **Priority:** Medium.

---

## Phase 3: Infrastructure & Frontend (Planned)

### 1. Database Refresh Optimization
- **Goal:** Replace `migrate:fresh` in `TestCase` with `RefreshDatabase` trait for improved performance.
- **Requirement:** All tests must be truly isolated (Phase 1 complete).

### 2. Frontend Modernization
- **Goal:** Migrate from Vue 2 to Vue 3 and replace Webpack/Mix with Vite.
- **Priority:** Long-term.

### 3. Legacy Routing Cleanup
- **Goal:** Convert all string-based routes to tuple-based syntax `[Controller::class, 'method']`.
- **Priority:** Medium.
