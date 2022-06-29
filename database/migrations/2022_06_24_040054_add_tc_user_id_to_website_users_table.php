<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTcUserIdToWebsiteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('website_users', 'tc_user_id')) {
            Schema::table('website_users', function (Blueprint $table) {
                $table->unsignedInteger('tc_user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_users', function (Blueprint $table) {
            $table->dropColumn('tc_user_id');
        });
    }
}
