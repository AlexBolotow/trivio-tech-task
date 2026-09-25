<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

final readonly class GetHotelsQueryHandler
{
    public function __construct(private GetHotelsQueryService $queryService) {}

    public function handle(GetHotelsQuery $query): HotelReadPage
    {
        return $this->queryService->execute($query);
    }
}
