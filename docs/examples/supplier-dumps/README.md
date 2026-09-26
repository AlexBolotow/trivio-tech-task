# Synthetic supplier feed samples

These small files are synthetic fixtures based on the Trivio system design assignment. They are examples for discussing and later testing the import boundary; they are not real supplier contracts.

- supplier-1-hotels.ndjson contains one JSON hotel object per line. The assignment describes a potentially multi-million-record feed with hotel details, room types, amenities and photos.
- supplier-2-hotels.xml is intentionally a single-line XML document. The assignment describes a daily feed with tens of thousands of hotels and room types with nightly prices.

The XML sample includes currency="RUB" as an illustrative assumption because the assignment does not specify a currency field. Exact field names, optionality, encoding, compression and money conventions must be confirmed before treating these files as a stable contract.

The source file format and the import pipeline are separate concerns. The Import module should process feeds incrementally; it must not load a whole supplier file into memory.
