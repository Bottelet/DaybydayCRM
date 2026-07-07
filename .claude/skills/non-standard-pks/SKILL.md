---
name: non-standard-pks
description: "Works with models that have non-standard primary key names. Activates when writing factories, tests, relationships, or seeders for models that use user_id, client_id, invoice_id, etc. instead of id."
license: MIT
metadata:
  author: project
---

# Non-Standard Primary Keys

Most models in this app use descriptive primary key names, not `id`.

## PK Reference Table

| Model | Table | Primary Key |
|-------|-------|-------------|
| User | users | `user_id` |
| Client | clients | `client_id` |
| Invoice | invoices | `invoice_id` |
| InvoiceGroup | invoice_groups | `invoice_group_id` |
| Quote | quotes | `quote_id` |
| Payment | payments | `payment_id` |
| Product | products | `product_id` |
| Project | projects | `project_id` |
| Task | tasks | `task_id` |
| TaxRate | tax_rates | `tax_rate_id` |
| Family | families | `family_id` |
| Unit | units | `unit_id` |
| EmailTemplate | email_templates | `email_template_id` |
| Company | companies | `id` ← standard |
| Expense | expenses | `id` ← standard |
| ExpenseCategory | expense_categories | `id` ← standard |

## Accessing the PK

Use `$model->getKey()` when you need the PK value generically. Use the named
attribute only when you know the model:

```php
$invoice->invoice_id   // ✓ typed access
$invoice->getKey()     // ✓ generic access
$invoice->id           // ✗ returns null on non-standard PKs
```

## In Factories: Pass the FK by Name

When a factory creates related records, pass the FK column explicitly:

```php
Invoice::factory()->create([
    'company_id'       => $this->companyId,
    'client_id'        => $client->client_id,      // not $client->id
    'invoice_group_id' => $group->invoice_group_id, // not $group->id
    'user_id'          => $user->user_id,           // not $user->id
]);
```

## In Tests: Use the PK for the Record Parameter

Filament's Edit page `record` parameter expects the PK value:

```php
Livewire::actingAs($this->user)
    ->test(EditInvoice::class, [
        'record' => $invoice->invoice_id,  // not $invoice->id
        'tenant' => $this->company,
    ])
```

## Model Definition

Always declare `$primaryKey` explicitly. Models also set `$timestamps = false`
since they manage date columns manually:

```php
class Invoice extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'invoice_id';
    public    $timestamps = false;
}
```

## Relationships Must Specify Keys

BelongsTo and HasMany must specify the FK and owner key when they differ from
Laravel's convention (`{model}_id` → `id`):

```php
// On Invoice — points to users.user_id, not users.id
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id', 'user_id');
}

// On Company — invoice FK column is invoice_group_id, PK is invoice_group_id
public function invoiceGroups(): HasMany
{
    return $this->hasMany(InvoiceGroup::class, 'company_id', 'id');
}
```

---

## Never Assume "id"

Never access:

$model->id

Never reference:

'id'

unless the model explicitly uses a standard primary key.

Use:

$model->getKey()

or the named primary key property.

---

## Fix-One-Fix-All

If one test, factory, seeder, relationship, resource, or service incorrectly
uses `id` instead of the model's primary key, search the repository for the
same pattern and correct all occurrences.

Primary key assumptions tend to fail systematically rather than individually.
