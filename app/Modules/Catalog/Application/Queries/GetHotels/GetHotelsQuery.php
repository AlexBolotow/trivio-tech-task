<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

final readonly class GetHotelsQuery
{
    public function __construct(
        public int $cityId,
        public int $page,
        public int $perPage,
    ) {
    }
}
