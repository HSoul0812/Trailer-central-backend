<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RegisterAddRefund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_pos_register', function (Blueprint $table) {
            //
            $table->decimal('refund_amount', 10, 2)
                ->nullable()
                ->after('floating_amount');
            $table->decimal('refund_counted', 10, 2)
                ->nullable()
                ->after('refund_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_pos_register', function (Blueprint $table) {
            //
            $table->dropColumn('refund_amount');
            $table->dropColumn('refund_counted');
        });
    }
}
