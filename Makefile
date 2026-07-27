# ============================================================================
# Makefile for DaybydayCRM
# Unified Docker & Host Development Workflow
# ============================================================================
#
# This Makefile provides a unified interface for common development tasks,
# supporting both host and Docker container execution. It is designed for
# developer productivity, CI/CD, and onboarding ease.
#
# Usage:
#   - Run from project root: `make <target>`
#   - For Dockerized workflows, ensure containers are running (`make up`).
#
# Primary Targets:
#   dtest      Run PHPUnit tests inside Docker (optionally filter by name)
#   dfail      Run all tests, stop on first failure
#   e2e-install Install frontend deps for Playwright and download Chromium
#   e2e-test   Run the Playwright e2e suite
#   e2e-test-one Run a single Playwright spec file
#   e2e-fail   Run Playwright tests, stop on first failure
#   e2e-list   List discovered Playwright tests
#   dsh        Open a shell in the workspace container
#   dmfs       Fresh migrate and seed database inside Docker
#   dseed      Fresh migrate and seed with demo + dummy data inside Docker
#   install    Composer install (inside container)
#   mfs        Fresh migrate/seed (inside container)
#   seed       Fresh migrate/seed with demo + dummy data (inside container)
#   yarn-setup Install JS deps and build (inside container)
#   setup      Full setup: composer, migrate, yarn
#   clear      Clear Laravel caches (inside container)
#   phpunit    Run PHPUnit (inside container)
#   test-fail  Run PHPUnit, stop on failure (inside container)
#   test-filter Run PHPUnit with filter (inside container)
#   paratest   Run tests in parallel (inside container)
#   parafail   Parallel tests, stop on failure (inside container)
#   up         Start Docker containers
#   down       Stop and remove Docker containers
#   rebuild    Rebuild Docker containers
#   help       Show this help message
#
# See also: README.md for onboarding and troubleshooting.
#
# -----------------------------------------------------------------------------

# --- Configuration ---
CONTAINER_NAME := phpMain
DOCKER_USER    := daybyday
E2E_SPEC       ?=
E2E_ARGS       ?=
STOP_ON_FAILURE ?= false
# Dynamic container lookup, matches container_name in docker-compose.yml
DOCKER_EXEC    := docker exec -t --user=$(DOCKER_USER) $$(docker ps -aqf "name=$(CONTAINER_NAME)")

# --- Primary Entry Points (Host) ---

# Run a specific test from host: make dtest f=ProjectsControllerTest
dtest:
	@$(DOCKER_EXEC) bash -c 'test -f .env.testing || cp .env.testing.example .env.testing'
	@$(DOCKER_EXEC) bash -c 'unset CACHE_STORE; APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure $(if $(f),--filter $(f),)'

# Run all tests until first failure: make dfail
dfail:
	@$(DOCKER_EXEC) bash -c 'test -f .env.testing || cp .env.testing.example .env.testing'
	@$(DOCKER_EXEC) bash -c 'unset CACHE_STORE; APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure'

# Quick shell access: make dsh
dsh:
	docker exec -it --user=$(DOCKER_USER) $$(docker ps -aqf "name=$(CONTAINER_NAME)") bash

# Fresh migration and seed from host: make dmfs
dmfs:
	@$(DOCKER_EXEC) php artisan migrate:fresh --seed

# Seed database with demo and dummy data from host: make dseed
dseed:
	@$(DOCKER_EXEC) php artisan migrate:fresh --seed && $(DOCKER_EXEC) php artisan db:seed --class=DemoTableSeeder && $(DOCKER_EXEC) php artisan db:seed --class=DummyDatabaseSeeder

# --- Inside-Container Targets (Local PHP) ---

install:
	@test -f .env || cp .env.example .env
	composer install
	@grep -q '^APP_KEY=base64:' .env || php artisan key:generate --ansi

mfs:
	php artisan migrate:fresh --seed

seed:
	php artisan migrate:fresh --seed && php artisan db:seed --class=DemoTableSeeder && php artisan db:seed --class=DummyDatabaseSeeder

yarn-setup:
	yarn install && yarn run build

setup: install mfs yarn-setup

e2e-install:
	yarn install --frozen-lockfile
	yarn run e2e:install

# e2e-* targets run Playwright's browser inside this container, where "nginx" is
# the reachable host, unlike the "localhost" that docker-compose.env's APP_URL
# uses for a human browsing from outside on the host.
# APP_URL must match too: AppServiceProvider calls URL::forceRootUrl(config('app.url'))
# outside the testing env, so login/redirect targets would otherwise resolve to
# this container's own loopback (nothing listens on :80 there) instead of nginx.
E2E_ENV := PLAYWRIGHT_BASE_URL=http://nginx APP_URL=http://nginx

e2e-test:
	@if [ "$(STOP_ON_FAILURE)" = "true" ]; then \
		$(E2E_ENV) yarn run test:e2e:stop-on-failure -- $(E2E_ARGS); \
	else \
		$(E2E_ENV) yarn run test:e2e -- $(E2E_ARGS); \
	fi

# Usage: make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js
# Usage: make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true
e2e-test-one:
	@test -n "$(E2E_SPEC)" || { echo "Usage: make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js E2E_ARGS='--project=chromium'"; exit 1; }
	@if [ "$(STOP_ON_FAILURE)" = "true" ]; then \
		$(E2E_ENV) yarn run test:e2e:stop-on-failure -- $(E2E_SPEC) $(E2E_ARGS); \
	else \
		$(E2E_ENV) yarn run test:e2e:file -- $(E2E_SPEC) $(E2E_ARGS); \
	fi
# Run Playwright tests, stop on first failure: make e2e-fail
e2e-fail:
	$(E2E_ENV) yarn run test:e2e:stop-on-failure -- $(E2E_ARGS)

e2e-list:
	yarn run test:e2e:list

clear:
	php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# --- Standard Testing ---

# .env.testing is gitignored (local dev keys/passwords shouldn't get
# overwritten by every checkout); regenerate it from .env.testing.example,
# mirroring the "Prepare .env" step in .github/workflows/phpunit.yml.
env-testing:
	@test -f .env.testing || cp .env.testing.example .env.testing

# CACHE_STORE is exported into the container's real environment (docker-compose.env)
# so normal browsing hits Redis; Laravel's dotenv never overrides an already-set
# env var, so .env.testing's CACHE_STORE=array would otherwise be ignored here.
# DB_HOST etc. are intentionally left alone: inside this container "db" is the
# real MySQL host, unlike .env.testing.example's 127.0.0.1 (correct for CI/host runs).
phpunit: env-testing
	@unset CACHE_STORE; vendor/bin/phpunit

test: env-testing
	@unset CACHE_STORE; APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure --stop-on-error

test-fail: env-testing
	@unset CACHE_STORE; APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure

# Usage: make test-filter f=SomeTest
test-filter: env-testing
	@unset CACHE_STORE; APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --filter $(f) --stop-on-failure --stop-on-error

# --- Parallel Testing (Inside Container) ---

paratest: env-testing
	@unset CACHE_STORE; APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16 > phpunit-testdox.log 2>&1 || (cat phpunit-testdox.log >&2; exit 1)

parafail: env-testing
	@unset CACHE_STORE; APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16 --stop-on-failure > phpunit-testdox.log 2>&1 || (cat phpunit-testdox.log >&2; exit 1)

# --- Docker Compose (Host Level) ---

up:
	docker-compose up -d

down:
	docker-compose down -v

rebuild:
	docker-compose down -v && docker-compose build --no-cache && docker-compose up -d

# --- Help ---

help:
	@echo "======================================================================"
	@echo "HOST COMMANDS (Run these from your terminal):"
	@echo "  make dtest f=<name>  : Run specific test (e.g., make dtest f=ProjectsControllerTest)"
	@echo "  make dfail           : Run all tests, stop on first error"
	@echo "  make dsh             : Enter the workspace container as $(DOCKER_USER)"
	@echo "  make dmfs            : Fresh migrate/seed inside container"
	@echo "  make dseed           : Fresh migrate + demo + dummy seed inside container"
	@echo "  make up / make down  : Manage docker-compose"
	@echo ""
	@echo "CONTAINER COMMANDS (Run these inside 'make dsh'):"
	@echo "  make setup           : Install composer/yarn and migrate"
	@echo "  make seed            : Fresh migrate + demo + dummy seed"
	@echo "  make test-fail       : Run phpunit until failure"
	@echo "  make e2e-install     : Install npm deps for Playwright and download Chromium"
	@echo "  make e2e-test        : Run all Playwright e2e tests"
	@echo "  make e2e-test-one    : Run one Playwright spec (set E2E_SPEC=tests/e2e/auth/auth.spec.js)"
	@echo "  make e2e-fail        : Run Playwright tests, stop on first failure"
	@echo "  make e2e-list        : List discovered Playwright tests"
	@echo "  make paratest        : Run tests in parallel"
	@echo ""
	@echo "E2E OPTIONS:"
	@echo "  STOP_ON_FAILURE=true : Stop on first failure (use with e2e-test or e2e-test-one)"
	@echo "  E2E_ARGS='...'       : Pass additional arguments to Playwright"
	@echo "  E2E_SPEC=path        : Specify test file for e2e-test-one"
	@echo ""
	@echo "EXAMPLES:"
	@echo "  make e2e-test STOP_ON_FAILURE=true"
	@echo "  make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js STOP_ON_FAILURE=true"
	@echo "======================================================================"

.DEFAULT_GOAL := help
