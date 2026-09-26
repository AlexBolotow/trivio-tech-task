<?php

namespace Tests\Feature\Modules\Catalog;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\City;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomTypePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class GetHotelsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_catalog_resource_for_active_hotels_in_the_requested_city(): void
    {
        $city = City::factory()->create();
        $otherCity = City::factory()->create();

        $hotel = Hotel::factory()->for($city)->create([
            'name' => 'Grand Hotel',
            'address' => 'Main street, 1',
        ]);
        Hotel::factory()->for($city)->create([
            'name' => 'Inactive Hotel',
            'status' => Hotel::STATUS_INACTIVE,
        ]);
        Hotel::factory()->for($otherCity)->create(['name' => 'Another City Hotel']);

        $roomType = RoomType::factory()->for($hotel)->create([
            'code' => 'STANDARD',
            'name' => 'Standard room',
            'max_adults' => 2,
            'max_children' => 1,
            'max_total_guests' => 3,
        ]);
        RoomType::factory()->for($hotel)->create([
            'code' => 'HIDDEN',
            'status' => RoomType::STATUS_INACTIVE,
        ]);
        RoomTypePhoto::factory()->for($roomType)->create([
            'url' => 'https://example.test/second.jpg',
            'sort_order' => 2,
        ]);
        RoomTypePhoto::factory()->for($roomType)->create([
            'url' => 'https://example.test/first.jpg',
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/catalog/hotels?city_id='.$city->id)
            ->assertOk()
            ->assertExactJson([
                'data' => [[
                    'id' => $hotel->id,
                    'name' => 'Grand Hotel',
                    'address' => 'Main street, 1',
                    'room_types' => [[
                        'id' => $roomType->id,
                        'code' => 'STANDARD',
                        'name' => 'Standard room',
                        'max_adults' => 2,
                        'max_children' => 1,
                        'max_total_guests' => 3,
                        'photos' => [
                            'https://example.test/first.jpg',
                            'https://example.test/second.jpg',
                        ],
                    ]],
                ]],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 20,
                    'total' => 1,
                    'last_page' => 1,
                ],
            ]);
    }

    public function test_it_returns_an_empty_page_for_an_unknown_city(): void
    {
        $this->getJson('/api/v1/catalog/hotels?city_id=999999999')
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ]);
    }

    public function test_it_returns_the_requested_page_and_metadata(): void
    {
        $city = City::factory()->create();
        Hotel::factory()->for($city)->create(['name' => 'Charlie Hotel']);
        $expectedHotel = Hotel::factory()->for($city)->create(['name' => 'Bravo Hotel']);
        Hotel::factory()->for($city)->create(['name' => 'Alpha Hotel']);
        Hotel::factory()->for($city)->create([
            'name' => 'Inactive Hotel',
            'status' => Hotel::STATUS_INACTIVE,
        ]);

        $this->getJson('/api/v1/catalog/hotels?city_id='.$city->id.'&page=2&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $expectedHotel->id)
            ->assertJsonPath('data.0.name', 'Bravo Hotel')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 3);
    }

    /** @return iterable<string, array{array<string, int>, string}> */
    public static function invalidQueryParameters(): iterable
    {
        yield 'missing city id' => [[], 'city_id'];
        yield 'non-positive city id' => [['city_id' => 0], 'city_id'];
        yield 'non-positive page' => [['city_id' => 1, 'page' => 0], 'page'];
        yield 'page size exceeds maximum' => [['city_id' => 1, 'per_page' => 51], 'per_page'];
    }

    #[DataProvider('invalidQueryParameters')]
    public function test_it_rejects_invalid_query_parameters(array $parameters, string $invalidField): void
    {
        $query = http_build_query($parameters);
        $url = '/api/v1/catalog/hotels'.($query === '' ? '' : '?'.$query);

        $this->getJson($url)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($invalidField);
    }
}
