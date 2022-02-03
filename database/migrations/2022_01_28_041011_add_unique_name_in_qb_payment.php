<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueNameInQbPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            $table->unique(['dealer_id', 'doc_num'], 'doc_nuique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            $table->dropUnique('doc_unique');
        });
    }
}
