SHELL := /bin/sh

COMPOSE := docker compose
SERVICE ?=
ARGS ?=
HOST_UID ?= $(shell id -u)
HOST_GID ?= $(shell id -g)

export HOST_UID
export HOST_GID

.DEFAULT_GOAL := help

.PHONY: help env build up composer-install app-key migrate down reset ps logs shell artisan composer test

help: ## Показать доступные команды
	@awk 'BEGIN {FS = ":.*##"}; /^[a-zA-Z_-]+:.*##/ {printf "  %-18s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

env: ## Создать локальный .env из безопасного примера, если его ещё нет
	@test -f .env || (cp .env.example .env && printf '%s\n' 'Created .env from .env.example')

build: env ## Собрать development-образ PHP
	$(COMPOSE) build app

up: env ## Поднять окружение, установить зависимости и дождаться healthcheck-ов
	$(COMPOSE) up --detach --build --remove-orphans --wait mysql app
	$(MAKE) composer-install
	$(MAKE) app-key
	$(MAKE) migrate
	$(COMPOSE) up --detach --remove-orphans --wait
	@url="$$( $(COMPOSE) port nginx 80 | head -n 1 | sed 's#^0\.0\.0\.0:#localhost:#; s#^\[::\]:#localhost:#')"; \
		printf 'Application health: http://%s/health\n' "$$url"

composer-install: ## Установить Composer-зависимости внутри app-контейнера
	$(COMPOSE) exec -T app composer install --no-interaction --prefer-dist

app-key: ## Сгенерировать APP_KEY, если он отсутствует
	@if ! grep -Eq '^APP_KEY=.+$$' .env; then \
		$(COMPOSE) exec -T app php artisan key:generate; \
	fi

migrate: ## Выполнить миграции Laravel
	$(COMPOSE) exec -T app php artisan migrate --force

down: ## Остановить окружение, сохранив данные MySQL
	$(COMPOSE) down --remove-orphans

reset: ## Удалить контейнеры и integration MySQL volume; личная dev-база сохраняется
	@printf '%s\n' 'Warning: integration MySQL data will be permanently removed; developer database volume is preserved.'
	$(COMPOSE) down --volumes --remove-orphans

ps: ## Показать состояние сервисов
	$(COMPOSE) ps

logs: ## Показать логи; можно указать SERVICE=nginx
	$(COMPOSE) logs --follow --tail=100 $(SERVICE)

shell: ## Открыть shell в app-контейнере
	$(COMPOSE) exec app sh

artisan: ## Запустить Artisan, например: make artisan ARGS='about'
	$(COMPOSE) exec app php artisan $(ARGS)

composer: ## Запустить Composer, например: make composer ARGS='validate --strict'
	$(COMPOSE) exec app composer $(ARGS)

test: ## Запустить PHPUnit на отдельной MySQL-схеме trivio_testing внутри app-контейнера
	$(COMPOSE) exec -T app php artisan test
