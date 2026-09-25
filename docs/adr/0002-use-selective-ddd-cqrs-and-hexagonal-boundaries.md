# ADR-0002: Use selective DDD, CQRS-lite and hexagonal boundaries

- Status: Accepted
- Date: 2026-09-25

## Context

В Booking есть инварианты и переходы состояния, тогда как каталог, импорт и поисковые проекции преимущественно data-oriented. Система также зависит от API поставщиков, MySQL, Redis, RabbitMQ и Elasticsearch.

## Decision

Использовать DDD только в областях с реальными бизнес-правилами, CQRS-lite для разделения Commands и Queries и порты/адаптеры на внешних границах. Это не подразумевает отдельные базы, обязательную асинхронность или интерфейс для каждого класса.

## Alternatives

- Полностью framework-centric Laravel architecture.
- Строгая Clean/Hexagonal Architecture во всех частях приложения.
- Полный CQRS с раздельными write/read stores.

## Consequences

- Бизнес-критичный код можно тестировать без Laravel и внешних систем.
- Read side может использовать наиболее подходящий способ чтения.
- Потребуется осознанно отличать Domain Command/Event от Laravel Job/Event.
- Архитектурная церемония вводится только при наличии причины.
