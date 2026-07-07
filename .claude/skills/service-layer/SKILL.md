---
name: service-layer
description: Defines application service structure and business orchestration boundaries
license: MIT
metadata:
  author: project
---

# Service Layer

Services are the only place business logic lives. They are framework-agnostic.

---

# 1. Responsibility

Services MUST:

- contain business logic
- coordinate models
- enforce domain rules
- return models or DTOs

Services MUST NOT:

- accept or return HTTP request/response objects
- contain UI logic
- use `app()` or `resolve()` internally

---

# 2. Dependency Rule

Constructor injection only:

```php
public function __construct(
    private InvoiceRepository $repository
) {}
```

---

# 3. DTO Rule

DTOs are **not** required when the source is a validated `FormRequest::validated()`
array. Arrays are fine there.

Use DTOs when:
- crossing system boundaries (API, queues, external integrations)
- multiple services share a contract
- the payload must be stable across refactors

Skip DTOs when:
- input comes from a single controller action's validated request
- the data is short-lived and not reused

---

# 4. Standard Shape

```
app/Services/{Domain}/{Model}Service.php
```

e.g. `app/Services/Task/TaskService.php`, `app/Services/Offer/OfferService.php`.

Standard method names: `create`, `update`, `delete`, `findByExternalId`. Prefer
one service class per domain holding both `create`/`update` (see `TaskService`)
over splitting into separate per-action service classes.
