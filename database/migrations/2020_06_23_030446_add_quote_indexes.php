<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->index('dealer_id', 'DEALER_ID');
            $table->index('title', 'TITLE');
            $table->index('created_at', 'CREATED_AT');
            $table->index('total_price', 'TOTAL_PRICE');
            $table->index(['is_archived', 'dealer_id'], 'ARCHIVED_LOOKUP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->dropIndex('DEALER_ID');
            $table->dropIndex('TITLE');
            $table->dropIndex('CREATED_AT');
            $table->dropIndex('TOTAL_PRICE');
            $table->dropIndex('ARCHIVED_LOOKUP');
        });
    }
}
