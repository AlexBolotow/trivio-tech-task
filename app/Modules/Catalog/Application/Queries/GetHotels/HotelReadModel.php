<?php

namespace App\Modules\Catalog\Application\Queries\GetHotels;

final readonly class HotelReadModel
{
    /**
     * @param  list<RoomTypeReadModel>  $roomTypes
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $address,
        public array $roomTypes,
    ) {
    }
}
