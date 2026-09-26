<?php

namespace App\Modules\Catalog\UI\Http\GetHotels;

use App\Modules\Catalog\Application\Queries\GetHotels\HotelReadModel;
use App\Modules\Catalog\Application\Queries\GetHotels\HotelReadPage;
use App\Modules\Catalog\Application\Queries\GetHotels\RoomTypeReadModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HotelReadPage */
final class HotelCatalogResource extends JsonResource
{
    /** @return list<array<string, mixed>> */
    public function toArray(Request $request): array
    {
        /** @var HotelReadPage $page */
        $page = $this->resource;

        return array_map(static fn (HotelReadModel $hotel): array => [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'address' => $hotel->address,
            'room_types' => array_map(static fn (RoomTypeReadModel $roomType): array => [
                'id' => $roomType->id,
                'code' => $roomType->code,
                'name' => $roomType->name,
                'max_adults' => $roomType->maxAdults,
                'max_children' => $roomType->maxChildren,
                'max_total_guests' => $roomType->maxTotalGuests,
                'photos' => $roomType->photos,
            ], $hotel->roomTypes),
        ], $page->items);
    }

    /** @return array<string, array<string, int>> */
    public function with(Request $request): array
    {
        /** @var HotelReadPage $page */
        $page = $this->resource;

        return [
            'meta' => [
                'current_page' => $page->currentPage,
                'per_page' => $page->perPage,
                'total' => $page->total,
                'last_page' => $page->lastPage,
            ],
        ];
    }
}
