<?php

namespace App\Modules\Import;

use App\Modules\Import\Domain\Repository\SupplierImportChunkRepository;
use App\Modules\Import\Domain\Repository\SupplierImportRepository;
use App\Modules\Import\Infrastructure\Persistence\Eloquent\EloquentSupplierImportChunkRepository;
use App\Modules\Import\Infrastructure\Persistence\Eloquent\EloquentSupplierImportRepository;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierImportRepository::class, EloquentSupplierImportRepository::class);
        $this->app->bind(SupplierImportChunkRepository::class, EloquentSupplierImportChunkRepository::class);
    }
}
