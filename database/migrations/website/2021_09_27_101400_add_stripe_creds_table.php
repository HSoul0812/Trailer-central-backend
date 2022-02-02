<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStripeCredsTable extends Migration
{
    private const DEFAULT_TEST_CREDS = [
        'publishable' => 'pk_test_SrHkSx5n3ekBXKiV9cLrRFe9',
        'secret' => 'sk_test_cDKDKSNuzJde6031tghXKTCU',
    ];

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

            DB::table('stripe_checkout_credentials')->insert(self::DEFAULT_TEST_CREDS);
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
