<?php

namespace App\Modules\Catalog;

use App\Modules\Catalog\Application\Queries\HotelQueryService;
use App\Modules\Catalog\Infrastructure\Persistence\QueryBuilder\QueryBuilderHotelQueryService;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HotelQueryService::class, QueryBuilderHotelQueryService::class);
    }
}
