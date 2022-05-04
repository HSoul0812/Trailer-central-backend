<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubscriptionsPrimaryKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->checkTable()) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('user_id', 'user_dealer_id');
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
        if ($this->checkTable()) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->renameColumn('user_dealer_id', 'user_id');
            });
        }
    }

    /**
     * Validate Table existence
     * @return bool
     */
    private function checkTable(): bool
    {
        return Schema::hasTable('subscriptions');
    }
}
