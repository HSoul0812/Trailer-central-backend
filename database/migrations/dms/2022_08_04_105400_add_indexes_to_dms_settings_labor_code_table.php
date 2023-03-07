<?php

use App\Models\CRM\Dms\ServiceOrder\LaborCode;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToDmsSettingsLaborCodeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(LaborCode::getTableName(), function (Blueprint $table) {
            $table->index(['dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(LaborCode::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['dealer_id']);
        });
    }
}
