<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToCrmTextCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_text_campaign', function (Blueprint $table) {
            $table->index('user_id', 'USER_ID_SOLO');
        });

        Schema::table('crm_text_blast', function (Blueprint $table) {
            $table->index('user_id', 'USER_ID_SOLO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_text_campaign', function (Blueprint $table) {
            $table->dropIndex('USER_ID_SOLO');
        });

        Schema::table('crm_text_blast', function (Blueprint $table) {
            $table->dropIndex('USER_ID_SOLO');
        });
    }
}
