# Tasks: Import supplier feeds

- [ ] Review and approve proposal.md, especially first-slice scope and non-goals.
- [ ] Decide whether source data uses common supplier tables, per-provider staging tables, or raw files plus common records.
- [ ] Decide how a single-line XML feed produces bounded chunks and whether it needs parallel processing in the first slice.
- [ ] Define import batch/chunk statuses, lease fields, heartbeat interval, retry limit and failure/quarantine policy.
- [ ] Define a version activation invariant so a failed full snapshot cannot replace the active supplier data.
- [ ] Define the public contract between Import, SupplierIntegration, Matching and Catalog.
- [ ] Add schema migrations and module contracts only after design approval.
- [ ] Implement one vertical import flow with idempotent chunk processing and MySQL integration coverage.
- [ ] Add both supplier-specific readers and tests using the synthetic NDJSON/XML fixtures.
- [ ] Verify fresh import, repeated chunk, partial failure/retry, invalid record, failed batch and atomic activation.
- [ ] Compare implementation with the approved spec/design and archive the change.
