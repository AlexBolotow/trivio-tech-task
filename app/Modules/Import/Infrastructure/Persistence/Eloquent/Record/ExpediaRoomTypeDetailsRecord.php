<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpediaRoomTypeDetailsRecord extends Model
{
    protected $table = 'expedia_room_type_details';

    protected $primaryKey = 'supplier_room_type_id';

    public $incrementing = false;

    protected $fillable = ['supplier_room_type_id', 'amenities'];

    protected function casts(): array
    {
        return ['amenities' => 'array'];
    }

    /** @return BelongsTo<SupplierRoomTypeRecord, $this> */
    public function supplierRoomType(): BelongsTo
    {
        return $this->belongsTo(SupplierRoomTypeRecord::class, 'supplier_room_type_id');
    }
}
