<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent;

use App\Modules\Import\Domain\Model\SupplierImport;
use App\Modules\Import\Domain\Repository\SupplierImportRepository;
use App\Modules\Import\Domain\ValueObject\SupplierImportStatus;
use App\Modules\Import\Infrastructure\Persistence\Eloquent\Record\SupplierImportRecord;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use UnexpectedValueException;

class EloquentSupplierImportRepository implements SupplierImportRepository
{
    public function find(string $id): ?SupplierImport
    {
        $record = SupplierImportRecord::query()->find($id);

        return $record === null ? null : $this->toDomain($record);
    }

    public function save(SupplierImport $supplierImport): void
    {
        $record = SupplierImportRecord::query()->firstOrNew(['id' => $supplierImport->id]);
        $checksum = hex2bin($supplierImport->sourceChecksum);

        if ($checksum === false) {
            throw new InvalidArgumentException('Source checksum must be hexadecimal.');
        }

        $record->fill([
            'supplier_id' => $supplierImport->supplierId,
            'source_version' => $supplierImport->sourceVersion,
            'source_checksum' => $checksum,
            'source_uri' => $supplierImport->sourceUri,
            'status' => $supplierImport->status(),
            'child_task_size_records' => $supplierImport->childTaskSizeRecords,
            'min_success_rate' => $supplierImport->minSuccessRate,
            'total_chunks' => $supplierImport->totalChunks(),
            'successful_chunks' => $supplierImport->successfulChunks(),
            'failed_chunks' => $supplierImport->failedChunks(),
            'started_at' => $supplierImport->startedAt(),
            'finished_at' => $supplierImport->finishedAt(),
        ]);
        $record->save();
    }

    private function toDomain(SupplierImportRecord $record): SupplierImport
    {
        return new SupplierImport(
            id: $record->id,
            supplierId: $record->supplier_id,
            sourceVersion: $record->source_version,
            sourceChecksum: bin2hex($record->source_checksum),
            sourceUri: $record->source_uri,
            childTaskSizeRecords: $record->child_task_size_records,
            minSuccessRate: $record->min_success_rate,
            status: SupplierImportStatus::from($record->getRawOriginal('status')),
            totalChunks: $record->total_chunks,
            successfulChunks: $record->successful_chunks,
            failedChunks: $record->failed_chunks,
            startedAt: $this->toDateTimeImmutable($record->getAttribute('started_at')),
            finishedAt: $this->toDateTimeImmutable($record->getAttribute('finished_at')),
        );
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

        throw new UnexpectedValueException('Unexpected datetime value in supplier import record.');
    }
}
