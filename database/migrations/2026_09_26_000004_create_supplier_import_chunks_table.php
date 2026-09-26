<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_import_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('supplier_import_id')->constrained('supplier_imports')->cascadeOnDelete();
            $table->unsignedInteger('chunk_number');
            $table->json('source_locator');
            $table->unsignedInteger('records_count')->default(0);
            $table->string('status', 32)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('last_heartbeat_at', precision: 6)->nullable();
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('started_at', precision: 6)->nullable();
            $table->dateTime('finished_at', precision: 6)->nullable();
            $table->timestamps();

            $table->unique(['supplier_import_id', 'chunk_number']);
            $table->index(['status', 'last_heartbeat_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_import_chunks');
    }
};
