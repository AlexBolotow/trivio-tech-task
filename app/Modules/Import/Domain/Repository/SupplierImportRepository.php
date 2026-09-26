<?php

namespace App\Modules\Import\Domain\Repository;

use App\Modules\Import\Domain\Model\SupplierImport;

interface SupplierImportRepository
{
    public function find(string $id): ?SupplierImport;

    public function save(SupplierImport $supplierImport): void;
}
