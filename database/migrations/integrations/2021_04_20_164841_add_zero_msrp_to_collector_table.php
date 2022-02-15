<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Integration\Collector\Collector;

class AddZeroMsrpToCollectorTable extends Migration
{ 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->boolean('zero_msrp')->default(Collector::MSRP_NOT_ZEROED_OUT_ON_USED);
        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->dropColumn('zero_msrp');
        });
    }
}
