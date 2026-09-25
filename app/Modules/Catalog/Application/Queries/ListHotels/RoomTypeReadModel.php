<?php

namespace App\Modules\Catalog\Application\Queries\ListHotels;

final readonly class RoomTypeReadModel
{
    /**
     * @param  list<string>  $photos
     */
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?int $maxAdults,
        public ?int $maxChildren,
        public ?int $maxTotalGuests,
        public array $photos,
    ) {}
}
