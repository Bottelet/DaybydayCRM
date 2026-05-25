# Refactor Plan Summary

This is the short-form refactor plan. The full roadmap lives in `.github/ROADMAP.md`.

## Completed or actively addressed on this branch
- **Controller-to-service extraction** — business logic moved from controllers to `app/Services/*` and `app/Actions/*`
- **FormRequest adoption** — validation and normalization moved to dedicated request classes
- **Storage/authentication abstractions** — stronger adapter patterns for external integrations
- **Feature test expansion** — broader HTTP/controller coverage with strict isolation
- **Seed/demo/test data cleanup** — deterministic test fixtures and factory-driven setup
- **Documentation refresh** — contributor and agent guides professionalized
- **Playwright e2e suite** — route-driven specs with stop-on-failure support and automatic failure capture

## Next priorities
1. **Continue extracting remaining large controllers** — identify fat controllers and extract business logic
2. **Replace inline validation with FormRequests** — move validation rules out of controllers
3. **Reduce legacy Entrust complexity** — simplify permission checks where safe
4. **Migrate fixed-value constants to enums** — replace hard-coded strings with typed enums and helpers
5. **Keep test infrastructure deterministic** — avoid shared state, use factories, normalize dates
6. **Document workflow changes** — update docs in the same PR as architecture changes

## Refactor guardrails
- **Do not** move business logic back into controllers
- **Do not** rely on shared seeded state in new tests
- **Do not** introduce new hard-coded status strings when helpers already exist
- **Do not** skip JSON-vs-web response handling in mixed endpoints
- **Do not** break the one-file-per-phenomenon Playwright e2e layout
- **Always** validate changes with `git ls-files '*.php' | xargs -n1 php -l` before pushing
