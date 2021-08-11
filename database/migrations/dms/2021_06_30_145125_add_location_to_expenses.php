<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationToExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('qb_expenses', function (Blueprint $table) {
            $table->integer('dealer_location_id')
                ->unsigned()
                ->after('dealer_id')
                ->nullable()
                ->comment('being used after #PRTBND-964');

            $table->foreign('dealer_location_id','qb_expenses_dealer_location_id_foreign')
                ->references('dealer_location_id')
                ->on('dealer_location')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('qb_expenses', function (Blueprint $table) {
            $table->dropForeign('qb_expenses_dealer_location_id_foreign');
            $table->dropColumn('dealer_location_id');
        });
    }
}
