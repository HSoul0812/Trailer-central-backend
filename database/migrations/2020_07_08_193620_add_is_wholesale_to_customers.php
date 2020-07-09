<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWholesaleToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_customer', function (Blueprint $table) {
            //
            $table->tinyInteger('is_wholesale')->default(0);
            $table->decimal('default_discount_percent', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_customer', function (Blueprint $table) {
            //
            $table->dropColumn('is_wholesale');
            $table->dropColumn('default_discount_percent');
        });
    }
}
