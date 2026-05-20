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
CONTAINER_NAME := workspace
DOCKER_USER    := ivpldock
# Dynamic container lookup for Laradock-style naming
DOCKER_EXEC    := docker exec -t --user=$(DOCKER_USER) $$(docker ps -aqf "name=$(CONTAINER_NAME)")

# --- Primary Entry Points (Host) ---

# Run a specific test from host: make dtest f=ProjectsControllerTest
dtest:
	@$(DOCKER_EXEC) vendor/bin/phpunit --exclude-group flaky --stop-on-failure $(if $(f),--filter $(f),)

# Run all tests until first failure: make dfail
dfail:
	@$(DOCKER_EXEC) vendor/bin/phpunit --exclude-group flaky --stop-on-failure

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
	composer install

mfs:
	php artisan migrate:fresh --seed

seed:
	php artisan migrate:fresh --seed && php artisan db:seed --class=DemoTableSeeder && php artisan db:seed --class=DummyDatabaseSeeder

yarn-setup:
	yarn install && yarn run build

setup: install mfs yarn-setup

clear:
	php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# --- Standard Testing ---

phpunit:
	vendor/bin/phpunit

test:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure --stop-on-error

test-fail:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure

# Usage: make test-filter f=SomeTest
test-filter:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --filter $(f) --stop-on-failure --stop-on-error

# --- Parallel Testing (Inside Container) ---

paratest:
	APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16 > phpunit-testdox.log 2>&1 || (cat phpunit-testdox.log >&2; exit 1)

parafail:
	APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16 --stop-on-failure > phpunit-testdox.log 2>&1 || (cat phpunit-testdox.log >&2; exit 1)

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
	@echo "  make paratest        : Run tests in parallel"
	@echo "======================================================================"

.DEFAULT_GOAL := help
