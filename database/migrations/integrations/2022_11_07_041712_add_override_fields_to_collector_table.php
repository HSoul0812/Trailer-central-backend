<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOverrideFieldsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->addColumn('tinyInteger', 'override_all', ['length' => 2])->default(0);
            $table->addColumn('tinyInteger', 'override_images', ['length' => 2])->default(0);
            $table->addColumn('tinyInteger', 'override_video', ['length' => 2])->default(0);
            $table->addColumn('tinyInteger', 'override_prices', ['length' => 2])->default(0);
            $table->addColumn('tinyInteger', 'override_attributes', ['length' => 2])->default(0);
            $table->addColumn('tinyInteger', 'override_descriptions', ['length' => 2])->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->dropColumn('override_all');
            $table->dropColumn('override_images');
            $table->dropColumn('override_video');
            $table->dropColumn('override_prices');
            $table->dropColumn('override_attributes');
            $table->dropColumn('override_descriptions');
        });
    }
}
