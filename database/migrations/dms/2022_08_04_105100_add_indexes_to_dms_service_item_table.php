<?php

use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToDmsServiceItemTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(ServiceItem::getTableName(), function (Blueprint $table) {
            $table->index(['repair_order_id']);
            $table->index(['labor_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(ServiceItem::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['repair_order_id']);
            $table->dropIndex(['labor_code_id']);
        });
    }
}
