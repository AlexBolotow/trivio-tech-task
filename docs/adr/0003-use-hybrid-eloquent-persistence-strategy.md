# ADR-0003: Use a hybrid Eloquent persistence strategy

- Status: Accepted
- Date: 2026-09-25

## Context

Eloquent реализует Active Record и удобен для data-oriented моделей и чтения. Универсальные CRUD repositories поверх него часто только дублируют API фреймворка. Вместе с тем Booking содержит агрегат и инварианты, которые не должны зависеть от ORM.

## Decision

Использовать Eloquent/Query Builder напрямую внутри query handlers и специализированных query services для каталога, импортов и read side. Сохранять Repository Port для бизнес-критичных агрегатов, прежде всего Booking; инфраструктурный adapter маппит доменную модель на persistence records. Не создавать generic/base repositories.

## Alternatives

- Использовать Eloquent-модели как доменную модель во всех модулях.
- Полностью отделить все доменные объекты от ORM.
- Создать repository для каждой таблицы.

## Consequences

- Используются сильные стороны Laravel без распространения Eloquent в Domain.
- В Booking появляется явный mapping между агрегатом и persistence records.
- Разные модули могут применять разные persistence patterns по обоснованной причине.
