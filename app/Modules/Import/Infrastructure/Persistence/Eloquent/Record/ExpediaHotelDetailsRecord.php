<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpediaHotelDetailsRecord extends Model
{
    protected $table = 'expedia_hotel_details';

    protected $primaryKey = 'supplier_hotel_id';

    public $incrementing = false;

    protected $fillable = ['supplier_hotel_id', 'description', 'check_in_time', 'check_out_time'];

    /** @return BelongsTo<SupplierHotelRecord, $this> */
    public function supplierHotel(): BelongsTo
    {
        return $this->belongsTo(SupplierHotelRecord::class, 'supplier_hotel_id');
    }
}
