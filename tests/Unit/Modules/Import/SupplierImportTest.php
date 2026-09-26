<?php

namespace Tests\Unit\Modules\Import;

use App\Modules\Import\Domain\Model\SupplierImport;
use App\Modules\Import\Domain\ValueObject\SupplierImportStatus;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SupplierImportTest extends TestCase
{
    public function test_it_marks_an_import_done_when_success_rate_meets_the_threshold(): void
    {
        $import = $this->pendingImport();
        $import->planChunks(5);
        $import->start(new DateTimeImmutable('2026-09-26T10:00:00Z'));

        $import->finish(4, 1, new DateTimeImmutable('2026-09-26T10:05:00Z'));

        self::assertSame(SupplierImportStatus::Done, $import->status());
        self::assertSame(4, $import->successfulChunks());
        self::assertSame(1, $import->failedChunks());
    }

    public function test_it_marks_an_import_error_when_success_rate_is_below_threshold(): void
    {
        $import = $this->pendingImport();
        $import->planChunks(5);
        $import->start(new DateTimeImmutable('2026-09-26T10:00:00Z'));

        $import->finish(3, 2, new DateTimeImmutable('2026-09-26T10:05:00Z'));

        self::assertSame(SupplierImportStatus::Error, $import->status());
    }

    public function test_it_requires_chunks_to_be_planned_before_start(): void
    {
        $this->expectException(LogicException::class);

        $this->pendingImport()->start(new DateTimeImmutable('2026-09-26T10:00:00Z'));
    }

    public function test_it_rejects_finalization_before_all_chunks_have_an_outcome(): void
    {
        $import = $this->pendingImport();
        $import->planChunks(2);
        $import->start(new DateTimeImmutable('2026-09-26T10:00:00Z'));

        $this->expectException(\InvalidArgumentException::class);
        $import->finish(1, 0, new DateTimeImmutable('2026-09-26T10:05:00Z'));
    }

    private function pendingImport(): SupplierImport
    {
        return new SupplierImport(
            id: '550e8400-e29b-41d4-a716-446655440000',
            supplierId: 1,
            sourceVersion: null,
            sourceChecksum: hash('sha256', 'supplier-file'),
            sourceUri: 'file:///supplier/catalog.ndjson',
            childTaskSizeRecords: 500,
            minSuccessRate: 80,
        );
    }
}
