<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

final readonly class HotelReadPage
{
    /**
     * @param  list<HotelReadModel>  $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {
    }
}
