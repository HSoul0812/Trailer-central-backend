<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValuesMappingFieldToWebsiteConfigDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_config_default', function (Blueprint $table) {
            $table->text('values_mapping')->nullable()->after('values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_config_default', function (Blueprint $table) {
            $table->dropColumn('values_mapping');
        });
    }
}
