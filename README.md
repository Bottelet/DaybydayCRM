# DaybydayCRM

DaybydayCRM is an open-source, self-hosted Laravel CRM for managing clients, leads, projects, tasks, invoices, offers, payments, documents, appointments, and internal team workflows.

## What it includes
- Client, lead, project, and task management
- Invoicing, offers, payments, and time-related workflows
- Documents and filesystem integrations
- Role/permission management and authorization middleware
- Notifications, comments, appointments, absences, and search
- Web UI, API endpoints, browser tests, and Playwright coverage

## Current stack
- PHP 8.3+
- Laravel 12
- MySQL/MariaDB
- Redis/queue support
- Vue 2 + Blade + Vite
- PHPUnit, Dusk, and Playwright
- Docker Compose + Makefile workflows

## Repository guide
- `AGENTS.md` — contributor and AI-agent working rules
- `.github/ARCHITECTURE.md` — architecture and technical debt notes
- `.github/TESTING.md` — testing and isolation standards
- `.github/ROADMAP.md` — current modernization roadmap
- `.github/copilot-instructions.md` — concise Copilot-specific guidance
- `.junie/*.md` — short operational summaries for analysis, testing, fixes, and refactors
- `CHANGELOG.md` — current branch changelog summary

## Quick start

### Option 1: Docker workflow
1. Copy environment defaults if needed.
2. Start containers:
   ```bash
   make up
   ```
3. Enter the workspace container:
   ```bash
   make dsh
   ```
4. Inside the container, install dependencies and initialize the app:
   ```bash
   make setup
   ```
5. Run tests inside the container when needed:
   ```bash
   make test
   ```

### Option 2: Host workflow
1. Install PHP and JavaScript dependencies:
   ```bash
   composer install
   yarn install
   ```
2. Create your environment file and app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Prepare the database:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. Build frontend assets:
   ```bash
   yarn run build
   ```
5. Start the application locally:
   ```bash
   composer dev
   ```

## Useful commands
- `make clear` — clear Laravel caches
- `make test` — PHPUnit with stop-on-failure behavior
- `make test-filter f=SomeTest` — run a filtered PHPUnit subset
- `make paratest` — run tests in parallel
- `make dmfs` — fresh migrate/seed inside Docker
- `make dseed` — seed demo and dummy data inside Docker
- `npm run build` / `yarn run build` — build frontend assets
- `git ls-files '*.php' | xargs -n1 php -l` — required PHP syntax lint before push/PR

## Testing expectations
- Keep tests self-contained.
- Use factories instead of depending on seeders or other tests.
- Prefer one HTTP request per test unless validating a workflow.
- Put controller HTTP tests in `tests/Feature/Controllers/` or the relevant `tests/Feature/<Domain>/` area.
- Normalize Carbon objects before assertions.
- Reload users after permission changes when authorization checks are involved.

See `.github/TESTING.md` and `AGENTS.md` for the full rules.

## Architecture snapshot
DaybydayCRM follows a layered Laravel architecture:

`Routes -> Middleware -> Controllers -> Services/Actions -> Repositories/Models -> Views or JSON responses`

Key conventions in the current refactor branch:
- thin controllers
- FormRequest-based validation
- service/action extraction for business logic
- enums for fixed value sets
- observer/trait-based model behavior
- explicit JSON vs web response handling

## Integrations and storage
The application supports external integrations through repository and service abstractions, including storage providers such as local storage, Dropbox, and Google Drive. Current refactor work also includes registry and null-adapter patterns to make these integrations safer in testing and fallback scenarios.

## Contributing
1. Read `AGENTS.md` first.
2. Follow the testing rules in `.github/TESTING.md`.
3. Prefer existing services, actions, enums, and FormRequests over adding new inline logic.
4. Run the minimum required validation before pushing changes.
5. Keep documentation updated when behavior or workflows change.

## Historical resources
- Project website: <https://daybydaycrm.com/>
- Demo: <https://demo.daybydaycrm.com/>
- Wiki: <https://github.com/Bottelet/DaybydayCRM/wiki>
- Sponsorship: <https://github.com/sponsors/Bottelet>

## License
- DaybydayCRM `>= 2.0.0` is GPLv3
- Older releases remain MIT-licensed

See the existing project notices for legacy licensing context.
