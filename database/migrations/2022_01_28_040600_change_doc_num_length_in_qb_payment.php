<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDocNumLengthInQbPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_payment', function (Blueprint $table) {
            $table->string('doc_num', 21)->change();
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
            $table->string('doc_num', 50)->change;
        });
    }
}
