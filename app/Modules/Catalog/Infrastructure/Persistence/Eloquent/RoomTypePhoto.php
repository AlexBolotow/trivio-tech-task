<?php

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent;

use Database\Factories\RoomTypePhotoFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(RoomTypePhotoFactory::class)]
class RoomTypePhoto extends Model
{
    /** @use HasFactory<RoomTypePhotoFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'room_type_id',
        'url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<RoomType, $this> */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
