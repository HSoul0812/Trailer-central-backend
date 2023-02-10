<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixGroupLengthBatchedJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('batched_job', static function (Blueprint $table) {
            $table->string('group', 150)->change();
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
            $table->string('group', 38)->change();
        });
    }
}
