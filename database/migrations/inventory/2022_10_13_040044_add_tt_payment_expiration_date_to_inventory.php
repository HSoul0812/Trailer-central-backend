<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTTPaymentExpirationDateToInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dateTime('tt_payment_expiration_date')->after('show_on_website')->nullable();
            $table->index("tt_payment_expiration_date", "index_tt_payment_expiration_date");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex("index_tt_payment_expiration_date");
            $table->dropColumn('tt_payment_expiration_date');
        });
    }
}
