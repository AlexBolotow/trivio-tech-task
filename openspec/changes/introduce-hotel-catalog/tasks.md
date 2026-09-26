# Tasks: Introduce hotel catalog

- [x] Получить approval поведения и компромиссов из `design.md`.
- [x] Добавить миграции `cities`, `hotels`, `room_types` и `room_type_photos` с индексами и constraints.
- [x] Добавить Catalog Eloquent-модели и отношения без публичной ORM-сериализации.
- [x] Добавить factories и небольшой локальный catalog seeder.
- [x] Добавить Application Query и Query Handler, общий `HotelQueryService` port с Infrastructure Query Builder адаптером и immutable read models.
- [x] Добавить Form Request, controller и API Resource для `GET /api/v1/catalog/hotels`.
- [x] Создать и зарегистрировать `CatalogServiceProvider`, привязав Query Service port к Query Builder адаптеру.
- [ ] Добавить application test на делегирование Query Handler в port.
- [x] Зарегистрировать маршрут через `CatalogServiceProvider`.
- [x] Добавить HTTP feature tests для успешной выдачи, пустого результата, фильтрации, пагинации и validation errors.
- [x] Добавить MySQL integration tests для фильтрации каталога, сортировки, пагинации и пакетного чтения вложенных данных.
- [ ] Добавить database integration tests для Eloquent-отношений и ограничений схемы.
- [ ] Обновить README примерами запуска seeder и запроса каталога.
- [ ] Выполнить миграции на чистой базе и проверить повторный запуск.
- [x] Запустить `make test`.
- [x] Запустить `composer validate --strict`.
- [ ] Сверить реализацию с proposal, specification и design.
- [ ] После успешной проверки обновить актуальную capability specification и архивировать change-пакет.
