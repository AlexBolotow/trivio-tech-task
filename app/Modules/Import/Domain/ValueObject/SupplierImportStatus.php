<?php

namespace App\Modules\Import\Domain\ValueObject;

enum SupplierImportStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Error = 'error';
}
