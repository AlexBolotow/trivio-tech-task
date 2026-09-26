<?php

namespace App\Modules\Import\Domain\Model;

use App\Modules\Import\Domain\ValueObject\SupplierImportStatus;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

final class SupplierImport
{
    private SupplierImportStatus $status;

    private int $totalChunks;

    private int $successfulChunks;

    private int $failedChunks;

    private ?DateTimeImmutable $startedAt;

    private ?DateTimeImmutable $finishedAt;

    public function __construct(
        public readonly string $id,
        public readonly int $supplierId,
        public readonly ?string $sourceVersion,
        public readonly string $sourceChecksum,
        public readonly string $sourceUri,
        public readonly int $childTaskSizeRecords,
        public readonly int $minSuccessRate,
        SupplierImportStatus $status = SupplierImportStatus::Pending,
        int $totalChunks = 0,
        int $successfulChunks = 0,
        int $failedChunks = 0,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $finishedAt = null,
    ) {
        $this->status = $status;
        $this->totalChunks = $totalChunks;
        $this->successfulChunks = $successfulChunks;
        $this->failedChunks = $failedChunks;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;

        if ($supplierId < 1) {
            throw new InvalidArgumentException('Supplier ID must be positive.');
        }

        if (! preg_match('/\A[a-f0-9]{64}\z/i', $sourceChecksum)) {
            throw new InvalidArgumentException('Source checksum must be a 64-character SHA-256 hex value.');
        }

        if ($childTaskSizeRecords < 1) {
            throw new InvalidArgumentException('Child task size must be positive.');
        }

        if ($minSuccessRate < 0 || $minSuccessRate > 100) {
            throw new InvalidArgumentException('Minimum success rate must be between 0 and 100.');
        }

        if ($totalChunks < 0 || $successfulChunks < 0 || $failedChunks < 0) {
            throw new InvalidArgumentException('Chunk counts cannot be negative.');
        }
    }

    public function status(): SupplierImportStatus
    {
        return $this->status;
    }

    public function totalChunks(): int
    {
        return $this->totalChunks;
    }

    public function successfulChunks(): int
    {
        return $this->successfulChunks;
    }

    public function failedChunks(): int
    {
        return $this->failedChunks;
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
        if ($this->status !== SupplierImportStatus::Pending) {
            throw new LogicException('Only a pending import can be started.');
        }

        if ($this->totalChunks < 1) {
            throw new LogicException('An import cannot start before its chunks are planned.');
        }

        $this->status = SupplierImportStatus::InProgress;
        $this->startedAt = $at;
    }

    public function planChunks(int $totalChunks): void
    {
        if ($this->status !== SupplierImportStatus::Pending) {
            throw new LogicException('Chunks can only be planned before the import starts.');
        }

        if ($totalChunks < 1) {
            throw new InvalidArgumentException('An import must contain at least one chunk.');
        }

        if ($this->totalChunks !== 0 && $this->totalChunks !== $totalChunks) {
            throw new LogicException('The planned chunk count cannot be changed.');
        }

        $this->totalChunks = $totalChunks;
    }

    public function finish(int $successfulChunks, int $failedChunks, DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportStatus::InProgress) {
            throw new LogicException('Only an in-progress import can be finished.');
        }

        if ($successfulChunks < 0 || $failedChunks < 0 || $successfulChunks + $failedChunks !== $this->totalChunks) {
            throw new InvalidArgumentException('Finished chunk counts must add up to the planned total.');
        }

        $this->successfulChunks = $successfulChunks;
        $this->failedChunks = $failedChunks;
        $this->status = ($successfulChunks * 100 >= $this->totalChunks * $this->minSuccessRate)
            ? SupplierImportStatus::Done
            : SupplierImportStatus::Error;
        $this->finishedAt = $at;
    }
}
