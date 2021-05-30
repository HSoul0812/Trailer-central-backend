<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmPaymentLaborsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('qb_payment_labors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('dealer_cost', 10, 2);
            $table->string('labor_code')->nullable();
            $table->string('status');
            $table->string('cause');
            $table->decimal('actual_hours', 6, 2);
            $table->decimal('paid_hours', 6, 2);
            $table->decimal('billed_hours', 6, 2);
            $table->string('technician');
            $table->string('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('qb_payment_labors');
    }
}
