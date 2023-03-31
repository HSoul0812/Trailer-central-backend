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
    private const REMOVE_UNMAPPED_ON_FACTORY_UNITS = 'not_save_unmapped_on_factory_units';
    private const CONDITIONAL_TITLE_FORMAT = 'conditional_title_format';
    private const USE_BRANDS_FOR_FACTORY_MAPPINGS = 'use_brands_for_factory_mapping';
    private const CHECK_FOR_BDV_MATCHING = 'check_images_for_bdv_matching';
    private const MARK_SOLD_MANUALLY_ADDED_ITEMS = 'mark_sold_manually_added_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->boolean(self::USE_PARTIAL_UPDATE_FIELD)->default(false);
            $table->boolean(self::MARK_SOLD_MANUALLY_ADDED_ITEMS)->default(true);
            $table->boolean(self::USE_BRANDS_FOR_FACTORY_MAPPINGS)->default(false);
            $table->boolean(self::CHECK_FOR_BDV_MATCHING)->default(false);
            $table->dateTime(self::LAST_FULL_RUN_FIELD)->nullable();
            $table->integer(self::DAYS_TILL_FULL_RUN_FIELD)->nullable();
            $table->boolean(self::REMOVE_UNMAPPED_ON_FACTORY_UNITS)->default(true);
            $table->string(self::CONDITIONAL_TITLE_FORMAT, 255)->nullable();
        });
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
            $table->dropColumn(self::MARK_SOLD_MANUALLY_ADDED_ITEMS);
            $table->dropColumn(self::USE_BRANDS_FOR_FACTORY_MAPPINGS);
            $table->dropColumn(self::CHECK_FOR_BDV_MATCHING);
            $table->dropColumn(self::LAST_FULL_RUN_FIELD);
            $table->dropColumn(self::DAYS_TILL_FULL_RUN_FIELD);
            $table->dropColumn(self::REMOVE_UNMAPPED_ON_FACTORY_UNITS);
            $table->dropColumn(self::CONDITIONAL_TITLE_FORMAT);
        });
    }
}
