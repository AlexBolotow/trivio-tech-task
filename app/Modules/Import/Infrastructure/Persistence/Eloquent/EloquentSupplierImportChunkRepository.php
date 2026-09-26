<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent;

use App\Modules\Import\Domain\Model\SupplierImportChunk;
use App\Modules\Import\Domain\Repository\SupplierImportChunkRepository;
use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use App\Modules\Import\Infrastructure\Persistence\Eloquent\Record\SupplierImportChunkRecord;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use UnexpectedValueException;

class EloquentSupplierImportChunkRepository implements SupplierImportChunkRepository
{
    public function find(int $id): ?SupplierImportChunk
    {
        $record = SupplierImportChunkRecord::query()->find($id);

        return $record === null ? null : $this->toDomain($record);
    }

    public function add(SupplierImportChunk $supplierImportChunk): SupplierImportChunk
    {
        if ($supplierImportChunk->id !== null) {
            throw new InvalidArgumentException('A new chunk must not have a database ID yet.');
        }

        $record = new SupplierImportChunkRecord();
        $this->writeToRecord($supplierImportChunk, $record);
        $record->save();

        return $this->toDomain($record);
    }

    public function save(SupplierImportChunk $supplierImportChunk): void
    {
        if ($supplierImportChunk->id === null) {
            throw new InvalidArgumentException('A persisted chunk must have a database ID.');
        }

        $record = SupplierImportChunkRecord::query()->findOrFail($supplierImportChunk->id);
        $this->writeToRecord($supplierImportChunk, $record);
        $record->save();
    }

    private function toDomain(SupplierImportChunkRecord $record): SupplierImportChunk
    {
        return new SupplierImportChunk(
            id: $record->id,
            supplierImportId: $record->supplier_import_id,
            chunkNumber: $record->chunk_number,
            sourceLocator: $this->sourceLocator($record->getAttribute('source_locator')),
            recordsCount: $record->records_count,
            status: SupplierImportChunkStatus::from($record->getRawOriginal('status')),
            startedAt: $this->toDateTimeImmutable($record->getAttribute('started_at')),
            finishedAt: $this->toDateTimeImmutable($record->getAttribute('finished_at')),
        );
    }

    /** @return array<string, mixed> */
    private function sourceLocator(mixed $value): array
    {
        if (! is_array($value)) {
            throw new UnexpectedValueException('Unexpected source locator value in supplier import chunk record.');
        }

        return $value;
    }

    private function toDateTimeImmutable(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (is_string($value)) {
            return new DateTimeImmutable($value);
        }

        throw new UnexpectedValueException('Unexpected datetime value in supplier import chunk record.');
    }

    private function writeToRecord(SupplierImportChunk $supplierImportChunk, SupplierImportChunkRecord $record): void
    {
        $record->fill([
            'supplier_import_id' => $supplierImportChunk->supplierImportId,
            'chunk_number' => $supplierImportChunk->chunkNumber,
            'source_locator' => $supplierImportChunk->sourceLocator,
            'records_count' => $supplierImportChunk->recordsCount,
            'status' => $supplierImportChunk->status(),
            'started_at' => $supplierImportChunk->startedAt(),
            'finished_at' => $supplierImportChunk->finishedAt(),
        ]);
    }
}
