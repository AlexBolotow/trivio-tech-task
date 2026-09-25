<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

final readonly class ListHotelsQuery
{
    public function __construct(
        public int $cityId,
        public int $page,
        public int $perPage,
    ) {}
}
