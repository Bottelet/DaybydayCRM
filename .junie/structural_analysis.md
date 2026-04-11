# Structural Code Analysis

Refer to **[.github/ARCHITECTURE.md](../.github/ARCHITECTURE.md)** for a detailed structural analysis.

## Core Weaknesses
1. **Database Schema:** Mandatory fields (UUIDs, IP addresses) often lack DB-level defaults, relying on application-level boot methods.
2. **Infrastructure:** Heavy reliance on `db:seed` in tests leads to slow execution and duplicate entry errors.
3. **Brittle Logic:** Date/Time comparisons and relationship assumptions frequently cause test failures.
4. **Technical Debt:** Outdated routing syntax and closure-based factories.
