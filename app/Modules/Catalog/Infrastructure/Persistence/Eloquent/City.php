<?php

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseFactory(CityFactory::class)]
class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
    ];

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
