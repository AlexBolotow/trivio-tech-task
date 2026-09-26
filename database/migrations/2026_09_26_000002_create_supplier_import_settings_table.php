<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_import_settings', function (Blueprint $table) {
            $table->foreignId('supplier_id')->primary()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('check_interval_minutes');
            $table->unsignedInteger('child_task_size_records');
            $table->unsignedTinyInteger('min_success_rate')->default(80);
            $table->dateTime('next_check_at', precision: 6)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE supplier_import_settings ADD CONSTRAINT supplier_settings_check_interval_positive CHECK (check_interval_minutes > 0)');
        DB::statement('ALTER TABLE supplier_import_settings ADD CONSTRAINT supplier_settings_chunk_size_positive CHECK (child_task_size_records > 0)');
        DB::statement('ALTER TABLE supplier_import_settings ADD CONSTRAINT supplier_settings_success_rate_range CHECK (min_success_rate <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_import_settings');
    }
};
