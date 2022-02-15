<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAvgCostToParts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->decimal('latest_cost', 9, 2)->nullable()->after('dealer_cost');
            // Add a description to dealer_cost field
            $table->decimal('dealer_cost', 9, 2)->default(0)->comment('Dealer average cost')->change();
        });

        DB::statement("UPDATE parts_v1 SET `latest_cost` = `dealer_cost` WHERE latest_cost IS NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->dropColumn('latest_cost');
        });
    }
}
