<?php

namespace App\Modules\Import\Domain\Model;

use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

final class SupplierImportChunk
{
    private SupplierImportChunkStatus $status;

    private ?DateTimeImmutable $startedAt;

    private ?DateTimeImmutable $finishedAt;

    /** @param array<string, mixed> $sourceLocator */
    public function __construct(
        public readonly ?int $id,
        public readonly string $supplierImportId,
        public readonly int $chunkNumber,
        public readonly array $sourceLocator,
        public readonly int $recordsCount,
        SupplierImportChunkStatus $status = SupplierImportChunkStatus::Pending,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $finishedAt = null,
    ) {
        $this->status = $status;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;

        if (($id !== null && $id < 1) || $chunkNumber < 1 || $recordsCount < 0) {
            throw new InvalidArgumentException('Chunk identifiers and counts are invalid.');
        }
    }

    public function status(): SupplierImportChunkStatus
    {
        return $this->status;
    }

    public function startedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function finishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function start(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::Pending) {
            throw new LogicException('Only a pending chunk can start processing.');
        }

        $this->status = SupplierImportChunkStatus::InProgress;
        $this->startedAt ??= $at;
    }

    public function finish(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress) {
            throw new LogicException('Only an in-progress chunk can finish.');
        }

        $this->status = SupplierImportChunkStatus::Done;
        $this->finishedAt = $at;
    }

    public function fail(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress) {
            throw new LogicException('Only an in-progress chunk can fail.');
        }

        $this->status = SupplierImportChunkStatus::Error;
        $this->finishedAt = $at;
    }
}
