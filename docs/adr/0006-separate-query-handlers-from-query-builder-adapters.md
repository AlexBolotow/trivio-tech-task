# ADR-0006: Separate query handlers from Query Builder adapters

- Status: Accepted
- Date: 2026-09-25

## Context

The Catalog read use case uses Laravel Query Builder and immutable read models. Keeping that database API directly in the Application query handler couples the use case to Laravel and makes the user's preferred Application-port / Infrastructure-adapter boundary less visible. Reading the catalog is a real persistence boundary, while the use case itself should describe what is requested.

## Decision

For the hotel-listing use case:

- Keep `ListHotelsQuery`, `ListHotelsQueryHandler`, the `ListHotelsQueryService` port, and immutable read models in `Catalog/Application/Queries/ListHotels`.
- Put the Laravel Query Builder implementation in `Catalog/Infrastructure/Persistence/QueryBuilder`.
- Let the handler delegate to the port; keep SQL, selection, pagination execution, relation batching, and row-to-read-model mapping in the adapter.
- Bind the port to the adapter in `CatalogServiceProvider`.

This refines ADR-0003 and ADR-0005 for this use case. It does not require a repository for every table, a separate read database, or a message bus.

## Alternatives

- Run Query Builder directly from the Application handler: simpler, and still allowed for straightforward read handlers, but does not preserve the requested application boundary here.
- Use Eloquent for the read use case: concise relation loading, but this Catalog query is intentionally implemented with Query Builder and immutable read models.
- Add a generic query framework or base handler: rejected because one query use case does not justify a framework abstraction.

## Consequences

- Application is independent of Laravel database APIs for this use case.
- There is one interface and one adapter to wire and maintain.
- Query behavior can be exercised through the handler with a fake service, while SQL behavior belongs to adapter-level integration tests.
- The port is justified by this explicit boundary and learning objective; it should not be copied to every simple query without a reason.
