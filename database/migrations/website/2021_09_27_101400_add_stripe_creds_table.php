<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeCredsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stripe_checkout_credentials')) {
            Schema::create('stripe_checkout_credentials', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('publishable')->nullable();
                $table->string('secret')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_checkout_credentials');
    }
}
