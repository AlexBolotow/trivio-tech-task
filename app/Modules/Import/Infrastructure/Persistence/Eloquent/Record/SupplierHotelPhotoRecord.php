<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierHotelPhotoRecord extends Model
{
    protected $table = 'supplier_hotel_photos';

    protected $fillable = ['supplier_hotel_id', 'source_url', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /** @return BelongsTo<SupplierHotelRecord, $this> */
    public function supplierHotel(): BelongsTo
    {
        return $this->belongsTo(SupplierHotelRecord::class, 'supplier_hotel_id');
    }
}
