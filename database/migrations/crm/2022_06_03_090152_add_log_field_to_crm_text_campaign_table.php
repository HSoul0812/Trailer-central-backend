<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogFieldToCrmTextCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_text_campaign', function (Blueprint $table) {
            $table->text('log')->nullable();
            $table->boolean('is_error')->default(false)->after('is_enabled');
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
            $table->dropColumn('log');
            $table->dropColumn('is_error');
        });
    }
}
