<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\traits\WithMysqlServerVersion;

class AddContextToBatchedJobTable extends Migration
{
    use WithMysqlServerVersion;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {

        Schema::table('batched_job', function (Blueprint $table) {
            $type = $this->version() < '5.7.0' ? 'text' : 'json';

            $table->{$type}('context')
                ->nullable()
                ->after('wait_time')
                ->comment('a valid json data useful to know info about the batch job');
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
            $table->dropColumn('context');
        });
    }
}
