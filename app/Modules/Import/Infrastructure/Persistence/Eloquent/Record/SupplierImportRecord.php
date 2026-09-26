<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use App\Modules\Import\Domain\ValueObject\SupplierImportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierImportRecord extends Model
{
    use HasUuids;

    protected $table = 'supplier_imports';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'supplier_id',
        'source_version',
        'source_checksum',
        'source_uri',
        'status',
        'child_task_size_records',
        'min_success_rate',
        'total_chunks',
        'successful_chunks',
        'failed_chunks',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SupplierImportStatus::class,
            'child_task_size_records' => 'integer',
            'min_success_rate' => 'integer',
            'total_chunks' => 'integer',
            'successful_chunks' => 'integer',
            'failed_chunks' => 'integer',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<SupplierRecord, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierRecord::class, 'supplier_id');
    }

    /** @return HasMany<SupplierImportChunkRecord, $this> */
    public function chunks(): HasMany
    {
        return $this->hasMany(SupplierImportChunkRecord::class, 'supplier_import_id');
    }

    /** @return HasMany<SupplierHotelRecord, $this> */
    public function lastSeenHotels(): HasMany
    {
        return $this->hasMany(SupplierHotelRecord::class, 'last_seen_import_id');
    }

    /** @return HasMany<SupplierRoomTypeRecord, $this> */
    public function lastSeenRoomTypes(): HasMany
    {
        return $this->hasMany(SupplierRoomTypeRecord::class, 'last_seen_import_id');
    }
}
