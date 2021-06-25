<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManuallyColumnToQbPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_payment_methods', function (Blueprint $table) {
            $table->boolean('is_manually')->after('is_visible')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_manually');
        });
    }
}
