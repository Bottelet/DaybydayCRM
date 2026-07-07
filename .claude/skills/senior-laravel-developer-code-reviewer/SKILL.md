---
name: senior-laravel-developer-code-reviewer
description: "Orchestrates existing Laravel skills to produce structured PR reviews"
---

# Purpose

This skill does not implement rules.

It orchestrates existing skills to produce a complete pull request review.

---

# Delegation Model

This reviewer MUST delegate evaluation to existing skills, when present in the project:

## Architecture

Delegate to:

- application-architecture-standard skill
- service-layer skill
- dto-contract skill
- safe-refactoring-rules skill
- laravel-modules skill (if the project uses modular architecture)
- any project-specific schema/PK convention skill (e.g. non-standard primary keys)

## Tests
Use:
- filament-resource-testing skill (if the project uses Filament)
- data-layer-contracts skill

## Security
Use:
- security-review-checklist skill
- otherwise infer from architecture + test gaps

---

# Review Process

When reviewing a PR:

## 1. Architecture pass
Summarize findings from architecture-related skills.

Do NOT restate rules.

Only report violations.

---

## 2. Test pass
Summarize findings from test-related skills.

Focus on:
- weak tests
- missing coverage
- nondeterministic tests
- missing failure cases

---

## 3. Security pass
Identify:
- missing authorization
- unsafe access paths
- privilege escalation risks
- missing validation

---

## 4. Consolidation

Merge findings into:

- Critical issues (must fix)
- Important issues
- Suggestions

---

# Output Format

## Summary
Short PR assessment

## Critical Issues
Bullets only

## Important Issues
Bullets only

## Suggestions
Bullets only

## Test Risk Summary
Only risks, no rule explanation

## Security Notes
Only vulnerabilities, no theory

## Suggested Fixes
Copy/paste code only

---

# Constraints

- Do NOT restate rules defined in other skills
- Do NOT include full explanations of SOLID, DRY, etc.
- Do NOT duplicate test philosophy definitions
- Only report deviations
- Keep output strictly diagnostic

---

# Prioritization

Report issues in this order:

1. Production bugs
2. Security issues
3. Data integrity risks
4. Data layer contract violations
5. Architecture violations
6. Maintainability improvements
7. Cosmetic suggestions

Never allow style issues to obscure correctness issues.

---

# Tone

Simple, direct, non-verbose.

Explain issues like:

> "This bypasses the service layer and writes directly to the model."

Not:

> "This violates layered architecture principles..."
