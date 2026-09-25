<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomType;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\RoomTypePhoto;
use Illuminate\Database\Eloquent\Builder;

/**
 * Learning comparison only. The application query path uses ListHotelsQueryService.
 */
final readonly class EloquentListHotelsQueryExample
{
    public function execute(int $cityId, int $page, int $perPage): HotelReadPage
    {
        $paginator = Hotel::query()
            ->active()
            ->where('city_id', $cityId)
            ->select(['id', 'name', 'address'])
            ->with([
                'roomTypes' => fn (Builder $query) => $query
                    ->active()
                    ->select([
                        'id',
                        'hotel_id',
                        'code',
                        'name',
                        'max_adults',
                        'max_children',
                        'max_total_guests',
                    ])
                    ->orderBy('code')
                    ->orderBy('id'),
                'roomTypes.photos' => fn (Builder $query) => $query
                    ->select(['id', 'room_type_id', 'url', 'sort_order'])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()
            ->map(fn (Hotel $hotel): HotelReadModel => new HotelReadModel(
                id: $hotel->id,
                name: $hotel->name,
                address: $hotel->address,
                roomTypes: $hotel->roomTypes
                    ->map(fn (RoomType $roomType): RoomTypeReadModel => new RoomTypeReadModel(
                        id: $roomType->id,
                        code: $roomType->code,
                        name: $roomType->name,
                        maxAdults: $roomType->max_adults,
                        maxChildren: $roomType->max_children,
                        maxTotalGuests: $roomType->max_total_guests,
                        photos: $roomType->photos
                            ->map(fn (RoomTypePhoto $photo): string => $photo->url)
                            ->all(),
                    ))
                    ->all(),
            ))
            ->all();

        return new HotelReadPage(
            items: $items,
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
        );
    }
}
