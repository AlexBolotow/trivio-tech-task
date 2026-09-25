<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

interface GetHotelsQueryService
{
    public function execute(GetHotelsQuery $query): HotelReadPage;
}
