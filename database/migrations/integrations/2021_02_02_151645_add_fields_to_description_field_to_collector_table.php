<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFieldsToDescriptionFieldToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->string('path_to_fields_to_description', 254)->nullable()->after('overridable_fields');
            $table->string('fields_to_description', 254)->nullable()->after('path_to_fields_to_description');
        });

        DB::statement("UPDATE collector SET path_to_fields_to_description = 'Options', fields_to_description = 'MajorUnitOptionId,Description' WHERE id = 3");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->dropColumn('fields_to_description');
            $table->dropColumn('path_to_fields_to_description');
        });
    }
}
