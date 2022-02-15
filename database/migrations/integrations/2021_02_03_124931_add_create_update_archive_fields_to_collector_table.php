<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreateUpdateArchiveFieldsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->boolean('create_items')->default(1)->after('path_to_data');
            $table->boolean('update_items')->default(1)->after('create_items');
            $table->boolean('archive_items')->default(1)->after('update_items');
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
            $table->dropColumn('create_items');
            $table->dropColumn('update_items');
            $table->dropColumn('archive_items');
        });
    }
}
