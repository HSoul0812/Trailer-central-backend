<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrmTextStopSmsNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_text_stop', function (Blueprint $table) {
            $table->string('sms_number', 12)->index();

            $table->dropColumn('response_id');

            /*$table->dropForeign('crm_text_stop_lead_id_foreign');

            $table->dropForeign('crm_text_stop_text_id_foreign');

            $table->dropForeign('crm_text_stop_response_id_foreign');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_text_stop', function (Blueprint $table) {
            $table->dropColumn('sms_number');

            $table->integer('response_id');

            $table->foreign('lead_id')
                    ->references('identifier')
                    ->on('website_lead');

            $table->foreign('text_id')
                    ->references('id')
                    ->on('dealer_texts_log');

            $table->foreign('response_id')
                    ->references('id')
                    ->on('dealer_texts_log');
        });
    }
}
