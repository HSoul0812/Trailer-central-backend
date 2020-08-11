<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntityElementsFieldToWebsiteEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_entity', function (Blueprint $table) {
            $table->text('entity_elements')->nullable()->after('entity_config');
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
            $table->dropColumn('entity_elements');
        });
    }
}
