# Design: Introduce hotel catalog

- Status: Approved
- Created: 2026-09-25
- Approved: 2026-09-25

## Business scenario and invariants

Клиент просматривает стабильную каноническую часть каталога отелей выбранного города. Сценарий не отвечает на вопрос, доступен ли номер и сколько он стоит на заданные даты.

Инварианты чтения:

- наружу попадают только активные отели и типы номеров;
- отель принадлежит одному городу;
- код типа номера уникален в пределах отеля;
- порядок отелей стабилен между одинаковыми запросами;
- порядок фотографий определяется `sort_order` и идентификатором;
- ответ не содержит supplier-specific identifiers и raw payload.

## Module owner and operation type

- Владелец: `Catalog`.
- Операция: Query.
- Изменение состояния: отсутствует.
- Транзакционная граница: не требуется; выполняется обычное согласованное чтение MySQL.

## HTTP contract

```http
GET /api/v1/catalog/hotels?city_id=1&page=1&per_page=20
Accept: application/json
```

Успешный ответ:

```json
{
  "data": [
    {
      "id": "0199...",
      "name": "Grand Hotel",
      "address": "Михайловская улица, 1/7",
      "room_types": [
        {
          "id": "0199...",
          "code": "STANDARD",
          "name": "Стандартный номер",
          "max_adults": 2,
          "max_children": 1,
          "max_total_guests": 3,
          "photos": ["https://example.test/standard-1.jpg"]
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

`city_id` обязателен и должен быть положительным integer. Отсутствующий в каталоге, но синтаксически корректный идентификатор возвращает пустую страницу, а не HTTP 404. `page` по умолчанию равен 1, `per_page` — 20, максимум — 50.

## Execution path

```text
HTTP request
  -> ListHotelsRequest
  -> ListHotelsController
  -> ListHotelsQueryService
  -> Laravel Query Builder queries (hotels, room types, photos)
  -> immutable paginated read models
  -> HotelCatalogResource
  -> JSON response
```

Контроллер выполняет только HTTP mapping. `ListHotelsQueryService` владеет запросом, фильтрацией и eager loading. API Resource/read DTO владеет публичной формой ответа.

## Module layout

Каталоги создаются только вместе с реальными классами:

```text
app/Modules/Catalog/
├── Application/Queries/ListHotels/
├── Infrastructure/Persistence/Eloquent/
└── UI/Http/
```

Маршрут модуля регистрируется через `CatalogServiceProvider`, подключённый в `bootstrap/providers.php`. Query service является concrete-классом и разрешается Laravel-контейнером автоматически; явный binding пока не нужен.

## Persistence model

### `cities`

- `id` bigint primary key;
- `name` string;
- `country_code` char(2);
- unique (`country_code`, `name`).

### `hotels`

- `id` UUID primary key;
- `city_id` foreign key;
- `name`, `address`;
- nullable `latitude`, `longitude`, `description`, `check_in_time`, `check_out_time`;
- `status` string;
- timestamps;
- index (`city_id`, `status`, `name`, `id`).

### `room_types`

- `id` UUID primary key;
- `hotel_id` foreign key;
- `code`, `name`;
- nullable `max_adults`, `max_children`, `max_total_guests`, `area`;
- `status` string;
- timestamps;
- unique (`hotel_id`, `code`);
- index (`hotel_id`, `status`, `code`, `id`).

### `room_type_photos`

- `id` UUID primary key;
- `room_type_id` foreign key;
- `url` string;
- `sort_order` unsigned integer;
- timestamps;
- index (`room_type_id`, `sort_order`, `id`).

На этом этапе `status` ограничивается прикладными константами `active` и `inactive`. Database enum не используется, чтобы изменение набора статусов не требовало изменения типа колонки.

## Read model and repository decision

Каталог data-oriented и обслуживает read use case. `ListHotelsQueryService` использует Laravel Query Builder через database connection, не загружает Eloquent-модели, и возвращает отдельные immutable read models. Это оставляет use case чтения без методов persistence вроде `save()` и ограничивает результат необходимыми колонками.

Eloquent-модели остаются persistence-моделями для записи и работы с отношениями там, где это нужно. Repository и богатый Domain aggregate для этого read use case не создаются. Read models не являются Eloquent-моделями и не сериализуются ORM автоматически.

## Query behavior and performance

- Фильтрация по `city_id` и `active` выполняется в SQL.
- Порядок отелей: `name`, затем `id`.
- После получения страницы отелей типы номеров и фотографии загружаются двумя пакетными Query Builder запросами по списку родительских ID; N+1 не допускается.
- Вложенные room types допустимы в первом срезе благодаря пределу `per_page <= 50`.
- Сборка read models выполняется в памяти с группировкой дочерних строк по foreign key; каждый запрос выбирает только поля, нужные read contract.
- Транзакция для Query не открывается. При параллельном изменении каталога `total` и выбранная страница могут отражать разные моменты времени; для каталога это приемлемо, а строгий snapshot потребовал бы отдельного обоснования.
- Elasticsearch не используется: MySQL достаточно для первого точного фильтра по городу и является источником истины.

## Errors and edge cases

- Некорректные query parameters: Laravel validation response, HTTP 422.
- Корректный неизвестный `city_id`: HTTP 200 с пустой страницей.
- Отель без активных типов номеров: возвращается с пустым `room_types`; каталог не объявляет наличие.
- Недоступность MySQL: стандартный HTTP 500 с записью технической ошибки без чувствительных данных.
- Дублирующий код типа номера предотвращается unique constraint.

## Testing

- HTTP feature tests фиксируют JSON-контракт, фильтрацию, пагинацию и validation errors.
- Database integration tests проверяют scopes/relations, ограничения уникальности и отсутствие неактивных записей.
- Factories создают независимые тестовые данные.
- Локальный seeder предоставляет небольшой демонстрационный каталог и не используется как источник production-данных.

## Alternatives considered

### Использовать сразу `/api/v1/hotels`

Отклонено на этом этапе: в исходном задании этот endpoint семантически означает доступные предложения с ценами на даты и состав гостей. Статический ответ под тем же адресом создал бы ложный контракт и последующее breaking change.

### Добавить Repository для каталога

Отклонено: repository не защищает агрегат и только дублирует возможности Eloquent Builder для read query.

### Читать каталог через Eloquent

Отклонено для этого use case: Eloquent удобен для отношений, но read model задаёт более узкий контракт и не предоставляет операции изменения состояния. Query Builder сохраняет SQL-подобный способ чтения без ORM lifecycle; отдельный PDO adapter не нужен.

### Сразу добавить Elasticsearch

Отклонено: первый сценарий использует точный фильтр по городу и малый демонстрационный набор. Поисковый port и Elasticsearch adapter появятся вместе с полнотекстовым/географическим поиском и требованиями масштаба.

### Сделать общий слой `app/Models`

Отклонено: он размывает владельца данных. Persistence-модели располагаются внутри `Catalog`, поскольку именно модуль владеет каноническим каталогом.

## Approval points

Перед реализацией требуется подтвердить:

1. отдельный промежуточный endpoint `/api/v1/catalog/hotels` без цен и availability;
2. возврат отеля даже при отсутствии активных типов номеров;
3. вложенные типы номеров в списке отелей;
4. UUID для публичных идентификаторов отелей и типов номеров;
5. модульный Service Provider для регистрации маршрута.
