<?php

namespace Database\Seeders;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\City;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomTypePhoto;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $moscow = City::query()->firstOrCreate(
            ['name' => 'Москва', 'country_code' => 'RU'],
        );

        $saintPetersburg = City::query()->firstOrCreate(
            ['name' => 'Санкт-Петербург', 'country_code' => 'RU'],
        );

        $moscowHotel = Hotel::query()->firstOrCreate(
            ['city_id' => $moscow->id, 'name' => 'Тестовый отель Москва'],
            ['address' => 'Тверская улица, 1'],
        );

        $petersburgHotel = Hotel::query()->firstOrCreate(
            ['city_id' => $saintPetersburg->id, 'name' => 'Тестовый отель Петербург'],
            ['address' => 'Невский проспект, 1'],
        );

        $standardRoom = RoomType::query()->firstOrCreate(
            ['hotel_id' => $moscowHotel->id, 'code' => 'STANDARD'],
            [
                'name' => 'Стандартный номер',
                'max_adults' => 2,
                'max_children' => 1,
                'max_total_guests' => 3,
            ],
        );

        $suiteRoom = RoomType::query()->firstOrCreate(
            ['hotel_id' => $moscowHotel->id, 'code' => 'SUITE'],
            [
                'name' => 'Люкс',
                'max_adults' => 3,
                'max_children' => 2,
                'max_total_guests' => 5,
            ],
        );

        $petersburgRoom = RoomType::query()->firstOrCreate(
            ['hotel_id' => $petersburgHotel->id, 'code' => 'STANDARD'],
            ['name' => 'Стандартный номер'],
        );

        foreach ([$standardRoom, $suiteRoom, $petersburgRoom] as $roomType) {
            foreach ([1, 2] as $sortOrder) {
                RoomTypePhoto::query()->firstOrCreate(
                    ['room_type_id' => $roomType->id, 'sort_order' => $sortOrder],
                    ['url' => "https://example.test/catalog/{$roomType->code}-{$sortOrder}.jpg"],
                );
            }
        }
    }
}
