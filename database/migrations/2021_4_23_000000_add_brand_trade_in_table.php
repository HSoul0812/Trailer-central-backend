<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class AddBrandTradeInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->string('temp_inv_brand', 100)->nullable()->default(null)->after('temp_inv_category');
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
            $table->dropColumn('temp_inv_brand');
        });
    }
}
