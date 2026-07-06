---
name: data-layer-contracts
description: Defines the schema-to-factory-to-seeder contract chain — NOT NULL alignment, factory/seeder ownership boundaries, and schema drift rules
---

# Data Layer Contracts

## Purpose

Defines the contract chain from migration schema to factory to seeder, and prevents drift between them.

---

## Schema Contract

Every NOT NULL column defined in a migration must be supported by:

- a factory definition, or
- a seeder definition (only for seed data), or
- an explicit DB default in the migration

This is a **schema-only rule**, not a validation rule. MySQL/MariaDB is the canonical database — SQLite differences are invalid for schema validation assumptions.

---

## Factory Rules

Factories MUST:

- satisfy all NOT NULL columns
- reflect migration constraints
- represent the smallest valid persisted entity — not random data, not business scenarios, only valid schema state

Factories MUST NOT:

- enforce business rules or validation rules
- replace service-layer creation logic
- define seeder logic, or depend on seeders

If a migration introduces a NOT NULL column, the factory MUST be updated immediately — omission is invalid state.

Factories SHOULD align with service-layer expectations but do NOT depend on it: service layer = behavior, factory = valid structure.

---

## Seeder Rules

Seeders MUST:

- rely on factories for object creation — factories are the source of truth for valid model state
- orchestrate dependency order between models, optionally via helpers (findOrCreateClient, findOrCreateProject, findOrCreateUser — convenience utilities, not business logic)
- only insert schema-valid data, with no reliance on implicit database defaults

Seeders MUST NOT:

- define validation rules, factory structure, or schema constraints
- contain business logic

Seeders assemble data. They do not define data correctness.

---

## Drift Triggers

The following indicate schema drift: migration changes, factory mismatch, seeder mismatch, SQLSTATE constraint violations, CI vs local DB mismatch.

Schema validation requires `migrate:fresh` + `seed` before running test suites.

---

## Identity Rule

Primary keys are non-deterministic. Tests MUST NOT rely on hardcoded IDs.
