<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegasRoomTypeDetailsRecord extends Model
{
    protected $table = 'pegas_room_type_details';

    protected $primaryKey = 'supplier_room_type_id';

    public $incrementing = false;

    protected $fillable = ['supplier_room_type_id', 'nightly_price', 'currency'];

    protected function casts(): array
    {
        return ['nightly_price' => 'decimal:2'];
    }

    /** @return BelongsTo<SupplierRoomTypeRecord, $this> */
    public function supplierRoomType(): BelongsTo
    {
        return $this->belongsTo(SupplierRoomTypeRecord::class, 'supplier_room_type_id');
    }
}
