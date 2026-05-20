# Fundamental Analysis

Use this file as a fast summary; the authoritative detail lives in `AGENTS.md` and `.github/ARCHITECTURE.md`.

## System identity
DaybydayCRM is a modular Laravel CRM handling sales, delivery, billing, documents, appointments, absences, permissions, and integrations.

## Core architectural direction
- thin controllers
- services and actions for business logic
- FormRequests for validation and normalization
- traits and observers for repeated model behavior
- adapters/registries for external integrations
- strong separation between web and JSON response flows

## Current branch themes
- large-controller refactors
- stronger validation boundaries
- improved permission tooling and cache handling
- storage hardening, especially Dropbox-related flows
- broader Feature and Unit test coverage
- contributor/agent documentation cleanup

## Files to read next
- `README.md`
- `CHANGELOG.md`
- `AGENTS.md`
- `.github/ARCHITECTURE.md`
