<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnPartsAuditLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('parts_audit_log', function (Blueprint $table) {
            $table->index(['part_id', 'bin_id'], 'parts_audit_log_ids_index');
            $table->index(['part_id', 'bin_id', 'created_at'], 'parts_audit_log_ids_and_time_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('parts_audit_log', function (Blueprint $table) {
            $table->dropIndex('parts_audit_log_ids_index');
            $table->dropIndex('parts_audit_log_ids_and_time_index');
        });
    }
}
