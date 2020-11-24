<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadAddCustomerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('website_lead', function (Blueprint $table) {
            $table->integer('customer_id')->nullable();

            $table->index(['customer_id', 'is_spam']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
}
