<?php

use Illuminate\Database\Migrations\Migration;
use Grimzy\LaravelMysqlSpatial\Schema\Blueprint;

class RemoveIndexFacebookIdUniqueFbappListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_listings', function (Blueprint $table) {
            $table->dropUnique(['facebook_id']);
        
            $table->index(['facebook_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_listings', function (Blueprint $table) {
            $table->dropIndex(['facebook_id']);
        
            $table->unique(['facebook_id']);
        });
    }
}
