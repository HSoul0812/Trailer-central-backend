<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSentByFieldToCrmInteractionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->string('sent_by', 255)->nullable()->after('from_email');
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
            $table->dropColumn('sent_by');
        });
    }
}
