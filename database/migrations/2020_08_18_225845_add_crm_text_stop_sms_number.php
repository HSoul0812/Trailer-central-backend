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
            if(Schema::hasColumn('crm_text_stop', 'sms_number')) {
                $table->string('sms_number', 12)->index();
            }

            if(Schema::hasColumn('crm_text_stop', 'response_id')) {
                $table->dropColumn('response_id');

                $table->dropForeign('crm_text_stop_lead_id_foreign');

                $table->dropForeign('crm_text_stop_text_id_foreign');

                $table->dropForeign('crm_text_stop_response_id_foreign');
            }
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
            //
        });
    }
}
