<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartIdIndexOnBins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('part_bin_qty', function (Blueprint $table) {
            $table->index('part_id', 'part_bin_qty_part_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('part_bin_qty', function (Blueprint $table) {
            $table->dropIndex('part_bin_qty_part_id_index');
        });
    }
}
