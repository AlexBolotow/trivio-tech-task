<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

interface ListHotelsQueryService
{
    public function execute(ListHotelsQuery $query): HotelReadPage;
}
