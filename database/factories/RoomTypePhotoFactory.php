<?php

namespace Database\Factories;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomTypePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomTypePhoto>
 */
class RoomTypePhotoFactory extends Factory
{
    protected $model = RoomTypePhoto::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'url' => fake()->imageUrl(),
            'sort_order' => 0,
        ];
    }
}
