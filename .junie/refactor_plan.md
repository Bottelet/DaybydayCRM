# Project Refactor Plan

Refer to **[.github/ROADMAP.md](../.github/ROADMAP.md)** for current status and milestones.

## Priority Goals
1. **Modernize Factories:** Transition to Class-based Factories (Laravel 8+).
2. **Centralize UUIDs:** Use `HasExternalId` trait across all applicable models.
3. **Decouple Logic:** Extract business logic from controllers into Services/Actions.
4. **Clean Routing:** Migrate to tuple-based routing syntax.
5. **Optimize Testing:** Achieve 100% test isolation and migrate to `RefreshDatabase`.
