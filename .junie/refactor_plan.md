# Project Refactor Plan

**For complete refactoring opportunities and details, see [.github/refactor.md](../.github/refactor.md)**

## Quick Reference

### Documentation
- **[.github/refactor.md](../.github/refactor.md)** — Complete refactoring opportunities (12 major sections)
- **[.github/ROADMAP.md](../.github/ROADMAP.md)** — Current status and milestones
- **[.github/TESTING.md](../.github/TESTING.md)** — Testing standards and patterns
- **[.github/ARCHITECTURE.md](../.github/ARCHITECTURE.md)** — System architecture details

### Priority Goals
1. **Request Validation:** Create missing FormRequests for all controllers
2. **Service Extraction:** Move business logic from large controllers (>200 LOC) to services
3. **Enum Migration:** Convert model constants to type-safe enums
4. **Test Organization:** Move 39 HTTP tests from `tests/Unit/Controllers/` to `tests/Feature/Controllers/`
5. **Response Handling:** Standardize JSON vs Web response handling
6. **Permission Middleware:** Consolidate scattered permission checks

### High-Priority Refactorings (from .github/refactor.md)
1. **#1:** Standardize JSON vs Web Response Handling (~10 files, 8 hours)
2. **#2:** Consolidate Permission Checks in Middleware (~15 files, 12 hours)
3. **#8:** Missing FormRequest Validation (~15 controllers, 8 hours)
4. **#11:** Service Extraction (8 controllers, 40 hours)

### Estimated Total Effort
- **Files Affected:** 140+
- **Lines Changed:** ~2000+
- **Time Estimate:** ~108 hours
- **Risk Reduction:** High (security, maintainability, testability)

## Archive
Historical refactoring documents have been moved to `.github/archive/`:
- `refactoring.md` (merged into refactor.md)
- `refactoring-plan.md` (replaced by this summary)
