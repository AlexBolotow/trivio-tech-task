<?php

namespace Database\Factories;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'country_code' => fake()->countryCode(),
        ];
    }
}
