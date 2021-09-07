<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSyncProcessesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_processes', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->string('name', 20)->index('sync_process_i_name');
            $table->enum('status', ['working', 'finished', 'failed'])
                ->default('working')
                ->index('sync_process_i_status');
            $table->jsonb('meta')->default('{}');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->timestamp('finished_at')->nullable();

            $table->index(['name', 'finished_at'], 'sync_process_i_name_finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_processes');
    }
}
