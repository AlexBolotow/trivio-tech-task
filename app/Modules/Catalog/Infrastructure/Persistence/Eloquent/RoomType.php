<?php

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent;

use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(RoomTypeFactory::class)]
class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use HasFactory;
    use HasUuids;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'hotel_id',
        'code',
        'name',
        'max_adults',
        'max_children',
        'max_total_guests',
        'area',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'max_adults' => 'integer',
            'max_children' => 'integer',
            'max_total_guests' => 'integer',
            'area' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Hotel, $this> */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /** @return HasMany<RoomTypePhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(RoomTypePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @param Builder<RoomType> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }
}
