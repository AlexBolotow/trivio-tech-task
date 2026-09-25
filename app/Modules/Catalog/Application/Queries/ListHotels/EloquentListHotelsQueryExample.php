<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Learning comparison only. The application query path uses ListHotelsQueryService.
 */
final readonly class EloquentListHotelsQueryExample
{
    /**
     * @return LengthAwarePaginator<int, Hotel>
     */
    public function execute(int $cityId, int $page, int $perPage): LengthAwarePaginator
    {
        return Hotel::query()
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
                    ->select(['id', 'room_type_id', 'url', 'sort_order']),
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
