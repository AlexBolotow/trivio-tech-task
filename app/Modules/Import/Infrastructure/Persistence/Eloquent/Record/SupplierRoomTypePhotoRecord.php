<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierRoomTypePhotoRecord extends Model
{
    protected $table = 'supplier_room_type_photos';

    protected $fillable = ['supplier_room_type_id', 'source_url', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /** @return BelongsTo<SupplierRoomTypeRecord, $this> */
    public function supplierRoomType(): BelongsTo
    {
        return $this->belongsTo(SupplierRoomTypeRecord::class, 'supplier_room_type_id');
    }
}
