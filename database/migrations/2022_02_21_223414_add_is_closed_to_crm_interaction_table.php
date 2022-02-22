<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsClosedToCrmInteractionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_interaction', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('interaction_time')->index();
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
            $table->dropColumn('is_closed');
        });
    }
}
