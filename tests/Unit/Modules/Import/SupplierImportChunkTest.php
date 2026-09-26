<?php

namespace Tests\Unit\Modules\Import;

use App\Modules\Import\Domain\Model\SupplierImportChunk;
use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SupplierImportChunkTest extends TestCase
{
    public function test_it_starts_a_pending_chunk_without_tracking_queue_attempts(): void
    {
        $chunk = $this->pendingChunk();
        $at = new DateTimeImmutable('2026-09-26T10:00:00Z');

        $chunk->start($at);

        self::assertSame(SupplierImportChunkStatus::InProgress, $chunk->status());
        self::assertEquals($at, $chunk->startedAt());
    }

    public function test_it_marks_an_in_progress_chunk_as_failed(): void
    {
        $chunk = $this->pendingChunk();
        $at = new DateTimeImmutable('2026-09-26T10:00:00Z');

        $chunk->start($at);
        $chunk->fail($at);

        self::assertSame(SupplierImportChunkStatus::Error, $chunk->status());
        self::assertEquals($at, $chunk->finishedAt());
    }

    public function test_it_rejects_starting_a_chunk_that_is_not_pending(): void
    {
        $chunk = $this->pendingChunk();
        $at = new DateTimeImmutable('2026-09-26T10:00:00Z');
        $chunk->start($at);

        $this->expectException(LogicException::class);

        $chunk->start($at);
    }

    private function pendingChunk(): SupplierImportChunk
    {
        return new SupplierImportChunk(
            id: 1,
            supplierImportId: '550e8400-e29b-41d4-a716-446655440000',
            chunkNumber: 1,
            sourceLocator: ['line_from' => 1, 'line_to' => 500],
            recordsCount: 500,
        );
    }
}
