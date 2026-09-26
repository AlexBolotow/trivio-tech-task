<?php

namespace Tests\Integration\Modules\Catalog;

use App\Modules\Catalog\Application\Queries\GetHotels\GetHotelsQuery;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\City;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomTypePhoto;
use App\Modules\Catalog\Infrastructure\Persistence\QueryBuilder\QueryBuilderHotelQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class QueryBuilderHotelQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_and_orders_catalog_rows_and_loads_nested_data_in_batches(): void
    {
        $city = City::factory()->create();
        $otherCity = City::factory()->create();

        $hotelWithLowerId = Hotel::factory()->for($city)->create([
            'id' => '00000000-0000-4000-8000-000000000001',
            'name' => 'Alpha Hotel',
            'status' => Hotel::STATUS_ACTIVE,
        ]);
        $hotelWithHigherId = Hotel::factory()->for($city)->create([
            'id' => '00000000-0000-4000-8000-000000000002',
            'name' => 'Alpha Hotel',
            'status' => Hotel::STATUS_ACTIVE,
        ]);
        Hotel::factory()->for($city)->create([
            'name' => 'Aardvark Hotel',
            'status' => Hotel::STATUS_INACTIVE,
        ]);
        Hotel::factory()->for($otherCity)->create([
            'name' => 'Other City Hotel',
            'status' => Hotel::STATUS_ACTIVE,
        ]);

        $standardRoom = RoomType::factory()->for($hotelWithLowerId)->create([
            'id' => '10000000-0000-4000-8000-000000000001',
            'code' => 'STANDARD',
            'name' => 'Standard room',
            'max_adults' => 2,
            'max_children' => 1,
            'max_total_guests' => 3,
            'status' => RoomType::STATUS_ACTIVE,
        ]);
        RoomType::factory()->for($hotelWithLowerId)->create([
            'code' => 'DELUXE',
            'status' => RoomType::STATUS_ACTIVE,
        ]);
        RoomType::factory()->for($hotelWithLowerId)->create([
            'code' => 'HIDDEN',
            'status' => RoomType::STATUS_INACTIVE,
        ]);
        RoomType::factory()->for($hotelWithHigherId)->create([
            'code' => 'INACTIVE-HOTEL-ROOM',
            'status' => RoomType::STATUS_INACTIVE,
        ]);

        RoomTypePhoto::factory()->for($standardRoom)->create([
            'id' => '20000000-0000-4000-8000-000000000002',
            'url' => 'https://example.test/later.jpg',
            'sort_order' => 10,
        ]);
        RoomTypePhoto::factory()->for($standardRoom)->create([
            'id' => '20000000-0000-4000-8000-000000000001',
            'url' => 'https://example.test/first.jpg',
            'sort_order' => 10,
        ]);
        RoomTypePhoto::factory()->for($standardRoom)->create([
            'url' => 'https://example.test/earliest.jpg',
            'sort_order' => 1,
        ]);

        $connection = DB::connection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        $page = (new QueryBuilderHotelQueryService($connection))->getHotels(new GetHotelsQuery(
            cityId: $city->id,
            page: 1,
            perPage: 10,
        ));

        self::assertSame(2, $page->total);
        self::assertSame([
            $hotelWithLowerId->id,
            $hotelWithHigherId->id,
        ], array_map(static fn ($hotel): string => $hotel->id, $page->items));
        self::assertSame(['DELUXE', 'STANDARD'], array_map(
            static fn ($roomType): string => $roomType->code,
            $page->items[0]->roomTypes,
        ));
        self::assertSame([2, 1, 3], [
            $page->items[0]->roomTypes[1]->maxAdults,
            $page->items[0]->roomTypes[1]->maxChildren,
            $page->items[0]->roomTypes[1]->maxTotalGuests,
        ]);
        self::assertSame([
            'https://example.test/earliest.jpg',
            'https://example.test/first.jpg',
            'https://example.test/later.jpg',
        ], $page->items[0]->roomTypes[1]->photos);
        self::assertSame([], $page->items[1]->roomTypes);
        self::assertCount(4, $connection->getQueryLog(), 'The read must use bounded batch queries, without N+1 loading.');
    }

    public function test_it_paginates_in_stable_order_and_returns_an_empty_page_for_an_unknown_city(): void
    {
        $city = City::factory()->create();
        Hotel::factory()->for($city)->create(['name' => 'Charlie Hotel']);
        Hotel::factory()->for($city)->create(['name' => 'Alpha Hotel']);
        Hotel::factory()->for($city)->create(['name' => 'Bravo Hotel']);

        $service = new QueryBuilderHotelQueryService(DB::connection());
        $secondPage = $service->getHotels(new GetHotelsQuery(
            cityId: $city->id,
            page: 2,
            perPage: 2,
        ));
        $unknownCityPage = $service->getHotels(new GetHotelsQuery(
            cityId: $city->id + 100000,
            page: 1,
            perPage: 20,
        ));

        self::assertSame(3, $secondPage->total);
        self::assertSame(2, $secondPage->currentPage);
        self::assertSame(2, $secondPage->lastPage);
        self::assertSame(['Charlie Hotel'], array_map(
            static fn ($hotel): string => $hotel->name,
            $secondPage->items,
        ));
        self::assertSame([], $unknownCityPage->items);
        self::assertSame(0, $unknownCityPage->total);
        self::assertSame(1, $unknownCityPage->lastPage);
    }
}
