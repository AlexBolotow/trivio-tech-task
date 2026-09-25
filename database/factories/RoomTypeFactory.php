<?php

namespace Database\Factories;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'code' => fake()->unique()->bothify('ROOM-###'),
            'name' => fake()->randomElement(['Standard', 'Deluxe', 'Suite']),
            'max_adults' => 2,
            'max_children' => 1,
            'max_total_guests' => 3,
            'area' => fake()->randomFloat(2, 18, 80),
            'status' => RoomType::STATUS_ACTIVE,
        ];
    }
}
