<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSliderFieldsToWebsiteEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_entity', function (Blueprint $table) {
            $table->boolean('is_slider_active')->default(0)->after('entity_config');
            $table->text('slider_config')->nullable()->after('is_slider_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_entity', function (Blueprint $table) {
            $table->dropColumn('is_slider_active');
            $table->dropColumn('slider_config');
        });
    }
}
