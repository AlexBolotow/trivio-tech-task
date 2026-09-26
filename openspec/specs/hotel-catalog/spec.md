# Hotel catalog specification

## Requirement: List canonical hotels by city

Система SHALL предоставлять постраничный список активных канонических отелей для указанного города через `GET /api/v1/catalog/hotels`.

### Scenario: City contains active hotels

GIVEN в каталоге существуют активные и неактивные отели разных городов
WHEN клиент передаёт корректный положительный `city_id`
THEN система отвечает HTTP 200
AND возвращает только активные отели указанного города
AND сортирует отели по названию и затем по идентификатору.

### Scenario: City has no catalog entries

GIVEN `city_id` имеет корректный формат, но в каталоге нет активных отелей этого города
WHEN клиент запрашивает список отелей
THEN система отвечает HTTP 200
AND возвращает пустой список с метаданными пагинации, где `total` равен 0 и `last_page` равен 1.

## Requirement: Expose stable catalog data

Каждый элемент списка SHALL содержать только стабильные данные канонического каталога.

### Scenario: Hotel contains room types

GIVEN активный отель имеет активные и неактивные типы номеров
AND активные типы номеров имеют фотографии с порядком отображения
WHEN отель попадает в ответ каталога
THEN ответ содержит `id`, `name`, `address` и `room_types` отеля
AND содержит только активные типы номеров, отсортированные по `code` и `id`
AND каждый тип содержит `id`, `code`, `name`, `max_adults`, `max_children`, `max_total_guests` и `photos`
AND фотографии отсортированы по `sort_order` и затем по `id`.

### Scenario: Catalog data is not availability

WHEN клиент получает ответ каталога
THEN ответ не содержит цены, offer ID или утверждения о доступности
AND названия полей не представляют типы номеров как доступные на конкретные даты.

## Requirement: Validate query parameters

Система SHALL отклонять некорректные параметры запроса до выполнения catalog query.

### Scenario: Missing or invalid city identifier

WHEN клиент не передаёт `city_id` или передаёт значение меньше 1 либо не integer
THEN система отвечает HTTP 422
AND возвращает ошибку валидации для `city_id`.

### Scenario: Invalid pagination

WHEN передан `page` меньше 1 либо `per_page` вне диапазона от 1 до 50
THEN система отвечает HTTP 422
AND возвращает ошибку для соответствующего параметра.

### Scenario: Pagination defaults

WHEN клиент не передаёт `page` и `per_page`
THEN система использует `page=1` и `per_page=20`.

## Requirement: Paginate catalog results

Система SHALL ограничивать размер ответа и предоставлять метаданные пагинации.

### Scenario: City contains more hotels than one page

GIVEN число активных отелей города превышает `per_page`
WHEN клиент запрашивает страницу каталога
THEN система возвращает не более `per_page` отелей
AND предоставляет `current_page`, `per_page`, `total` и `last_page`.

### Scenario: Catalog response envelope

WHEN клиент запрашивает страницу каталога
THEN JSON содержит массив отелей в `data`
AND метаданные пагинации в объекте `meta`.
