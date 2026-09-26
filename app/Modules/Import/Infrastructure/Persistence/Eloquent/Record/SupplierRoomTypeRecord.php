<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupplierRoomTypeRecord extends Model
{
    protected $table = 'supplier_room_types';

    protected $fillable = [
        'supplier_hotel_id',
        'external_room_type_id',
        'name',
        'max_adults',
        'max_children',
        'max_total_guests',
        'room_type_id',
        'last_seen_import_id',
        'link_status',
    ];

    protected function casts(): array
    {
        return [
            'max_adults' => 'integer',
            'max_children' => 'integer',
            'max_total_guests' => 'integer',
        ];
    }

    /** @return BelongsTo<SupplierHotelRecord, $this> */
    public function supplierHotel(): BelongsTo
    {
        return $this->belongsTo(SupplierHotelRecord::class, 'supplier_hotel_id');
    }

    /** @return BelongsTo<SupplierImportRecord, $this> */
    public function lastSeenImport(): BelongsTo
    {
        return $this->belongsTo(SupplierImportRecord::class, 'last_seen_import_id');
    }

    /** @return HasOne<ExpediaRoomTypeDetailsRecord, $this> */
    public function expediaDetails(): HasOne
    {
        return $this->hasOne(ExpediaRoomTypeDetailsRecord::class, 'supplier_room_type_id');
    }

    /** @return HasOne<PegasRoomTypeDetailsRecord, $this> */
    public function pegasDetails(): HasOne
    {
        return $this->hasOne(PegasRoomTypeDetailsRecord::class, 'supplier_room_type_id');
    }

    /** @return HasMany<SupplierRoomTypePhotoRecord, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(SupplierRoomTypePhotoRecord::class, 'supplier_room_type_id')->orderBy('sort_order');
    }
}
