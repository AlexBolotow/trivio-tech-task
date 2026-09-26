<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

use App\Modules\Catalog\Application\Queries\HotelQueryService;

final readonly class GetHotelsQueryHandler
{
    public function __construct(private HotelQueryService $queryService)
    {
    }

    public function handle(GetHotelsQuery $query): HotelReadPage
    {
        return $this->queryService->getHotels($query);
    }
}
