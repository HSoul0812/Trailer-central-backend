<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\Common\BatchedJobService;

class AddWaitTimeJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('batched_job', static function (Blueprint $table) {
            $table->smallInteger('wait_time')
                ->after('group')
                ->unsigned()
                ->default(BatchedJobService::WAIT_TIME_IN_SECONDS);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('batched_job', static function (Blueprint $table) {
            $table->dropColumn('wait_time');
        });
    }
}
