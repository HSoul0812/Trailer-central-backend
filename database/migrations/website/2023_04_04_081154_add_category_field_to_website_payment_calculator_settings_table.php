<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryFieldToWebsitePaymentCalculatorSettingsTable extends Migration
{
    private const PAYMENT_CALCULATOR_TABLE = 'website_payment_calculator_settings';
    private const INVENTORY_CATEGORY_TABLE = 'inventory_category';
    private const CATEGORY_FIELD = 'inventory_category_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::PAYMENT_CALCULATOR_TABLE, function (Blueprint $table) {
            $table->integer(self::CATEGORY_FIELD)->nullable()->after('entity_type_id');

            $table->foreign(self::CATEGORY_FIELD, 'payment_calculator_settings_inventory_category_id_foreign')
                ->references(self::CATEGORY_FIELD)
                ->on(self::INVENTORY_CATEGORY_TABLE)
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::PAYMENT_CALCULATOR_TABLE, function (Blueprint $table) {
            $table->dropForeign([self::CATEGORY_FIELD]);
            $table->dropColumn(self::CATEGORY_FIELD);
        });
    }
}
