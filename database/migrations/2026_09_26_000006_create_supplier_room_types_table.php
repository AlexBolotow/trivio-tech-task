<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_hotel_id')->constrained('supplier_hotels')->cascadeOnDelete();
            $table->string('external_room_type_id', 128);
            $table->string('name');
            $table->unsignedSmallInteger('max_adults')->nullable();
            $table->unsignedSmallInteger('max_children')->nullable();
            $table->unsignedSmallInteger('max_total_guests')->nullable();
            $table->foreignUuid('room_type_id')->nullable()->constrained('room_types')->restrictOnDelete();
            $table->foreignUuid('last_seen_import_id')->nullable()->constrained('supplier_imports')->nullOnDelete();
            $table->string('link_status', 32)->default('unmatched');
            $table->timestamps();

            $table->unique(['supplier_hotel_id', 'external_room_type_id'], 'supplier_room_external_id_unique');
            $table->index('room_type_id');
            $table->index('last_seen_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_room_types');
    }
};
