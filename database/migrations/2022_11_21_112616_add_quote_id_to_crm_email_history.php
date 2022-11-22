<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteIdToCrmEmailHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_email_history', function (Blueprint $table) {
            $table->integer('quote_id')->default(0)->after('lead_id');
        });

        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->integer('quote_id')->default(0)->after('tc_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_email_history', function (Blueprint $table) {
            $table->dropColumn('quote_id');
        });

        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->dropColumn('quote_id');
        });
    }
}
