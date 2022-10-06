<?php

use App\Models\Marketing\Facebook\Marketplace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddTfaCodeToFbAppMarketingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Marketplace::TABLE_NAME, function ($table) {
            $table->string('tfa_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Marketplace::TABLE_NAME, function ($table) {
            $table->dropColumn('tfa_code');
        });
    }
}
