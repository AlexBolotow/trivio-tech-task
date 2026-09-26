<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('source_version', 255)->nullable();
            $table->binary('source_checksum', 32, true);
            $table->string('source_uri', 2048);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('child_task_size_records');
            $table->unsignedTinyInteger('min_success_rate')->default(80);
            $table->unsignedInteger('total_chunks')->default(0);
            $table->unsignedInteger('successful_chunks')->default(0);
            $table->unsignedInteger('failed_chunks')->default(0);
            $table->dateTime('started_at', precision: 6)->nullable();
            $table->dateTime('finished_at', precision: 6)->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'source_checksum']);
            $table->index(['supplier_id', 'status']);
        });

        DB::statement('ALTER TABLE supplier_imports ADD CONSTRAINT supplier_import_chunk_size_positive CHECK (child_task_size_records > 0)');
        DB::statement('ALTER TABLE supplier_imports ADD CONSTRAINT supplier_import_success_rate_range CHECK (min_success_rate <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_imports');
    }
};
