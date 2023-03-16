<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsePartialUpdateFieldsToCollectorTable extends Migration
{
    private const TABLE = 'collector';

    private const USE_PARTIAL_UPDATE_FIELD = 'use_partial_update';
    private const LAST_FULL_RUN_FIELD = 'last_full_run';
    private const DAYS_TILL_FULL_RUN_FIELD = 'days_till_full_run';
    private const NOT_ARCHIVE_MANUALLY_ITEMS_FIELD = 'not_archive_manually_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
/*        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::USE_PARTIAL_UPDATE_FIELD);
            $table->dropColumn(self::LAST_FULL_RUN_FIELD);
            $table->dropColumn(self::DAYS_TILL_FULL_RUN_FIELD);
        });*/

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->boolean(self::USE_PARTIAL_UPDATE_FIELD)->default(false);
            $table->boolean(self::NOT_ARCHIVE_MANUALLY_ITEMS_FIELD)->default(false);
            $table->dateTime(self::LAST_FULL_RUN_FIELD)->nullable();
            $table->integer(self::DAYS_TILL_FULL_RUN_FIELD)->nullable();
        });

        exit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::USE_PARTIAL_UPDATE_FIELD);
            $table->dropColumn(self::LAST_FULL_RUN_FIELD);
            $table->dropColumn(self::DAYS_TILL_FULL_RUN_FIELD);
            $table->dropColumn(self::NOT_ARCHIVE_MANUALLY_ITEMS_FIELD);
        });
    }
}
