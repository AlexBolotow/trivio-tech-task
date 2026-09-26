<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupplierHotelRecord extends Model
{
    protected $table = 'supplier_hotels';

    protected $fillable = [
        'supplier_id',
        'external_hotel_id',
        'name',
        'city_name',
        'country_code',
        'address',
        'latitude',
        'longitude',
        'hotel_id',
        'last_seen_import_id',
        'link_status',
        'link_confidence',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'link_confidence' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<SupplierRecord, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierRecord::class, 'supplier_id');
    }

    /** @return BelongsTo<SupplierImportRecord, $this> */
    public function lastSeenImport(): BelongsTo
    {
        return $this->belongsTo(SupplierImportRecord::class, 'last_seen_import_id');
    }

    /** @return HasMany<SupplierRoomTypeRecord, $this> */
    public function roomTypes(): HasMany
    {
        return $this->hasMany(SupplierRoomTypeRecord::class, 'supplier_hotel_id');
    }

    /** @return HasOne<ExpediaHotelDetailsRecord, $this> */
    public function expediaDetails(): HasOne
    {
        return $this->hasOne(ExpediaHotelDetailsRecord::class, 'supplier_hotel_id');
    }

    /** @return HasMany<SupplierHotelPhotoRecord, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(SupplierHotelPhotoRecord::class, 'supplier_hotel_id')->orderBy('sort_order');
    }
}
