<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPoDefIdToPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_preferences', function (Blueprint $table) {
            $table->integer('po_num_ref_id')
                ->unsigned()
                ->nullable()
                ->after('use_sales_tax')
                ->comment('Definition id to Custom field called "PO #" under Sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_preferences', function (Blueprint $table) {
            $table->dropColumn('po_num_ref_id');
        });
    }
}
