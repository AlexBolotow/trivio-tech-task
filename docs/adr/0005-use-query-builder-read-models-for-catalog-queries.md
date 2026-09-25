# ADR-0005: Use Query Builder read models for catalog queries

- Status: Accepted
- Date: 2026-09-25

## Context

The Catalog module has a read-only hotel listing use case. Its result is a projection for an HTTP response, not an aggregate that needs domain behavior or persistence methods. The project already uses Eloquent for Catalog persistence and permits Query Builder for read-side queries. The user prefers the familiar separation of ORM-backed writes and SQL-oriented queries used in Symfony/Doctrine projects.

## Decision

Implement Catalog query services with Laravel Query Builder and return immutable, purpose-specific read models. Do not hydrate Eloquent models for these queries. Load nested collections in bounded batch queries and assemble the projection in the application query service. Keep Eloquent models available for write-side persistence and relationship operations where appropriate.

Laravel's connection and Query Builder are the adapter boundary; do not introduce a custom PDO wrapper or a repository for this read use case.

## Alternatives

- Eloquent queries and eager loading: idiomatic and concise, but provides a broader mutable ORM model than this read contract needs.
- Direct PDO: rejected because it bypasses Laravel's connection, binding, grammar, and testing integration without a demonstrated need.
- Doctrine DBAL: not introduced into the Laravel application; Laravel Query Builder supplies the required SQL-oriented query API.

## Consequences

- The read path cannot persist data through the returned read models.
- Query shape and selected columns are explicit, and related rows can be loaded in a fixed number of queries.
- No read transaction is opened. Under concurrent Catalog changes, pagination totals and loaded related rows can reflect slightly different moments; a consistent snapshot can be added if a later requirement justifies its transaction cost.
- The application must map database rows into read models and maintain that mapping as the read contract evolves.
- This is a targeted CQRS-lite separation, not a separate database or a requirement to use raw SQL for every query.
