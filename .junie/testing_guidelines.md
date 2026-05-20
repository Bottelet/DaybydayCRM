# Testing Guidelines

For the complete rules, use `.github/TESTING.md` and `AGENTS.md`.

## Mandatory rules
- Tests must be self-contained.
- Prefer factories over shared seed data.
- Keep controller/HTTP coverage in `tests/Feature/*`.
- Prefer one HTTP request per test unless validating a real workflow.
- Normalize Carbon/date values before assertions.
- Refresh users after changing roles or permissions.

## Practical commands
```bash
git ls-files '*.php' | xargs -n1 php -l
make test
make test-filter f=SomeTest
make paratest
```

## Watch for these failures
- permission cache stale after role/permission updates
- JSON requests asserting browser redirect responses
- status checks comparing relation objects or raw strings incorrectly
- null relationship or null date access
- nondeterministic storage behavior in tests
- seeded VAT/percentage data causing unexpected calculations
