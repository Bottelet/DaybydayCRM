# AGENTS.md — DaybydayCRM AI Agent Guide

## Project Overview
- **DaybydayCRM** is a Laravel-based CRM for managing clients, tasks, projects, invoices, and more. The architecture is modular, with clear separation between domains (Clients, Projects, Tasks, Invoices, Integrations).
- **Key Directories:**
  - `app/Models/`: Eloquent models for all major entities.
  - `app/Http/Controllers/`: RESTful controllers, grouped by domain.
  - `app/Http/Middleware/`: Custom and standard middleware for permissions, demo mode, etc.
  - `app/Repositories/`: Abstractions for integrations (Billing, Filesystem).
  - `resources/views/`: Blade templates, organized by feature.
  - `routes/`: Route definitions (`web.php`, `api.php`).
  - `database/factories/`, `migrations/`, `seeders/`: Standard Laravel data setup.

## Architecture & Data Flow
- **Service Boundaries:**
  - Core business logic is in controllers and services, with repositories abstracting external integrations.
  - Integrations (billing, file storage) are pluggable via repository interfaces (`app/Repositories/BillingIntegration/`, `FilesystemIntegration/`).
  - Notifications use Laravel's notification system, often via database channel.
- **Data Flow:**
  - HTTP requests enter via `routes/`, pass through middleware, and are handled by controllers.
  - Controllers interact with Eloquent models and repositories, returning views or JSON.
  - Views use Blade and often include partials for headers, boards, etc.

## Developer Workflows
- **Build:**
  - Frontend: Use `npm run dev`, `npm run watch`, or `npm run prod` (see `package.json`).
  - Asset compilation: `webpack.mix.js` and legacy `gulpfile.js` (Elixir) for SASS/JS.
- **Test:**
  - PHP: `vendor/bin/phpunit` (config in `phpunit.xml`).
  - Browser: `php artisan dusk` for browser tests.
  - CI: See `.github/workflows/phpunit.yml` for GitHub Actions setup.
- **Database:**
  - Migrations: `php artisan migrate`.
  - Factories: `database/factories/` for test/dummy data.
- **Debug:**
  - Use `barryvdh/laravel-debugbar` (dev only).
  - Logging: Configured in `config/logging.php`.

## Project-Specific Conventions
- **External IDs:**
  - Most entities use `external_id` (UUID) for routing and API, not auto-increment IDs.
- **Permissions:**
  - Role/permission checks via Entrust (`app/Zizaco/Entrust/`).
  - Middleware like `user.is.admin`, `is.demo` restrict access to sensitive routes.
- **Integrations:**
  - Billing and file storage integrations are managed via `integrations` table and repository interfaces.
  - Example: Dropbox and Google Drive for file storage, Dinero for billing.
- **UI Patterns:**
  - Project/task boards use custom Blade partials and SASS (`resources/assets/sass/components/project-board.scss`).
  - DataTables for tabular data (see `yajra/laravel-datatables-oracle`).

## Integration Points
- **APIs:**
  - RESTful API routes in `routes/api.php` (auth:api middleware).
- **External Services:**
  - AWS S3, Dropbox, Google Drive, Dinero, etc. (see `composer.json` dependencies).
- **Notifications:**
  - Use Laravel's notification system, often with custom notification classes.

## Examples
- **Adding a new integration:**
  - Implement the relevant repository interface, register in the service provider, and add config/migration as needed.
- **Custom middleware:**
  - Add to `app/Http/Middleware/`, register in `app/Http/Kernel.php`.
- **New UI component:**
  - Add Blade partial in `resources/views/partials/`, SASS in `resources/assets/sass/components/`.

---
For more, see the [README.md](./readme.md) and code comments in each module.
