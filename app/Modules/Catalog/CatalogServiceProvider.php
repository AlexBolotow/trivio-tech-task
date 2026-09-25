<?php

namespace App\Modules\Catalog;

use App\Modules\Catalog\Application\Queries\GetHotels\GetHotelsQueryService;
use App\Modules\Catalog\Infrastructure\Persistence\QueryBuilder\QueryBuilderGetHotelsQueryService;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GetHotelsQueryService::class, QueryBuilderGetHotelsQueryService::class);
    }
}
