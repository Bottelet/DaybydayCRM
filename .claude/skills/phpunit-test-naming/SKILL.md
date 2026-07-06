---
name: phpunit-test-naming
description: "Enforces grammatically correct, intention-revealing PHPUnit test method names"
triggers:
  - writing new test methods
  - reviewing test files
  - renaming test methods
  - when a test name contains test_, it_user_, a_user_, or is otherwise grammatically wrong
---

# PHPUnit Test Naming Convention

## The Rule

Every test method MUST start with `it_` and form a grammatically correct English
sentence when you replace underscores with spaces.

```
it_{verb}_{object}_{optional_qualifiers}
```

**"it"** is the grammatical subject — "it" = the feature/system under test.

```
"It returns 404 for a nonexistent record."
"It stores the entity and fires the expected event."
"It rejects the action when a precondition is not met."
```

## ✅ Correct

```php
public function it_returns_200_when_record_is_found(): void {}
public function it_redirects_to_index_when_user_lacks_permission(): void {}
public function it_stores_entity_with_valid_payload(): void {}
public function it_rejects_invalid_status_for_task(): void {}
public function it_soft_deletes_parent_and_preserves_children(): void {}
public function it_sends_403_json_when_access_is_denied(): void {}
public function it_creates_line_item_and_attaches_it_to_parent(): void {}
public function it_calculates_total_price_excluding_tax(): void {}
public function it_authorized_user_can_delete_task(): void {} // ✅ reads: "it [is that an] authorized user can delete task"
public function it_unauthorized_user_cannot_delete_task(): void {} // ✅ same pattern
```

## ❌ Wrong — and why

| Bad name | Problem | Fix |
|---|---|---|
| `it_user_can_view_document` | "it user" is not English | `it_authorized_user_can_view_document` |
| `a_user_can_view_document` | Doesn't start with `it_` | `it_authorized_user_can_view_document` |
| `test_duplicates_are_removed` | `test_` prefix — use `#[Test]` attribute instead | `it_removes_duplicate_statuses_in_response` |
| `test_it_can_access_create_task` | Double prefix: `test_it_` | `it_can_access_task_create_page` |
| `it_can_list_clients_index` | Redundant "index" after verb "list" | `it_lists_all_clients_on_index_page` |

## The `it_user_*` smell

`it_user_can_*` was historically used for user-permission tests. The fix is NOT to
rename to `a_user_*` — that just swaps one broken convention for another. The correct
patterns are:

```php
// For permission tests: keep "user" as a qualifier on the subject
it_authorized_user_can_delete_task()
it_unauthorized_user_cannot_delete_task()

// Or: drop the subject, make it about the outcome
it_deletes_task_when_user_has_delete_permission()
it_returns_403_when_user_lacks_delete_permission()
```

## Attribute, Not Prefix

Never use `test_` prefix. Always annotate with `#[Test]`:

```php
// ❌
public function test_creates_record() {}

// ✅
#[Test]
public function it_creates_record(): void {}
```

## Return Type

All test methods must declare `: void`.

## Checklist When Writing a Test Name

1. Does it start with `it_`?
2. Read it aloud replacing underscores with spaces. Does it sound like a sentence?
3. Is the verb meaningful? (`returns`, `stores`, `rejects`, `sends`, `deletes`, `redirects`, `calculates`, `creates`, `lists`, `shows`)
4. Does the name reveal the *scenario*, not just the *action*? (`it_rejects_payment_when_invoice_is_not_sent` > `it_cannot_add_payment`)
5. Return type `: void` declared?

## Audit Command

```bash
# Find non-conforming names (test_, a_user_, it_user_, no it_ prefix)
grep -rn "public function " tests/ --include="*.php" \
  | grep -vE "setUp|tearDown|__construct|protected |abstract " \
  | grep -vE "public function it_" \
  | grep -v "Browser/"
```
