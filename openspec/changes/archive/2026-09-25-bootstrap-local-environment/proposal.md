# Proposal: Bootstrap local development environment

## Why

Проекту нужна воспроизводимая среда, в которой Laravel и MySQL запускаются одинаково на чистом checkout без зависимости от локальных PHP и Composer.

## What changes

- Инициализируется Laravel-приложение.
- Добавляется development Docker Compose окружение.
- Добавляются PHP-FPM, Nginx и MySQL.
- Добавляются документированные команды запуска и проверки.

## Scope

- Локальная разработка на Docker Desktop.
- Конфигурируемые host-порты.
- Healthchecks и сохранение данных MySQL.
- Минимальный HTTP health endpoint.

## Non-goals

- Production deployment.
- Redis, RabbitMQ и Elasticsearch.
- CI/CD.
- Бизнес-модули и схема предметной области.
- Frontend.
