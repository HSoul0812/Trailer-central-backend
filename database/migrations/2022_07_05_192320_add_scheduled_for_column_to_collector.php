<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledForColumnToCollector extends Migration
{
    private $table = 'collector';
    private $column = 'scheduled_for';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dateTime($this->column)->nullable();
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
        if (Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->removeColumn($this->column);
            });
        }
    }
}
