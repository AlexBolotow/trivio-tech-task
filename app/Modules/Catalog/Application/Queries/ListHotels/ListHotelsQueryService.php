<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

use Illuminate\Database\ConnectionInterface;

final readonly class ListHotelsQueryService
{
    public function __construct(private ConnectionInterface $database) {}

    public function execute(int $cityId, int $page, int $perPage): HotelReadPage
    {
        $total = $this->database->table('hotels')
            ->where('city_id', $cityId)
            ->where('status', 'active')
            ->count();

        $hotelRows = $this->database->table('hotels')
            ->select(['id', 'name', 'address'])
            ->where('city_id', $cityId)
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        if ($hotelRows->isEmpty()) {
            return new HotelReadPage(
                items: [],
                currentPage: $page,
                perPage: $perPage,
                total: $total,
                lastPage: max(1, (int) ceil($total / $perPage)),
            );
        }

        $hotelIds = $hotelRows->pluck('id')->all();

        $roomTypeRows = $this->database->table('room_types')
            ->select(['id', 'hotel_id', 'code', 'name', 'max_adults', 'max_children', 'max_total_guests'])
            ->whereIn('hotel_id', $hotelIds)
            ->where('status', 'active')
            ->orderBy('hotel_id')
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $roomTypeIds = $roomTypeRows->pluck('id')->all();
        $photosByRoomType = $roomTypeIds === []
            ? collect()
            : $this->database->table('room_type_photos')
                ->select(['room_type_id', 'url'])
                ->whereIn('room_type_id', $roomTypeIds)
                ->orderBy('room_type_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('room_type_id');

        $roomTypesByHotel = $roomTypeRows->groupBy('hotel_id');

        $items = $hotelRows->map(function (object $hotel) use ($roomTypesByHotel, $photosByRoomType): HotelReadModel {
            $roomTypes = ($roomTypesByHotel->get($hotel->id) ?? collect())
                ->map(fn (object $roomType): RoomTypeReadModel => new RoomTypeReadModel(
                    id: (string) $roomType->id,
                    code: (string) $roomType->code,
                    name: (string) $roomType->name,
                    maxAdults: $roomType->max_adults === null ? null : (int) $roomType->max_adults,
                    maxChildren: $roomType->max_children === null ? null : (int) $roomType->max_children,
                    maxTotalGuests: $roomType->max_total_guests === null ? null : (int) $roomType->max_total_guests,
                    photos: ($photosByRoomType->get($roomType->id) ?? collect())
                        ->map(fn (object $photo): string => (string) $photo->url)
                        ->all(),
                ))
                ->all();

            return new HotelReadModel(
                id: (string) $hotel->id,
                name: (string) $hotel->name,
                address: (string) $hotel->address,
                roomTypes: $roomTypes,
            );
        })->all();

        return new HotelReadPage(
            items: $items,
            currentPage: $page,
            perPage: $perPage,
            total: $total,
            lastPage: max(1, (int) ceil($total / $perPage)),
        );
    }
}
