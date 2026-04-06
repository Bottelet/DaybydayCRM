# Makefile for DaybydayCRM Docker/Laravel workflow
# Bring containers down (with volumes) and up again
down:
	docker-compose down -v

up:
	docker-compose up -d

install:
	docker-compose exec php composer install

migrate-seed:
	docker-compose exec php php artisan migrate:fresh --seed

npm-install:
	docker-compose exec php npm install

npm-dev:
	docker-compose exec php npm run dev

setup: install migrate-seed npm-install npm-dev

# Bring containers down (with volumes) and up again
downup:
	docker-compose down -v
	docker-compose up -d

workmeup:
	docker exec -it $(docker ps -aqf "name=php") bash

shell:
	docker exec $(docker ps -aqf "name=workspace") bash

clear:
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan cache:clear

docker-rebuild:
	docker-compose down -v
	docker system prune -af
	docker-compose build --no-cache
	docker-compose up -d

test-phpunit:
	docker-compose exec php vendor/bin/phpunit

test-artisan:
	docker-compose exec php php artisan test
