<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTcUserLocationIdToWebsiteUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('website_users', 'tc_user_location_id')) {
            Schema::table('website_users', function (Blueprint $table) {
                $table->unsignedInteger('tc_user_location_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('website_users', function (Blueprint $table) {
            $table->dropColumn('tc_user_location_id');
        });
    }
}
