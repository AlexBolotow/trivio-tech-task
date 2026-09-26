<?php

namespace App\Modules\Catalog;

use App\Modules\Catalog\Application\Queries\HotelQueryService;
use App\Modules\Catalog\Infrastructure\Persistence\QueryBuilder\QueryBuilderHotelQueryService;
use App\Modules\Catalog\UI\Http\GetHotels\GetHotelsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HotelQueryService::class, QueryBuilderHotelQueryService::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1/catalog')
            ->name('api.v1.catalog.')
            ->group(function (): void {
                Route::get('/hotels', GetHotelsController::class)->name('hotels.index');
            });
    }
}
