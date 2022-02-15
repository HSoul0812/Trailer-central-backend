<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsToDmsUnitSaleTradeInV1 extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            DB::statement('ALTER TABLE dms_unit_sale_trade_in_v1 MODIFY trade_value DOUBLE(8,2) NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE dms_unit_sale_trade_in_v1 MODIFY temp_inv_cost_of_unit DOUBLE(8,2) NULL DEFAULT 0.00');
            $table->string('temp_inv_mfg', 100)->nullable()->change();
            $table->string('temp_inv_category', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            DB::statement('ALTER TABLE dms_unit_sale_trade_in_v1 MODIFY trade_value DOUBLE(8,2)');
            DB::statement('ALTER TABLE dms_unit_sale_trade_in_v1 MODIFY temp_inv_cost_of_unit DOUBLE(8,2)');
            $table->string('temp_inv_mfg', 100)->change();
            $table->string('temp_inv_category', 100)->change();
        });
    }
}
