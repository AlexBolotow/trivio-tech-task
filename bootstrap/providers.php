<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    App\Modules\Catalog\CatalogServiceProvider::class,
    App\Modules\Import\ImportServiceProvider::class,
];
