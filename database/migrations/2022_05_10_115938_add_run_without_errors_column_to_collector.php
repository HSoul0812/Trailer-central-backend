<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRunWithoutErrorsColumnToCollector extends Migration
{

    private $table = 'collector';
    private $column = 'run_without_errors';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $tb) {
                $tb->boolean($this->column)->default(1);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $tb) {
                $tb->removeColumn($this->column);
            });
        };
    }
}
