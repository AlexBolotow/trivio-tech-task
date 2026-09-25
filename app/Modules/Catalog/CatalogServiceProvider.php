<?php

namespace App\Modules\Catalog;

use App\Modules\Catalog\Application\Queries\ListHotels\ListHotelsQueryService;
use App\Modules\Catalog\Infrastructure\Persistence\QueryBuilder\QueryBuilderListHotelsQueryService;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ListHotelsQueryService::class, QueryBuilderListHotelsQueryService::class);
    }
}
