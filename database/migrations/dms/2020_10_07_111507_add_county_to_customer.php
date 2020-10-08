<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountyToCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_customer', function (Blueprint $table) {
            $table->string('county',255)->nullable();
            $table->string('shipping_county',255)->nullable();
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
            $table->dropColumn(['county', 'shipping_county']);
        });
    }
}
