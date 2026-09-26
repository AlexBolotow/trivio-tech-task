<?php

namespace App\Modules\Import\Infrastructure\Persistence\Eloquent\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupplierRecord extends Model
{
    protected $table = 'suppliers';

    protected $fillable = ['code', 'name'];

    /** @return HasOne<SupplierImportSettingsRecord, $this> */
    public function importSettings(): HasOne
    {
        return $this->hasOne(SupplierImportSettingsRecord::class, 'supplier_id');
    }

    /** @return HasMany<SupplierImportRecord, $this> */
    public function imports(): HasMany
    {
        return $this->hasMany(SupplierImportRecord::class, 'supplier_id');
    }

    /** @return HasMany<SupplierHotelRecord, $this> */
    public function hotels(): HasMany
    {
        return $this->hasMany(SupplierHotelRecord::class, 'supplier_id');
    }
}
