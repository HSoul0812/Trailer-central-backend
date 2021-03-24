<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesPersonIdToCrmInteractionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->integer('sales_person_id')->unsigned()->nullable()->after('user_id');

            $table->foreign('sales_person_id')->references('id')->on('crm_sales_person');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->dropForeign(['sales_person_id']);
            $table->dropColumn('sales_person_id');
        });
    }
}
