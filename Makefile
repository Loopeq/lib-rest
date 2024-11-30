COMPOSE = docker-compose

.PHONY: down up migrate

down:
	$(COMPOSE) down

up:
	$(COMPOSE) up --build -d

migrate:
	$(COMPOSE) run --rm migrate
