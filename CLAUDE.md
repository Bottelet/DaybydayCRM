# Claude Code — DaybydayCRM Working Guide

Start with `AGENTS.md`, then use `.github/ARCHITECTURE.md`, `.github/TESTING.md`, and `.github/ROADMAP.md` as needed.

## Non-negotiable rules

### Controller rules
- Controllers orchestrate requests, authorization, and responses only — no business logic.
- Business logic belongs in `app/Services/*` or `app/Actions/*`.
- **Never expose public proxy methods** on controllers (e.g. `getInvoices()`, `findByExternalId()`, `listAllClients()`). Call the service directly.
- **Authorization must go in `__construct()` middleware** — never inline `auth()->user()->can()` checks inside action methods.
- **`Model::all()->count()` is forbidden** — use `Model::count()`.
- **Extract repeated DataTable column closures** to private helpers: `formatDateColumn()`, `statusBadgeColumn()`.
- **Guard every optional relationship** before calling any method. If `primaryContact` can be null, null-check it: `$contact = $client->primaryContact; $merged = $contact ? array_merge($contact->toArray(), $client->toArray()) : $client->toArray();`

### PHPUnit test rules
- **Never use `withoutMiddleware()`** in feature tests. Set up correct permissions with `withPermissions()` instead.
- **Never hardcode email addresses**. Use `'email' => 'user_' . uniqid() . '@test.com'`.
- **Assert exact flash messages**: `assertSessionHas('flash_message', __('Client successfully updated'))` not just `assertSessionHas('flash_message')`.
- **Minimum coverage per controller**: index, show (valid id), show (invalid id → 404), store (valid), store (invalid), update, delete, unauthorized access.
- **`withPermissions()` is variadic** — you can call `withPermissions(PermissionName::A, PermissionName::B)` or pass an array.

### Playwright e2e test rules
- **`dismissTourIfVisible(page)` is mandatory** after every `page.goto()` before interacting with any page element. The Bootstrap tour backdrop blocks all clicks and is always active in fresh Playwright sessions. Import from `tests/e2e/helpers/plain-e2e.js`.
- **`loginAsAdmin` already calls `waitForLoadState('networkidle')`** — do not remove this line.
- **After state-changing requests, verify persistence** via a follow-up data fetch — never assert only on HTTP status.
- **Browser-driven tests** (using `page.goto()` + form/button interactions) must always call `dismissTourIfVisible` before touching any element.

## Architecture
- Prefer `app/Services/*` for multi-step workflows, `app/Actions/*` for single-purpose operations.
- Use `FormRequest` classes for all validation — no inline `$request->validate()` in controllers.
- Use enums for fixed value sets (`PermissionName`, status types, etc.).
- Mixed web/API endpoints must branch on `$request->expectsJson()`.

## Response pattern
```php
if ($request->expectsJson()) {
    return response()->json(['message' => 'Success'], 200);
}
session()->flash('flash_message', 'Success');
return redirect()->back();
```

## Pre-push checklist
```bash
git ls-files '*.php' | xargs -n1 php -l   # syntax check
make test                                   # PHPUnit
make e2e-test                              # Playwright
```
