<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

final readonly class ListHotelsQueryHandler
{
    public function __construct(private ListHotelsQueryService $queryService) {}

    public function handle(ListHotelsQuery $query): HotelReadPage
    {
        return $this->queryService->execute($query);
    }
}
