<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePaymentMethodNameToQbPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    DB::table('qb_payment_methods')->where('name', '=', 'PO')->update(['name' => 'A/R']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    DB::table('qb_payment_methods')->where('name', '=', 'A/R')->update(['name' => 'PO']);
    }
}