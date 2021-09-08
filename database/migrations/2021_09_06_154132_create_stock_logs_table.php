<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('record_id')->unsigned()->index('stock_logs_i_record_id');
            $table->enum('event', ['created', 'updated', 'price-changed'])
                ->default('created')
                ->index('stock_logs_i_event');
            $table->enum('status', ['available', 'sold'])->index('stock_logs_i_status');
            $table->string('vin', 40)->nullable()->index('stock_logs_i_vin');
            $table->string('brand', 80)->nullable()->index('stock_logs_i_brand');
            $table->string('manufacturer', 80)->index('stock_logs_i_manufacturer');
            $table->decimal('price', 10)->unsigned();

            $table->jsonb('meta')->default('{}');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));

            $table->index(['event', 'manufacturer', 'brand', 'created_at'], 'stock_logs_i_default_lookup');
        });

        DB::statement('CREATE INDEX stock_logs_i_update_lookup ON stock_logs (created_at DESC)');
        DB::statement('CREATE INDEX stock_logs_igin_vin ON stock_logs USING gin (vin gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
}
