# Tasks: Introduce hotel catalog

- [x] Получить approval поведения и компромиссов из `design.md`.
- [ ] Добавить миграции `cities`, `hotels`, `room_types` и `room_type_photos` с индексами и constraints.
- [ ] Добавить Catalog Eloquent-модели и отношения без публичной ORM-сериализации.
- [ ] Добавить factories и небольшой локальный catalog seeder.
- [ ] Добавить `ListHotelsQueryService` с фильтрацией, стабильным порядком и constrained eager loading.
- [ ] Добавить Form Request, controller и API Resources/read DTO для `GET /api/v1/catalog/hotels`.
- [ ] Зарегистрировать маршрут через `CatalogServiceProvider`.
- [ ] Добавить HTTP feature tests для успешной выдачи, пустого результата, фильтрации, пагинации и validation errors.
- [ ] Добавить database integration tests для отношений и ограничений схемы.
- [ ] Обновить README примерами запуска seeder и запроса каталога.
- [ ] Выполнить миграции на чистой базе и проверить повторный запуск.
- [ ] Запустить `make test` и `composer validate --strict`.
- [ ] Сверить реализацию с proposal, specification и design.
- [ ] После успешной проверки обновить актуальную capability specification и архивировать change-пакет.
