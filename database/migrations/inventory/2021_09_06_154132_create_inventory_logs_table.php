<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInventoryLogsTable extends Migration
{
    /**
     * Pitifully Laravel doesn't support multiples schemas for testing, somehow it just fails at BD recreation time,
     * so we will handle this in the MySQL way.
     *
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('trailercentral_id')->unsigned()->index('inventory_logs_i_trailercentral_id');
            $table->enum('event', ['created', 'updated', 'price-changed'])
                ->default('created')
                ->index('inventory_logs_i_event');
            $table->enum('status', ['available', 'sold'])->index('inventory_logs_i_status');
            $table->string('vin', 40)->nullable()->index('inventory_logs_i_vin');
            $table->string('brand', 80)->nullable()->index('inventory_logs_i_brand');
            $table->string('manufacturer', 80)->index('inventory_logs_i_manufacturer');
            $table->decimal('price', 10)->unsigned();

            $table->jsonb('meta')->default('{}');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
        });

        DB::statement('CREATE INDEX inventory_logs_i_default_lookup ON inventory_logs (event ASC, manufacturer ASC, brand ASC, created_at DESC)');
        DB::statement('CREATE INDEX inventory_logs_i_created_lookup ON inventory_logs (created_at DESC)');

        DB::statement('CREATE INDEX inventory_logs_igin_manufacturer ON inventory_logs USING gin (manufacturer gin_trgm_ops)');
        DB::statement('CREATE INDEX inventory_logs_igin_brand ON inventory_logs USING gin (brand gin_trgm_ops)');
        DB::statement('CREATE INDEX inventory_logs_igin_vin ON inventory_logs USING gin (vin gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
}
