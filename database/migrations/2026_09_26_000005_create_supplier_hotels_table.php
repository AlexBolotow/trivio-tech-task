<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('external_hotel_id', 128);
            $table->string('name');
            $table->string('city_name');
            $table->char('country_code', 2);
            $table->string('address', 512)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignUuid('hotel_id')->nullable()->constrained('hotels')->restrictOnDelete();
            $table->foreignUuid('last_seen_import_id')->nullable()->constrained('supplier_imports')->nullOnDelete();
            $table->string('link_status', 32)->default('unmatched');
            $table->decimal('link_confidence', 5, 4)->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'external_hotel_id']);
            $table->index('hotel_id');
            $table->index('last_seen_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_hotels');
    }
};
