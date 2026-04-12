# Fundamental Architectural Analysis

Refer to **[.github/ARCHITECTURE.md](../.github/ARCHITECTURE.md)** for the full analysis.

## Core Issues
1. **Infrastructure:** Legacy factories and inconsistent UUID generation across models.
2. **Logic Leaks:** Business logic scattered across controllers instead of Services/Actions.
3. **Authorization:** Aging Entrust implementation and missing policy consistency.
4. **Technical Debt:** Vue 2 EOL and legacy Webpack/Mix asset pipeline.
5. **Testing Strategy:** Slow execution due to heavy setup and lack of true isolation.
