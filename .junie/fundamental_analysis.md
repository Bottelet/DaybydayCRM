# Fundamental Architectural Analysis

Refer to **[.github/ARCHITECTURE.md](../.github/ARCHITECTURE.md)** for the full analysis.

## Commit Linting Requirement
- Every commit must be linted before push/PR.
- Run: `git ls-files '*.php' | xargs -n1 php -l`
- CI also enforces this via the `php-lint` workflow.

## Core Issues
1. **Infrastructure:** Legacy factories and inconsistent UUID generation across models.
2. **Logic Leaks:** Business logic scattered across controllers instead of Services/Actions.
3. **Authorization:** Aging Entrust implementation and missing policy consistency.
4. **Technical Debt:** Vue 2 EOL and legacy Webpack/Mix asset pipeline.
5. **Testing Strategy:** Slow execution due to heavy setup and lack of true isolation.
