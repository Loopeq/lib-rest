COMPOSE = docker-compose

APP_NAME = laravel_app

.PHONY: down up migrate

down:
	$(COMPOSE) down

up:
	$(COMPOSE) up --build -d

migrate:
	$(COMPOSE) run migrate