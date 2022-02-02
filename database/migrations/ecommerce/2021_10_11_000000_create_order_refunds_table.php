<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ecommerce_order_refunds', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement()->unsigned();
            $table->bigInteger('order_id')->unsigned()->index();
            $table->decimal('amount', 10, 2)->unsigned();
            $table->text('parts')->nullable()->comment('should be a valid json array value');
            $table->enum('reason', ['duplicate', 'fraudulent', 'requested_by_customer'])->nullable()->index();
            $table->string('object_id')->comment('Payment processing (gateway) unique id')->index();
            $table->text('metadata')->comment('Payment processing (gateway) useful json response/error');
            $table->enum('status', ['finished', 'failed', 'recoverable_failure'])->default('finished')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('order_id')
                ->references('id')
                ->on('ecommerce_completed_orders');

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_refunds');
    }
}
