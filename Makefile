# ============================================================================
# Makefile for DaybydayCRM
#
# Professional, unified workflow for Laravel, Docker, and CI environments.
# - Supports local and Docker-based development
# - Provides standardized install, build, cache, and test commands
# - Includes robust support for parallel testing (ParaTest)
# - All targets are safe to run repeatedly
#
# Usage:
#   make <target>
#   make help   # for full list
#
# ============================================================================

# --- Install & Setup (inside container) ---
install:
	composer install

mfs:
	php artisan migrate:fresh --seed

yarn-install:
	yarn install

yarn-build:
	yarn run build

setup: install mfs yarn-install yarn-build

# --- Laravel Cache Clear (inside container) ---
clear:
	php artisan config:clear
	php artisan cache:clear
	php artisan route:clear
	php artisan view:clear

aargh:
	composer aargh

# --- PHPUnit Tests (inside container) ---
phpunit:
	vendor/bin/phpunit

phpunit-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make phpunit-group group=crud"; \
		exit 1; \
	fi
	vendor/bin/phpunit --group $(group)

testdox:
	vendor/bin/phpunit --testdox

testdox-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make testdox-group group=crud"; \
		exit 1; \
	fi
	vendor/bin/phpunit --testdox --group $(group)

test-log:
	vendor/bin/phpunit 2>&1 | tee phpunit-full.log

testdox-log:
	vendor/bin/phpunit --testdox 2>&1 | tee phpunit-testdox.log

test-fail-fast:
	vendor/bin/phpunit --stop-on-failure

# --- ParaTest: Parallel Testing (inside container) ---
# Run tests in parallel for faster feedback. Use -p8 for 8 processes (adjust as needed).
paratest:
	vendor/bin/paratest -p8

paratest-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make paratest-group group=crud"; \
		exit 1; \
	fi
	vendor/bin/paratest -p8 --group $(group)

paratest-testdox:
	vendor/bin/paratest -p8 --testdox

paratest-testdox-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make paratest-testdox-group group=crud"; \
		exit 1; \
	fi
	vendor/bin/paratest -p8 --testdox --group $(group)

paratest-log:
	vendor/bin/paratest -p8 2>&1 | tee paratest-full.log

paratest-testdox-log:
	vendor/bin/paratest -p8 --testdox 2>&1 | tee paratest-testdox.log

paratest-fail-fast:
	vendor/bin/paratest -p8 --stop-on-failure

# --- Docker Compose Targets (for HOST use) ---
down:
	docker-compose down -v

up:
	docker-compose up -d

downup:
	docker-compose down -v && docker-compose up -d

docker-rebuild:
	docker-compose down -v
	docker system prune -af
	docker-compose build --no-cache
	docker-compose up -d

docker-install:
	docker-compose exec php composer install

docker-mfs:
	docker-compose exec php php artisan migrate:fresh --seed

docker-yarn-install:
	docker-compose exec php yarn install

docker-yarn-dev:
	docker-compose exec php yarn run dev

docker-setup: docker-install docker-mfs docker-yarn-install docker-yarn-dev

docker-clear:
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

docker-aargh:
	docker-compose exec php composer aargh

docker-phpunit:
	docker-compose exec php vendor/bin/phpunit

docker-phpunit-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make docker-phpunit-group group=crud"; \
		exit 1; \
	fi
	docker-compose exec php vendor/bin/phpunit --group $(group)

docker-testdox:
	docker-compose exec php vendor/bin/phpunit --testdox

docker-testdox-group:
	@if [ -z "$(group)" ]; then \
		echo "Usage: make docker-testdox-group group=crud"; \
		exit 1; \
	fi
	docker-compose exec php vendor/bin/phpunit --testdox --group $(group)

docker-test-log:
	docker-compose exec php vendor/bin/phpunit 2>&1 | tee phpunit-full.log

docker-testdox-log:
	docker-compose exec php vendor/bin/phpunit --testdox 2>&1 | tee phpunit-testdox.log

docker-test-fail-fast:
	docker-compose exec php vendor/bin/phpunit --stop-on-failure

# --- Quick Shell Helpers ---
shell:
	docker exec -it $$(docker ps -aqf "name=workspace") bash

# --- Help ---
help:
	@echo ""
	@echo "DaybydayCRM Makefile — Professional Laravel/Docker/CI workflow"
	@echo ""
	@echo "Inside-container targets:"
	@echo "  setup            = install, migrate, yarn, dev"
	@echo "  clear            = clear all Laravel caches"
	@echo "  phpunit          = run all tests"
	@echo "  phpunit-group    = phpunit --group=<group>   (make phpunit-group group=crud)"
	@echo "  testdox          = phpunit --testdox"
	@echo "  testdox-group    = phpunit --testdox --group=crud"
	@echo "  test-log         = logs to phpunit-full.log"
	@echo "  testdox-log      = logs to phpunit-testdox.log"
	@echo "  test-fail-fast   = stop on first failure"
	@echo ""
	@echo "  paratest         = run all tests in parallel (ParaTest)"
	@echo "  paratest-group   = paratest --group=<group> (make paratest-group group=crud)"
	@echo "  paratest-testdox = paratest --testdox"
	@echo "  paratest-testdox-group = paratest --testdox --group=crud"
	@echo "  paratest-log     = logs to paratest-full.log"
	@echo "  paratest-testdox-log = logs to paratest-testdox.log"
	@echo "  paratest-fail-fast = stop on first failure (parallel)"
	@echo ""
	@echo "Docker targets (from host):"
	@echo "  down, up, downup, docker-rebuild, shell"
	@echo "  docker-install, docker-mfs, docker-yarn-install, docker-yarn-dev"
	@echo "  docker-setup     = all setup steps"
	@echo "  docker-clear     = clear all Laravel caches"
	@echo "  docker-aargh     = composer aargh"
	@echo "  docker-phpunit, docker-phpunit-group, docker-test-log, etc."
	@echo ""
	@echo "Usage from host:"
	@echo "  make docker-phpunit"
	@echo "Usage from container:"
	@echo "  make phpunit"
	@echo ""

.DEFAULT_GOAL := help
