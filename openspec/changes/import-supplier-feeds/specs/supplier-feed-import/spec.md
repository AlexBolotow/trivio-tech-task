# Supplier feed import specification

## Requirement: Import supplier catalog feeds

The system SHALL accept supplier catalog feeds through a supplier-specific reader and represent each import as a tracked batch.

### Scenario: Start an import

GIVEN a provider and a feed source/version are configured
WHEN an import is requested
THEN the system records an import batch before processing records
AND records enough metadata to identify and diagnose the source version
AND does not make the batch active before successful completion.

## Requirement: Process large feeds incrementally

The system SHALL process catalog feeds without loading the full file into application memory.

### Scenario: Read an NDJSON feed

GIVEN a feed containing one hotel record per line
WHEN the import reader processes the feed
THEN each line is handled as an independent record
AND work can be divided only at record boundaries.

### Scenario: Read a single-line XML feed

GIVEN a feed containing many hotel records in one XML document on one physical line
WHEN the import reader processes the feed
THEN it uses a streaming parser
AND it does not assume that line boundaries are record boundaries.

## Requirement: Retry chunks safely

The system SHALL tolerate at-least-once execution of import chunks.

### Scenario: A worker lease expires after partial progress

GIVEN a worker has partially processed a chunk and its lease expires
WHEN another worker retries that chunk
THEN repeated source records do not create duplicate supplier records
AND the chunk eventually reaches a terminal result or an explicit retry limit.

## Requirement: Keep incomplete imports out of active reads

The system SHALL activate a feed version only after its required processing and validation have completed.

### Scenario: A chunk or batch fails

GIVEN an active supplier version already exists
WHEN a new import is incomplete or fails validation thresholds
THEN the active version remains unchanged
AND the failed batch remains available for diagnosis.

### Scenario: Import completes

GIVEN all required chunks completed and batch-level checks pass
WHEN the finalizer activates the batch
THEN readers see the new version as one active snapshot
AND they do not observe a mixture of the previous and new supplier versions.

## Requirement: Isolate provider-specific format rules

The system SHALL keep NDJSON/XML parsing and source-field translation at the supplier integration boundary.

### Scenario: A supplier changes its feed shape

WHEN one supplier adds or changes source fields
THEN the change is handled in that supplier's reader/translation layer
AND unrelated provider readers and canonical Catalog contracts do not acquire provider-specific conditionals.
