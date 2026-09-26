<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->upsert(
            [
                ['code' => 'expedia', 'name' => 'Expedia'],
                ['code' => 'pegas', 'name' => 'Pegas'],
            ],
            ['code'],
            ['name'],
        );
    }
}
