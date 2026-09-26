<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('expedia_hotel_details', function (Blueprint $table) {
            $table->foreignId('supplier_hotel_id')->primary()->constrained('supplier_hotels')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->timestamps();
        });

        Schema::create('expedia_room_type_details', function (Blueprint $table) {
            $table->foreignId('supplier_room_type_id')->primary()->constrained('supplier_room_types')->cascadeOnDelete();
            $table->json('amenities')->nullable();
            $table->timestamps();
        });

        Schema::create('pegas_room_type_details', function (Blueprint $table) {
            $table->foreignId('supplier_room_type_id')->primary()->constrained('supplier_room_types')->cascadeOnDelete();
            $table->decimal('nightly_price', 12, 2);
            $table->char('currency', 3);
            $table->timestamps();
        });

        Schema::create('supplier_hotel_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_hotel_id')->constrained('supplier_hotels')->cascadeOnDelete();
            $table->string('source_url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['supplier_hotel_id', 'sort_order']);
        });

        Schema::create('supplier_room_type_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_room_type_id')->constrained('supplier_room_types')->cascadeOnDelete();
            $table->string('source_url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['supplier_room_type_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_room_type_photos');
        Schema::dropIfExists('supplier_hotel_photos');
        Schema::dropIfExists('pegas_room_type_details');
        Schema::dropIfExists('expedia_room_type_details');
        Schema::dropIfExists('expedia_hotel_details');
    }
};
