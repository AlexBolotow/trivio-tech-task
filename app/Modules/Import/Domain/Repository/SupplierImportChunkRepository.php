<?php

namespace App\Modules\Import\Domain\Repository;

use App\Modules\Import\Domain\Model\SupplierImportChunk;

interface SupplierImportChunkRepository
{
    public function find(int $id): ?SupplierImportChunk;

    public function add(SupplierImportChunk $supplierImportChunk): SupplierImportChunk;

    public function save(SupplierImportChunk $supplierImportChunk): void;
}
