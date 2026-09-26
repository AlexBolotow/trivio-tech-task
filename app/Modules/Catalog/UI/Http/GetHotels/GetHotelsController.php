<?php

namespace App\Modules\Catalog\UI\Http\GetHotels;

use App\Modules\Catalog\Application\Queries\GetHotels\GetHotelsQuery;
use App\Modules\Catalog\Application\Queries\GetHotels\GetHotelsQueryHandler;

final readonly class GetHotelsController
{
    public function __construct(private GetHotelsQueryHandler $handler)
    {
    }

    public function __invoke(GetHotelsRequest $request): HotelCatalogResource
    {
        $page = $this->handler->handle(new GetHotelsQuery(
            cityId: $request->cityId(),
            page: $request->page(),
            perPage: $request->perPage(),
        ));

        return new HotelCatalogResource($page);
    }
}
