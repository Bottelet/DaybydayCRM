# Refactor Plan Summary

This is the short-form refactor plan. The full roadmap lives in `.github/ROADMAP.md`.

## Completed or actively addressed on this branch
- controller-to-service extraction across multiple domains
- broader FormRequest adoption
- stronger storage/authentication abstractions
- expanded Feature coverage and test isolation work
- seed/demo/test data cleanup
- contributor and agent documentation refresh

## Next priorities
1. Continue extracting remaining large controllers.
2. Keep replacing inline validation with FormRequests.
3. Reduce legacy Entrust complexity where safe.
4. Continue migrating fixed-value domain constants toward enums and helper methods.
5. Keep test infrastructure deterministic and isolated.
6. Document any workflow or architecture change as part of the same PR.

## Refactor guardrails
- do not move business logic back into controllers
- do not rely on shared seeded state in new tests
- do not introduce new hard-coded status strings when helpers already exist
- do not skip JSON-vs-web response handling in mixed endpoints
- keep Playwright e2e specs in the one-file-per-phenomenon layout and route-driven style
