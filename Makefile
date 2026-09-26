SHELL := /bin/sh

COMPOSE := docker compose
SERVICE ?=
ARGS ?=
HOST_UID ?= $(shell id -u)
HOST_GID ?= $(shell id -g)

export HOST_UID
export HOST_GID

.DEFAULT_GOAL := help

.PHONY: help env build up composer-install app-key migrate down reset ps logs shell artisan composer test-db test cs-check cs-fix analyse

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
	$(COMPOSE) exec -T app mkdir -p bootstrap/cache storage/framework/cache/data
	$(COMPOSE) exec -T app composer install --no-interaction --prefer-dist

app-key: ## Сгенерировать APP_KEY, если он отсутствует
	@if ! grep -Eq '^APP_KEY=.+$$' .env; then \
		$(COMPOSE) exec -T app php artisan key:generate; \
	fi

migrate: ## Выполнить миграции Laravel
	$(COMPOSE) exec -T app php artisan migrate --force

down: ## Остановить окружение, сохранив данные MySQL
	$(COMPOSE) down --remove-orphans

reset: ## Удалить контейнеры и Compose-managed volumes; внешние volumes сохраняются
	@printf '%s\n' 'Warning: Compose-managed MySQL data will be permanently removed; external volumes are preserved.'
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

test-db: ## Создать изолированную MySQL-схему для тестов
	$(COMPOSE) exec -T mysql sh -c 'MYSQL_PWD="$$MYSQL_ROOT_PASSWORD" mysql --user=root' < docker/mysql/create-test-database.sql

test: test-db ## Запустить PHPUnit на отдельной MySQL-схеме trivio_testing внутри app-контейнера
	$(COMPOSE) exec -T app php artisan test

cs-check: ## Проверить PSR-12 стиль PHP-CS-Fixer
	$(COMPOSE) exec -T app composer cs:check

cs-fix: ## Исправить PSR-12 стиль PHP-CS-Fixer
	$(COMPOSE) exec -T app composer cs:fix

analyse: ## Запустить PHPStan level 6 с Larastan
	$(COMPOSE) exec -T app composer analyse
