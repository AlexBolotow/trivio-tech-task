# Design: Import supplier hotel feeds

## Context from the source system-design conversation

- Supplier 1 supplies a potentially multi-million-record NDJSON catalog with hotel details, room types, amenities and photo URLs. It also has a separate live API for current offers; that API is not part of feed import.
- Supplier 2 supplies a daily, single-line XML catalog with tens of thousands of hotels and room types with nightly prices. Its API only books; the file price is not proof of availability.
- The user proposed parent import_tasks and child import_chunk_tasks, created on a schedule, with workers claiming chunks using MySQL SELECT ... FOR UPDATE SKIP LOCKED, heartbeats/leases, retry counters and retry after stale work.
- The conversation favored treating chunk processing as at-least-once and making writes idempotent. RabbitMQ should not mirror the import-chunk queue state.
- The source discussion considered both common supplier tables and source-specific staging. No final persistence choice is assumed here; it needs review against the current modular-monolith guidance.

## Proposed boundaries

~~~text
SupplierIntegration format reader
        ↓ source-specific records
Import batch / chunk orchestration
        ↓ validated source records
supplier-side persistence / staging
        ↓ later public contract
Matching → canonical Catalog
~~~

- Import owns the import run lifecycle, chunk scheduling/claiming, retries, quarantine counts and activation readiness.
- Supplier-specific readers own the external feed shape and convert it into typed source records. Do not create one giant interface that also includes availability and booking capabilities.
- Catalog remains the owner of canonical hotels and room types. Import does not reach into another module's private implementation; integration with Matching/Catalog needs an explicit public application contract when that slice is designed.

## Proposed lifecycle

1. Create an import batch for a provider and input version; record a checksum and source reference.
2. Validate that the feed can be read. Reject an identical already-completed version or make its retry behavior explicit.
3. Produce bounded chunks. NDJSON can be partitioned at record boundaries. XML must be read as a stream; because it is one physical line, line-offset chunking is invalid.
4. Workers claim pending chunks in a short MySQL transaction with FOR UPDATE SKIP LOCKED, then record worker/lease information. Do not hold a database transaction while parsing a large chunk.
5. Parse, validate and write idempotently. A uniqueness key must include the import version and supplier's external record identifier. Invalid records go to a bounded quarantine/error record with source position and a reason; the raw feed remains the diagnostic source.
6. A heartbeat renews the lease. A stale lease becomes retryable; retry count and terminal failure are explicit. Reprocessing a partial chunk must converge to the same staged state.
7. A finalizer activates a complete, validated import version atomically. A failed or incomplete import must not replace the currently active supplier snapshot.
8. Matching and search-index updates consume the new version later through their module contracts; they are not implemented as hidden parser side effects.

## Open decisions for review

### Staging representation

Options:

1. Common supplier_hotels / supplier_room_types tables with typed common fields and a source payload for diagnostics.
2. Separate supplier-specific staging tables that preserve each source shape, followed by conversion into common supplier records.
3. Keep raw feed files outside MySQL and write directly to common supplier records, adding staging tables only where retry/validation requires them.

The historical design discussion leaned toward source-aligned data at the integration edge and a common internal model after translation, but it did not establish this as a final project ADR. My recommendation for the first Laravel slice is raw file + common per-provider records/version metadata, with source-specific parsers and no permanent duplicate staging tables unless replay/validation proves they are needed.

### XML chunk production

1. One streaming XML producer emits bounded batches into MySQL staging and creates chunk tasks from completed ranges.
2. A streaming splitter materializes bounded XML fragments/chunk files, then chunk workers process them in parallel.
3. Process this smaller Supplier 2 feed in one streaming task initially; add chunk fan-out only when throughput requires it.

The smallest safe first version is option 3 for XML and independently chunked NDJSON. Both use the same parent-task lifecycle; a source reader may emit a different number of child chunks.

### Full snapshot activation

Use versioned supplier records and an explicit active import/version pointer rather than trying to update canonical records row-by-row while users read them. Confirm whether this pointer lives on the provider, on the batch, or is represented with an active version field before schema work.

## Transaction and failure boundaries

- Claim/update a chunk under a short row-locking transaction.
- Persist each bounded batch transactionally; never wrap the entire multi-million-record feed in one transaction.
- Reprocessing is at-least-once; uniqueness constraints and deterministic upserts provide idempotency.
- Chunk failure is retried only while its lease and retry policy permit it. Permanent record validation errors are quarantined; systemic file/format failures fail the batch.
- Activation is a short atomic operation after all required chunks have terminal successful processing and batch-level checks pass.

## Alternatives considered

- RabbitMQ for every chunk: rejected for the first iteration because MySQL task rows already own progress, lease, retry and parent aggregation; a broker would duplicate queue state.
- Load the whole feed into memory and parse with json_decode/DOM: rejected because the assignment's feeds are large and the XML is one line.
- Write directly to active canonical Catalog rows while parsing: rejected because partial imports and retries could expose a mixed dataset.
- One universal provider adapter with search, booking and import methods: rejected because feed capabilities differ and the first task concerns import only.
