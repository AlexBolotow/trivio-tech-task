<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierImportSettingsRecord extends Model
{
    protected $table = 'supplier_import_settings';

    protected $primaryKey = 'supplier_id';

    public $incrementing = false;

    protected $fillable = [
        'supplier_id',
        'enabled',
        'check_interval_minutes',
        'child_task_size_records',
        'min_success_rate',
        'next_check_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'check_interval_minutes' => 'integer',
            'child_task_size_records' => 'integer',
            'min_success_rate' => 'integer',
            'next_check_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<SupplierRecord, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierRecord::class, 'supplier_id');
    }
}
