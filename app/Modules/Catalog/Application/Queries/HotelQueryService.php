<?php

namespace App\Modules\Catalog\Application\Queries;

use App\Modules\Catalog\Application\Queries\GetHotels\GetHotelsQuery;
use App\Modules\Catalog\Application\Queries\GetHotels\HotelReadPage;

interface HotelQueryService
{
    public function getHotels(GetHotelsQuery $query): HotelReadPage;
}
