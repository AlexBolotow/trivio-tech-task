# Proposal: Import supplier hotel feeds

## Why

The Catalog read endpoint now exposes canonical hotel data, but the project has no path for bringing supplier hotel and room data into the system. The Trivio assignment has two materially different inputs: a large line-delimited JSON feed and a daily single-line XML feed.

The source system-design conversation established a MySQL-backed parent import task plus child chunk tasks claimed by workers with SELECT ... FOR UPDATE SKIP LOCKED. Chunks are at-least-once and therefore need leases, retry limits and idempotent writes. RabbitMQ should not duplicate the state of this import work queue.

## Proposed scope

- Define a supplier-feed import boundary and small, synthetic examples for both formats.
- Track an import batch and its child work, including source identity, checksum/version, status, record counts and failures.
- Parse feeds incrementally and validate each hotel/room record.
- Keep provider identifiers and source data separate from canonical Catalog records so a later Matching step can link them.
- Make chunk retries safe and avoid exposing an incomplete feed version as active.
- Keep actual SFTP/API delivery, production scheduling, matching decisions and search indexing outside the first implementation slice.

## Out of scope

- Availability queries, prices fetched from Supplier 1's API, and booking.
- RabbitMQ as the queue for import chunks.
- A matching algorithm or manual matching UI.
- Production object storage, SFTP credentials, supplier API clients or deployment workers.
- A performance claim for millions of records based only on the small fixtures.

## Proposed first vertical slice

Use local feed files as input in development/tests. Implement one complete import lifecycle against MySQL, then add format-specific readers for both sample feeds. Keep the XML reader streaming; a single-line XML file cannot be partitioned by line boundaries like NDJSON. The plan must choose whether to stream XML into bounded batches in one producer or create materialized chunk files before parallel workers claim them.

Before coding, review the design choices listed in design.md, especially the staging representation and XML chunk production strategy.
