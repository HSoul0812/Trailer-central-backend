<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLeadLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_logs`', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('trailercentral_id')->unsigned()->index('lead_logs_i_trailercentral_id');
            $table->integer('inventory_id')->unsigned()->index('lead_logs_i_inventory_id');
            $table->string('first_name', 250)->nullable()->index('lead_logs_i_first_name');
            $table->string('last_name', 250)->nullable()->index('lead_logs_i_last_name');
            $table->string('email_address', 250)->nullable()->index('lead_logs_i_email_address');

            $table->jsonb('meta')->default('{}');

            $table->timestamp('submitted_at');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
        });

        DB::statement('CREATE INDEX lead_logs_i_created_lookup ON lead_logs (created_at DESC)');
        DB::statement('CREATE INDEX lead_logs_i_submitted_lookup ON lead_logs (submitted_at DESC)');
        DB::statement('CREATE INDEX inventory_logs_igin_first_name ON lead_logs USING gin (first_name gin_trgm_ops)');
        DB::statement('CREATE INDEX inventory_logs_igin_last_name ON lead_logs USING gin (last_name gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_logs');
    }
}
