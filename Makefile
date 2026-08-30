.PHONY: help up down rebuild install composer-update mfs seed yarn-setup setup clear \
         test test-fail test-filter phpunit paratest parafail \
         e2e-install e2e-test e2e-test-one e2e-fail e2e-list \
         dsh dtest dfail dmfs dseed docker-logs

# ============================================================================
# Makefile for DaybydayCRM
# Unified Docker & Host Development Workflow for PHP 8.3 + Laravel 12
# ============================================================================
#
# This Makefile provides a unified interface for common development tasks,
# supporting both host and Docker container execution.
#
# Usage: Run from project root: `make <target>`

# --- Configuration ---
CONTAINER_NAME := workspace
DOCKER_USER    := daybyday
E2E_SPEC       ?=
E2E_ARGS       ?=
STOP_ON_FAILURE ?= false

# Dynamic container lookup
DOCKER_EXEC := docker exec --user=$(DOCKER_USER)
DOCKER_IT   := docker exec -it --user=$(DOCKER_USER)
CONTAINER   := $$(docker ps -aqf "name=$(CONTAINER_NAME)")

# --- Docker Compose (Host Level) ---

build:
	docker-compose build
	@echo "✓ Docker images built successfully"

up:
	docker-compose up -d
	@echo "✓ Containers started"
	@echo "  Web:   http://localhost"
	@echo "  PHP:   localhost:9000"
	@echo "  MySQL: localhost:3306"
	@echo "  Redis: localhost:6379"

down:
	docker-compose down -v
	@echo "✓ Containers stopped and volumes removed"

rebuild: down build up
	@echo "✓ Containers rebuilt successfully"

logs:
	docker-compose logs -f

logs-php:
	docker-compose logs -f php

logs-nginx:
	docker-compose logs -f nginx

logs-db:
	docker-compose logs -f db

# --- Shell Access ---

dsh:
	@$(DOCKER_IT) $(CONTAINER) bash

# --- Docker-based Host Commands (run from host) ---

dtest:
	@$(DOCKER_EXEC) $(CONTAINER) vendor/bin/phpunit --exclude-group flaky --stop-on-failure $(if $(f),--filter $(f),)

dfail:
	@$(DOCKER_EXEC) $(CONTAINER) vendor/bin/phpunit --exclude-group flaky --stop-on-failure

dmfs:
	@$(DOCKER_EXEC) $(CONTAINER) php artisan migrate:fresh --seed

dseed:
	@$(DOCKER_EXEC) $(CONTAINER) php artisan migrate:fresh --seed
	@$(DOCKER_EXEC) $(CONTAINER) php artisan db:seed --class=DemoTableSeeder
	@$(DOCKER_EXEC) $(CONTAINER) php artisan db:seed --class=DummyDatabaseSeeder

# --- Inside-Container Targets (run inside container with `make dsh`) ---

install:
	composer install

composer-update:
	composer update

mfs:
	php artisan migrate:fresh --seed

seed:
	php artisan migrate:fresh --seed
	php artisan db:seed --class=DemoTableSeeder
	php artisan db:seed --class=DummyDatabaseSeeder

yarn-setup:
	yarn install && yarn run build

setup: install mfs yarn-setup
	@echo "✓ Full setup complete: Composer, migrations, and Yarn"

clear:
	php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
	@echo "✓ All caches cleared"

# --- PHPUnit Testing ---

phpunit:
	vendor/bin/phpunit

test:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure --stop-on-error

test-fail:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --stop-on-failure

test-filter:
	APP_ENV=testing vendor/bin/phpunit --exclude-group flaky --filter $(f) --stop-on-failure --stop-on-error

# --- Parallel Testing ---

paratest:
	APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16

parafail:
	APP_ENV=testing vendor/bin/paratest --exclude-group flaky -p16 --stop-on-failure

# --- Playwright E2E Testing ---

e2e-install:
	yarn install --frozen-lockfile
	npx playwright install --with-deps

e2e-test:
	@if [ "$(STOP_ON_FAILURE)" = "true" ]; then \
		yarn run test:e2e:stop-on-failure -- $(E2E_ARGS); \
	else \
		yarn run test:e2e -- $(E2E_ARGS); \
	fi

e2e-test-one:
	@test -n "$(E2E_SPEC)" || { echo "Usage: make e2e-test-one E2E_SPEC=tests/e2e/auth/auth.spec.js"; exit 1; }
	@if [ "$(STOP_ON_FAILURE)" = "true" ]; then \
		yarn run test:e2e:stop-on-failure -- $(E2E_SPEC) $(E2E_ARGS); \
	else \
		yarn run test:e2e:file -- $(E2E_SPEC) $(E2E_ARGS); \
	fi

e2e-fail:
	yarn run test:e2e:stop-on-failure -- $(E2E_ARGS)

e2e-list:
	yarn run test:e2e:list

# --- Help ---

help:
	@echo "=============================================================="
	@echo "  DaybydayCRM Makefile - PHP 8.3 + Laravel 12"
	@echo "=============================================================="
	@echo ""
	@echo "DOCKER MANAGEMENT (run from host):"
	@echo "  make build           Build Docker images"
	@echo "  make up              Start all Docker containers"
	@echo "  make down            Stop and remove containers"
	@echo "  make rebuild         Rebuild all containers from scratch"
	@echo ""
	@echo "DOCKER LOGS:"
	@echo "  make logs            View all container logs (follow mode)"
	@echo "  make logs-php        View PHP container logs"
	@echo "  make logs-nginx      View Nginx container logs"
	@echo "  make logs-db         View Database container logs"
	@echo ""
	@echo "HOST COMMANDS (run from host terminal):"
	@echo "  make dsh             Enter workspace container shell"
	@echo "  make dtest [f=Test]  Run specific test in Docker"
	@echo "  make dfail           Run tests, stop on failure"
	@echo "  make dmfs            Fresh migrate + seed in Docker"
	@echo "  make dseed           Fresh migrate + demo + dummy seed"
	@echo ""
	@echo "CONTAINER COMMANDS (run inside 'make dsh'):"
	@echo "  make setup           Full setup: composer, migrate, yarn"
	@echo "  make install         Composer install"
	@echo "  make mfs             Fresh migrate + seed"
	@echo "  make seed            Fresh migrate + demo + dummy seed"
	@echo "  make yarn-setup      Install and build JS deps"
	@echo "  make clear           Clear all Laravel caches"
	@echo ""
	@echo "TESTING (inside container):"
	@echo "  make test            Run PHPUnit tests"
	@echo "  make test-fail       Run tests, stop on failure"
	@echo "  make test-filter f=TestName"
	@echo "  make paratest        Run tests in parallel (16 workers)"
	@echo "  make parafail        Parallel tests, stop on failure"
	@echo ""
	@echo "E2E TESTING (Playwright):"
	@echo "  make e2e-install     Install Playwright + browsers"
	@echo "  make e2e-test        Run all e2e tests"
	@echo "  make e2e-test-one E2E_SPEC=path/to/spec.js"
	@echo "  make e2e-fail        Run e2e tests, stop on failure"
	@echo "  make e2e-list        List all discovered e2e tests"
	@echo ""
	@echo "OPTIONS (e.g., make e2e-test STOP_ON_FAILURE=true):"
	@echo "  STOP_ON_FAILURE=true Stop on first failure"
	@echo "  E2E_ARGS='...'       Pass additional arguments to Playwright"
	@echo ""
	@echo "=============================================================="

.DEFAULT_GOAL := help
