.PHONY: up down restart logs bash composer npm artisan

up:
	docker compose up --build

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

bash:
	docker compose exec app bash

composer:
	docker compose exec app composer $(cmd)

npm:
	docker compose exec app npm $(cmd)

artisan:
	docker compose exec app php artisan $(cmd)
