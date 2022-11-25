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
            $table->integer('quote_id')->nullable()->after('lead_id');

            $table->index(['quote_id']);
        });

        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->integer('quote_id')->nullable()->after('tc_lead_id');

            $table->index(['quote_id']);
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
