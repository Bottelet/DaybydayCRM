# ============================================================================
# Makefile for DaybydayCRM (Docker & Host Unified)
# ============================================================================

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

# --- Inside-Container Targets (Local PHP) ---

install:
	composer install

mfs:
	php artisan migrate:fresh --seed

yarn-setup:
	yarn install && yarn run build

setup: install mfs yarn-setup

clear:
	php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# --- Standard Testing ---

phpunit:
	vendor/bin/phpunit

test-fail:
	vendor/bin/phpunit --exclude-group flaky --stop-on-failure

# Usage: make test-filter f=SomeTest
test-filter:
	vendor/bin/phpunit --exclude-group flaky --filter $(f) --stop-on-failure

# --- Parallel Testing (Inside Container) ---

paratest:
	vendor/bin/paratest --exclude-group flaky -p8 --stop-on-failure

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
	@echo "  make up / make down  : Manage docker-compose"
	@echo ""
	@echo "CONTAINER COMMANDS (Run these inside 'make dsh'):"
	@echo "  make setup           : Install composer/yarn and migrate"
	@echo "  make test-fail       : Run phpunit until failure"
	@echo "  make paratest        : Run tests in parallel"
	@echo "======================================================================"

.DEFAULT_GOAL := help
