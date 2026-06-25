.PHONY: up down build restart shell artisan composer npm migrate fresh seed test logs

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

restart:
	docker compose restart

shell:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker compose exec node npm $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test

logs:
	docker compose logs -f

dev:
	docker compose --profile dev up -d

setup: build up
	@echo "Waiting for MySQL..."
	@sleep 15
	docker compose exec app composer install
	docker compose exec app php artisan key:generate --force
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan storage:link
	@echo "SALIKSIK is ready at http://localhost:8080"

%:
	@:
