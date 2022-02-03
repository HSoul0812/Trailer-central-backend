<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class ChangeTotalAmountTypeInOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE ecommerce_completed_orders MODIFY total_amount decimal(10,2) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE ecommerce_completed_orders MODIFY total_amount integer NOT NULL');
    }
}
