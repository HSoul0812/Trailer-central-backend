<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostsPerDayToFbappMarketplaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->integer('posts_per_day')->default(null)->nullable()->after('tfa_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->dropColumn('posts_per_day');
        });
    }
}
