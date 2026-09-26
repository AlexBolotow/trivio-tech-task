<?php

namespace Tests\Unit\Modules\Import;

use App\Modules\Import\Domain\Model\SupplierImportChunk;
use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SupplierImportChunkTest extends TestCase
{
    public function test_it_counts_initial_attempt_and_updates_heartbeat(): void
    {
        $chunk = $this->pendingChunk();
        $at = new DateTimeImmutable('2026-09-26T10:00:00Z');

        $chunk->startAttempt($at);
        $chunk->heartbeat(new DateTimeImmutable('2026-09-26T10:01:00Z'));

        self::assertSame(1, $chunk->attempts());
        self::assertSame(SupplierImportChunkStatus::InProgress, $chunk->status());
        self::assertEquals(new DateTimeImmutable('2026-09-26T10:01:00Z'), $chunk->lastHeartbeatAt());
    }

    public function test_it_returns_failed_chunk_to_pending_until_five_retries_are_used(): void
    {
        $chunk = $this->pendingChunk();
        $at = new DateTimeImmutable('2026-09-26T10:00:00Z');

        $chunk->startAttempt($at);
        $chunk->fail('temporary', 'Temporary failure.', $at);

        self::assertSame(1, $chunk->attempts());
        self::assertSame(SupplierImportChunkStatus::Pending, $chunk->status());

        for ($attempt = 2; $attempt <= SupplierImportChunk::MAX_ATTEMPTS; $attempt++) {
            $chunk->startAttempt($at);
            $chunk->fail('temporary', 'Temporary failure.', $at);
        }

        self::assertSame(6, $chunk->attempts());
        self::assertSame(SupplierImportChunkStatus::Error, $chunk->status());
    }

    public function test_it_rejects_heartbeat_for_a_pending_chunk(): void
    {
        $this->expectException(LogicException::class);

        $this->pendingChunk()->heartbeat(new DateTimeImmutable('2026-09-26T10:00:00Z'));
    }

    public function test_it_returns_a_stale_attempt_to_pending_before_the_retry_limit(): void
    {
        $chunk = $this->pendingChunk();
        $chunk->startAttempt(new DateTimeImmutable('2026-09-26T10:00:00Z'));

        $chunk->expireStaleAttempt(
            new DateTimeImmutable('2026-09-26T10:05:00Z'),
            new DateTimeImmutable('2026-09-26T10:06:00Z'),
        );

        self::assertSame(SupplierImportChunkStatus::Pending, $chunk->status());
        self::assertSame('heartbeat_expired', $chunk->errorCode());
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
