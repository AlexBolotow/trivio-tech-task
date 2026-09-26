# ADR-0007: Group Catalog read operations by entity

- Status: Accepted
- Date: 2026-09-26

## Context

The Catalog module uses a distinct Query and Handler per use case. Its read-side ports can either be split per use case or grouped by entity. The user prefers one query service per entity, consistent with the Product query service pattern in the Crust project, while preserving a separate Handler for each use case.

## Decision

Define one `HotelQueryService` interface in `Catalog/Application/Queries` for Hotel read operations. Keep each use case represented by its own Query and Handler under `Catalog/Application/Queries/<UseCase>`. Implement the entity-level interface with `QueryBuilderHotelQueryService` in `Catalog/Infrastructure/Persistence/QueryBuilder`.

The interface currently exposes `getHotels(GetHotelsQuery): HotelReadPage`. Add another operation, such as `getHotel(...)`, when that use case is introduced. Each method should retain a clear, semantic contract and a purpose-specific read result.

This supersedes ADR-0006's choice to place a separate `GetHotelsQueryService` port within the use-case folder. ADR-0006's Handler/port/adapter separation remains in effect.

## Alternatives

- One port and adapter per query use case: narrower interfaces and isolated evolution, but more service classes and wiring for this Catalog read model.
- One broad generic query repository for all Catalog tables: rejected because it would expose persistence-shaped CRUD operations instead of Hotel read capabilities.
- Put SQL directly in every Query Handler: rejected because it couples Application to Laravel database APIs and duplicates adapter responsibilities.

## Consequences

- Query handlers stay use-case-specific, while related Hotel read operations share one port and adapter.
- Changes to the Hotel read contract can affect multiple consumers of the entity-level service; each method must remain cohesive and purpose-specific.
- The Infrastructure adapter may still issue several SQL statements internally to assemble one read result.
- This is a Catalog convention, not a rule requiring one read service for every database table.
