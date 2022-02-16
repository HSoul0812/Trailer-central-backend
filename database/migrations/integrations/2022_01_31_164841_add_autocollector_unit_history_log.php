<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutocollectorUnitHistoryLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autocollector_unit_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stock');
            $table->integer('dealer_id');
            $table->date('date_removed')->nullable();
            $table->timestamp('date_first_seen')->useCurrent();
        });
        
        Schema::table('autocollector_unit_history', function (Blueprint $table) {
            $table->index('dealer_id');
            $table->index('stock');
            $table->index(['dealer_id', 'stock']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autocollector_unit_history');
    }
}
