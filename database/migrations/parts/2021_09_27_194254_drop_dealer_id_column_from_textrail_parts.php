<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDealerIdColumnFromTextrailParts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('textrail_parts', function (Blueprint $table) {
            $table->dropColumn('dealer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('textrail_parts', function (Blueprint $table) {
            $table->integer('dealer_id');
        });
    }
}
