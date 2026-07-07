---
name: laravel-modules
description: "Creates and modifies code inside the Modules/ directory structure. Activates when adding a new module, adding a model/factory/migration/resource/service to an existing module, registering a module with Filament, or when the user mentions modules, modular, or a specific module name (clients, invoices, payments, etc.)."
license: MIT
metadata:
  author: project
---

# Laravel Modules

This project uses a hand-rolled modular structure under `Modules/`. There is no
`nwidart/laravel-modules` package — modules are plain directories registered
manually.

## Directory Layout

Every module follows this exact layout:

```
Modules/{Name}/
  database/
    factories/        ← Eloquent factories
    migrations/       ← Module-specific migrations
    seeders/          ← Module seeders (extend AbstractSeeder)
  resources/
    views/            ← Blade views namespaced as '{name}::'
  src/
    Contracts/        ← Interfaces / contracts (optional)
    Enums/            ← Module enums
    Filament/
      Resources/
        {Model}/
          {Model}Resource.php     ← extends BaseResource
          Pages/
            List{Model}.php       ← extends ListRecords
            Create{Model}.php     ← extends CreateRecord
            Edit{Model}.php       ← extends EditRecord
    Models/           ← Eloquent models
    Observers/        ← Model observers (optional)
    Providers/        ← {Name}ServiceProvider
    Services/         ← {Name}Service (createX, updateX, deleteX, listForCompany)
    Traits/           ← Shared traits (optional)
  tests/
    Feature/          ← PHPUnit feature tests
    Unit/             ← PHPUnit unit tests
```

## Namespace Convention

```
Modules\{Name}\            root namespace
Modules\{Name}\Models\     models
Modules\{Name}\Filament\Resources\{Model}\{Model}Resource
Modules\{Name}\Database\Factories\{Model}Factory
Modules\{Name}\Database\Seeders\{Model}Seeder
Modules\{Name}\Services\{Model}Service
Modules\{Name}\Tests\Feature\{Model}Test
```

## Service Provider

Every module has a `{Name}ServiceProvider` that loads migrations and views.
Observers are registered here too:

```php
class ClientsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'clients');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        Client::observe(ClientObserver::class); // only if observer exists
    }
}
```

## Registering a New Module

1. Create the directory tree above.
2. Add the service provider to `config/app.php` providers array.
3. Register the factory namespace in `app/Providers/AppServiceProvider.php`
   (look at how existing modules do it — there's a `Factory::guessFactoryNamesUsing` callback).
4. Add `->discoverResources(...)` to `CompanyPanelProvider` for the new module.

## Registering Resources in the Panel

`app/Providers/Filament/CompanyPanelProvider.php` discovers resources per module:

```php
->discoverResources(
    in: base_path('Modules/mymodule/src/Filament/Resources'),
    for: 'Modules\\Mymodule\\Filament\\Resources'
)
```

## Tests Are Auto-Discovered

`phpunit.xml` includes module test directories:

```xml
<directory>Modules/*/tests/Unit</directory>
<directory>Modules/*/tests/Feature</directory>
```

No extra registration needed — just put tests in the right place.
