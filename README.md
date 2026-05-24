<a href="https://daybydaycrm.com/">
    <img src="https://user-images.githubusercontent.com/15610490/69175894-ed771300-0b04-11ea-9ecd-a5ad6e3d8877.png" height="100" alt="DaybydayCRM logo" />
</a>

======================

![GitHub Workflow Status](https://img.shields.io/github/workflow/status/bottelet/DaybydayCRM/Run%20tests?style=for-the-badge)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/bottelet/DaybydayCRM?label=Latest%20version&style=for-the-badge)
![](https://img.shields.io/david/bottelet/DaybydayCRM?style=for-the-badge)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=for-the-badge&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIGlkPSJzdmcyIiB3aWR0aD0iNjQ1IiBoZWlnaHQ9IjU4NSIgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPiA8ZyBpZD0ibGF5ZXIxIj4gIDxwYXRoIGlkPSJwYXRoMjQxNyIgZD0ibTI5Ny4zIDU1MC44N2MtMTMuNzc1LTE1LjQzNi00OC4xNzEtNDUuNTMtNzYuNDM1LTY2Ljg3NC04My43NDQtNjMuMjQyLTk1LjE0Mi03Mi4zOTQtMTI5LjE0LTEwMy43LTYyLjY4NS01Ny43Mi04OS4zMDYtMTE1LjcxLTg5LjIxNC0xOTQuMzQgMC4wNDQ1MTItMzguMzg0IDIuNjYwOC01My4xNzIgMTMuNDEtNzUuNzk3IDE4LjIzNy0zOC4zODYgNDUuMS02Ni45MDkgNzkuNDQ1LTg0LjM1NSAyNC4zMjUtMTIuMzU2IDM2LjMyMy0xNy44NDUgNzYuOTQ0LTE4LjA3IDQyLjQ5My0wLjIzNDgzIDUxLjQzOSA0LjcxOTcgNzYuNDM1IDE4LjQ1MiAzMC40MjUgMTYuNzE0IDYxLjc0IDUyLjQzNiA2OC4yMTMgNzcuODExbDMuOTk4MSAxNS42NzIgOS44NTk2LTIxLjU4NWM1NS43MTYtMTIxLjk3IDIzMy42LTEyMC4xNSAyOTUuNSAzLjAzMTYgMTkuNjM4IDM5LjA3NiAyMS43OTQgMTIyLjUxIDQuMzgwMSAxNjkuNTEtMjIuNzE1IDYxLjMwOS02NS4zOCAxMDguMDUtMTY0LjAxIDE3OS42OC02NC42ODEgNDYuOTc0LTEzNy44OCAxMTguMDUtMTQyLjk4IDEyOC4wMy01LjkxNTUgMTEuNTg4LTAuMjgyMTYgMS44MTU5LTI2LjQwOC0yNy40NjF6IiBmaWxsPSIjZGQ1MDRmIi8%2BIDwvZz48L3N2Zz4%3D)](http://makeapullrequest.com)
![Twitter URL](https://img.shields.io/twitter/url?color=%2300acee&style=for-the-badge&url=https%3A%2F%2Fgithub.com%2Fbottelet%2Fdaybydaycrm)

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
- `make clear` — clear Laravel caches
- `make test` — run PHPUnit with stop-on-failure behavior
- `make test-filter f=SomeTest` — run a filtered PHPUnit subset
- `make paratest` — run tests in parallel
- `make dmfs` — fresh migrate/seed inside Docker
- `make dseed` — seed demo and dummy data inside Docker
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
