<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnClosedByRelatedUnitSaleToDmsRepairOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->boolean('closed_by_related_unit_sale')->default(false)->after('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->dropColumn('closed_by_related_unit_sale');
        });
    }
}
