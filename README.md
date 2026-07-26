<a href="https://daybydaycrm.com/">
    <img src="https://user-images.githubusercontent.com/15610490/69175894-ed771300-0b04-11ea-9ecd-a5ad6e3d8877.png" height="100" alt="DaybydayCRM logo" />
</a>

======================

![PHPUnit](https://img.shields.io/github/actions/workflow/status/Bottelet/DaybydayCRM/phpunit.yml?branch=develop&label=phpunit&style=for-the-badge)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/bottelet/DaybydayCRM?label=Latest%20version&style=for-the-badge)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=for-the-badge)](http://makeapullrequest.com)
![License](https://img.shields.io/badge/license-GPLv3-blue.svg?style=for-the-badge)

[DaybydayCRM](https://daybydaycrm.com) is an everyday customer relationship management system (CRM) to help you keep track of customers, tasks, appointments, invoices, payments, documents, and other daily workflows. The CRM is available as an open-source, self-hosted platform and as a [hosted CRM system](https://daybydaycrm.com) on daybydaycrm.com.

<img src="https://user-images.githubusercontent.com/15610490/84194453-54f2b100-aa9d-11ea-8fa8-12bde56b9deb.png" align="left" alt="DaybydayCRM screenshot" />

# Demo

Try a demo version of DaybydayCRM at:

[demo.daybydaycrm.com](https://demo.daybydaycrm.com/?utm_source=github&utm_medium=daybydaycrmPage&utm_campaign=readme)

# Support the project
If you benefit from or like using DaybydayCRM, please consider helping drive the future development of the project by:
* Starring the project. ⭐
* Creating a pull request. 🚧
* [Donating/Sponsoring today](https://github.com/sponsors/Bottelet). 💛
* Considering the hosted version of [DaybydayCRM](https://daybydaycrm.com). ✔️

The project continues to ship features, releases, support, and fixes through community and sponsor support.

### Features
- Tasks and leads management
- Invoice management
- Time registration
- User absence and vacation registration
- Client and user appointments
- Role and permission management
- Global search
- Client overview
- Uploading documents and tracking client files
- And much more; see daybydaycrm.com for a broader feature overview

### Current stack
- PHP 8.3+
- Laravel 12
- MySQL/MariaDB
- Redis/queue support
- Blade + Vue 2 + Vite
- PHPUnit, Dusk, and Playwright
- Docker Compose and Makefile-driven workflows

### Get started

For help getting started, take a look at the wiki first:

* [Installation](https://github.com/Bottelet/DaybydayCRM/wiki/Install)
* [Installation with Docker](https://github.com/Bottelet/DaybydayCRM/wiki/Install-using-Docker)
* [Insertion of dummy data](https://github.com/Bottelet/DaybydayCRM/wiki/Insertion-of-dummy-data)

#### Quick start with Docker
```bash
make up
make dsh
make setup
```

#### Quick start on host
```bash
composer install
yarn install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
yarn run build
composer dev
```

### Useful commands

#### PHPUnit testing
- `make test` — run PHPUnit with stop-on-failure behavior
- `make test-filter f=SomeTest` — run a filtered PHPUnit subset
- `make test-fail` — run PHPUnit, stop on first failure
- `make paratest` — run tests in parallel

#### Playwright e2e testing
- `make e2e-install` — install Playwright and browser dependencies
- `make e2e-test` — run all Playwright e2e tests
- `make e2e-test STOP_ON_FAILURE=true` — run all tests, stop on first failure
- `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js` — run a single spec file
- `make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true` — run single spec, stop on failure
- `make e2e-fail` — run Playwright tests, stop on first failure
- `make e2e-list` — list all discovered Playwright tests
- `npm run test:e2e` — run e2e tests directly via npm
- `npm run test:e2e:stop-on-failure` — run e2e tests, stop on first failure

#### Database and maintenance
- `make clear` — clear Laravel caches
- `make dmfs` — fresh migrate/seed inside Docker
- `make dseed` — seed demo and dummy data inside Docker

#### Code quality
- `git ls-files '*.php' | xargs -n1 php -l` — minimum required PHP syntax lint before push/PR

### Repository guide
- `AGENTS.md` — contributor and AI-agent workflow guide
- `.github/ARCHITECTURE.md` — architecture and technical debt notes
- `.github/TESTING.md` — testing and isolation standards
- `.github/ROADMAP.md` — current modernization roadmap
- `.github/copilot-instructions.md` — concise Copilot-specific guidance
- `.junie/*.md` — short operational summaries for analysis, testing, fixes, and refactors
- `CHANGELOG.md` — current branch changelog summary

### Architecture snapshot
DaybydayCRM follows a layered Laravel architecture:

`Routes -> Middleware -> Controllers -> Services/Actions -> Repositories/Models -> Views or JSON responses`

Current repository conventions emphasize:
- thin controllers
- FormRequest-based validation
- service/action extraction for business logic
- enums and helpers for fixed value sets
- observer and trait-based model behavior
- explicit JSON vs web response handling

### Contribution Guide
DaybydayCRM follows [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standards.

Before pushing changes:
- keep tests self-contained and factory-driven
- place new HTTP/controller coverage in `tests/Feature/*`
- normalize dates before assertions
- refresh users after permission changes in tests
- run `git ls-files '*.php' | xargs -n1 php -l`

If workflows are available, all tests should pass on GitHub Actions, or failing expectations should be updated to reflect intentional behavior changes.

### Feedback
Feel free to send feedback on [Twitter](https://twitter.com/Cbottelet) or [file an issue](https://github.com/bottelet/DaybydayCRM/issues/new). Feature requests are always welcome. If you want to contribute, please take a quick look at the repository guidance above.

### Localization
You can help translate DaybydayCRM into other languages by copying `resources/lang/en` into, for example, `resources/lang/de` and translating the files inside that folder.

### Licenses
DaybydayCRM from version 2.0.0 and up is open-sourced software licensed under the [GNU GPLv3](https://opensource.org/licenses/GPL-3.0).
[FAQ GPL](https://www.gnu.org/licenses/gpl-faq.html#DoesFreeSoftwareMeanUsingTheGPL)

DaybydayCRM under and not including version 2.0.0 is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
