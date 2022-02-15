<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('business_id')->index();
            $table->bigInteger('catalog_id')->unique();
            $table->bigInteger('feed_id')->unique();
            $table->string('feed_title');
            $table->string('feed_url');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->timestamp('imported_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('fbapp_catalog', function (Blueprint $table) {
            $table->dropColumn('feed_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_catalog', function (Blueprint $table) {
            $table->bigInteger('feed_id')->index()->after('account_name')->nullable();
        });

        Schema::dropIfExists('fbapp_feeds');
    }
}
