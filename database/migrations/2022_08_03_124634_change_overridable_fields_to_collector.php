<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOverridableFieldsToCollector extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            if ($this->checkColumn()) {
                $table->text('overridable_fields')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            if ($this->checkColumn()) {
                $table->string('overridable_fields', 254)->change();
            }
        });
    }

    /**
     * Validate column existence on migrate
     * @return bool
     */
    private function checkColumn(): bool
    {
        return Schema::hasColumn('collector', 'overridable_fields');
    }
}
