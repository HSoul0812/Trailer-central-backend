<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepairOrderTaxes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('dms_repair_order_taxes', function (Blueprint $table) {
            $table->integer('repair_order_id')->unsigned()->primary();
            $table->enum('source', ['quote', 'default'])->default('default');
            $table->decimal('state', 10, 2);
            $table->decimal('county', 10, 2);
            $table->decimal('local', 10, 2);
            $table->decimal('others', 10, 2);
            $table->timestamps();

            $table->foreign('repair_order_id')
                ->references('id')
                ->on('dms_repair_order')
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
        Schema::dropIfExists('dms_repair_order_taxes');
    }
}
