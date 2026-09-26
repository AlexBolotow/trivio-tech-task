<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierImportChunkRecord extends Model
{
    protected $table = 'supplier_import_chunks';

    protected $fillable = [
        'supplier_import_id',
        'chunk_number',
        'source_locator',
        'records_count',
        'status',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'source_locator' => 'array',
            'records_count' => 'integer',
            'status' => SupplierImportChunkStatus::class,
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<SupplierImportRecord, $this> */
    public function supplierImport(): BelongsTo
    {
        return $this->belongsTo(SupplierImportRecord::class, 'supplier_import_id');
    }
}
