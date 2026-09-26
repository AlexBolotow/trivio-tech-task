<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('supplier_import_chunks', function (Blueprint $table): void {
            $table->dropIndex('supplier_import_chunks_status_last_heartbeat_at_index');
            $table->dropColumn([
                'attempts',
                'last_heartbeat_at',
                'error_code',
                'error_message',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('supplier_import_chunks', function (Blueprint $table): void {
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('last_heartbeat_at', precision: 6)->nullable();
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->index(['status', 'last_heartbeat_at']);
        });
    }
};
