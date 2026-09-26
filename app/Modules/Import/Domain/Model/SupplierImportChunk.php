<?php

namespace App\Modules\Import\Domain\Model;

use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

final class SupplierImportChunk
{
    public const MAX_ATTEMPTS = 6;

    private SupplierImportChunkStatus $status;

    private int $attempts;

    private ?DateTimeImmutable $lastHeartbeatAt;

    private ?string $errorCode;

    private ?string $errorMessage;

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
        int $attempts = 0,
        ?DateTimeImmutable $lastHeartbeatAt = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $finishedAt = null,
    ) {
        $this->status = $status;
        $this->attempts = $attempts;
        $this->lastHeartbeatAt = $lastHeartbeatAt;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;

        if (($id !== null && $id < 1) || $chunkNumber < 1 || $recordsCount < 0 || $attempts < 0) {
            throw new InvalidArgumentException('Chunk identifiers and counts are invalid.');
        }
    }

    public function status(): SupplierImportChunkStatus
    {
        return $this->status;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function lastHeartbeatAt(): ?DateTimeImmutable
    {
        return $this->lastHeartbeatAt;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function startedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function finishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function startAttempt(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::Pending) {
            throw new LogicException('Only a pending chunk can start an attempt.');
        }

        if ($this->attempts >= self::MAX_ATTEMPTS) {
            $this->status = SupplierImportChunkStatus::Error;
            $this->errorCode = 'retry_limit_exceeded';
            $this->errorMessage = 'The maximum number of processing attempts has been reached.';
            $this->finishedAt = $at;

            return;
        }

        $this->attempts++;
        $this->status = SupplierImportChunkStatus::InProgress;
        $this->lastHeartbeatAt = $at;
        $this->startedAt ??= $at;
        $this->errorCode = null;
        $this->errorMessage = null;
    }

    public function heartbeat(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress) {
            throw new LogicException('Only an in-progress chunk can heartbeat.');
        }

        $this->lastHeartbeatAt = $at;
    }

    public function expireStaleAttempt(DateTimeImmutable $olderThan, DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress || $this->lastHeartbeatAt === null) {
            throw new LogicException('Only a heartbeat-tracked in-progress chunk can expire.');
        }

        if ($this->lastHeartbeatAt > $olderThan) {
            throw new LogicException('The chunk heartbeat has not expired.');
        }

        $this->errorCode = 'heartbeat_expired';
        $this->errorMessage = 'The worker heartbeat expired before the chunk completed.';
        $this->status = $this->attempts >= self::MAX_ATTEMPTS
            ? SupplierImportChunkStatus::Error
            : SupplierImportChunkStatus::Pending;
        $this->finishedAt = $this->status === SupplierImportChunkStatus::Error ? $at : null;
    }

    public function finish(DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress) {
            throw new LogicException('Only an in-progress chunk can finish.');
        }

        $this->status = SupplierImportChunkStatus::Done;
        $this->finishedAt = $at;
    }

    public function fail(string $errorCode, string $errorMessage, DateTimeImmutable $at): void
    {
        if ($this->status !== SupplierImportChunkStatus::InProgress) {
            throw new LogicException('Only an in-progress chunk can fail.');
        }

        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->status = $this->attempts >= self::MAX_ATTEMPTS
            ? SupplierImportChunkStatus::Error
            : SupplierImportChunkStatus::Pending;
        $this->finishedAt = $this->status === SupplierImportChunkStatus::Error ? $at : null;
    }
}
