<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomerAddNewFields1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('dms_customer', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->text('bill_to')->nullable();
            $table->text('ship_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('dms_customer', function (Blueprint $table) {
            $table->dropColumn('company_name');
            $table->dropColumn('bill_to');
            $table->dropColumn('ship_to');
        });
    }
}
