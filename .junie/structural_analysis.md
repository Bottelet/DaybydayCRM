# Structural Code Analysis

Refer to **[.github/ARCHITECTURE.md](../.github/ARCHITECTURE.md)** for a detailed structural analysis.

## Core Weaknesses
1. **Database Schema:** Mandatory fields (UUIDs, IP addresses) often lack DB-level defaults, relying on application-level boot methods.
2. **Infrastructure:** Heavy reliance on `db:seed` in tests leads to slow execution and duplicate entry errors.
3. **Brittle Logic:** Date/Time comparisons and relationship assumptions frequently cause test failures.
4. **Technical Debt:** Outdated routing syntax and closure-based factories.
5. **Percentage Storage:** Inconsistent handling of percentage values (stored as int × 100, requires division by 10000).
6. **Response Handling:** Controllers lack consistent JSON vs web response differentiation.
7. **Type Safety:** Status `source_type` mixes string literals and class names, causing validation failures.
8. **Null Safety:** Trait methods don't consistently check for null before accessing optional properties.
