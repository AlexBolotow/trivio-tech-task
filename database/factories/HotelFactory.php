<?php

namespace Database\Factories;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\City;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => fake()->company().' Hotel',
            'address' => fake()->streetAddress(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'description' => fake()->paragraph(),
            'check_in_time' => '15:00:00',
            'check_out_time' => '11:00:00',
            'status' => Hotel::STATUS_ACTIVE,
        ];
    }
}
