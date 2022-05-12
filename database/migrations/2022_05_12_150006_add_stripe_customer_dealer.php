<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeCustomerDealer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add stripe columns to dealer table
        Schema::table('dealer', function (Blueprint $table) {
            $table->string('card_brand')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from dealer table
        $this->dropTableColumns('dealer', ['card_brand', 'card_last_four', 'trial_ends_at']);
    }

    /**
     * Validate table existence and delete columns if it has them
     *
     * @return void
     */
    function dropTableColumns($table, $columns)
    {
        if (Schema::hasTable($table)) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    Schema::table($table, function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
}
