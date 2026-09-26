<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hotel_id')->constrained()->restrictOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->unsignedSmallInteger('max_adults')->nullable();
            $table->unsignedSmallInteger('max_children')->nullable();
            $table->unsignedSmallInteger('max_total_guests')->nullable();
            $table->decimal('area', 8, 2)->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->unique(['hotel_id', 'code']);
            $table->index(['hotel_id', 'status', 'code', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
