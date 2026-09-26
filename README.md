# Trivio Tech Task

Учебная реализация модуля отельных услуг на Laravel по материалам system design Trivio.

## Требования

- Docker Desktop с Docker Compose;
- GNU Make;
- Git.

Локальные PHP, Composer, MySQL и Nginx не требуются.

## Первый запуск

```bash
git clone https://github.com/AlexBolotow/trivio-tech-task.git
cd trivio-tech-task
cp .env.example .env
make up
```

`make up`:

1. собирает development PHP-образ;
2. запускает MySQL и PHP-FPM;
3. устанавливает Composer-зависимости в bind-mounted `vendor/`;
4. генерирует `APP_KEY`, если он отсутствует;
5. выполняет миграции;
6. запускает Nginx и ждёт healthcheck-ов.

После запуска endpoint состояния доступен по адресу:

```text
http://localhost:8080/health
```

Ожидаемый ответ:

```json
{"service":"trivio-tech-task","status":"ok"}
```

Если порт занят, измени `APP_HTTP_PORT` в локальном `.env`. Внешний порт MySQL задаётся через `DB_FORWARD_PORT`; внутри Docker-сети Laravel всегда использует `mysql:3306`.

## Сервисы первого этапа

| Сервис | Назначение |
|---|---|
| `nginx` | Входящий HTTP и статические файлы |
| `app` | PHP 8.4, PHP-FPM, Laravel, Composer и development Xdebug |
| `mysql` | MySQL 8.4 с постоянным named volume |

Redis, RabbitMQ и Elasticsearch намеренно будут добавлены только вместе со сценариями, которым они нужны.

## Команды

| Команда | Назначение |
|---|---|
| `make up` | Собрать и запустить окружение, дождаться healthcheck-ов |
| `make down` | Остановить контейнеры, сохранив MySQL volume |
| `make reset` | Удалить контейнеры и MySQL volume; данные будут потеряны |
| `make ps` | Показать состояние сервисов |
| `make logs` | Следить за логами всех сервисов |
| `make logs SERVICE=nginx` | Следить за логами выбранного сервиса |
| `make shell` | Открыть shell внутри app-контейнера |
| `make artisan ARGS='about'` | Выполнить Artisan-команду |
| `make composer ARGS='validate --strict'` | Выполнить Composer-команду |
| `make migrate` | Выполнить миграции |
| `make test` | Запустить PHPUnit на отдельной MySQL-схеме `trivio_testing` |

Тесты подключаются к текущему MySQL-сервису `mysql`, но используют собственную схему `trivio_testing`. Локальная база приложения (`mysql-dev`) и основная схема `trivio` в тестах не используются. Тестовую схему можно очищать и пересоздавать без потери локальных данных разработки.

## Xdebug

Xdebug установлен только в development target и по умолчанию выключен. Для включения измени в локальном `.env`:

```dotenv
XDEBUG_MODE=debug
```

Затем пересоздай app-контейнер:

```bash
docker compose up -d --force-recreate app nginx
```

Xdebug подключается к `host.docker.internal:9003`.

## Конфигурация

Один корневой `.env` используется Laravel и Docker Compose. Он создаётся из `.env.example`, содержит только локальные значения и не коммитится. Значения внутри `.env.example` предназначены только для development.

Docker images зафиксированы конкретными версиями и multi-platform digest. UID/GID пользователя передаются из Makefile во время сборки, чтобы файлы `vendor/`, кеши и логи оставались редактируемыми на host.

## Архитектура и процесс

- Постоянные правила проекта: [`AGENTS.md`](AGENTS.md).
- Архитектурные решения: [`docs/adr`](docs/adr).
- Актуальные и предлагаемые спецификации: [`openspec`](openspec).
