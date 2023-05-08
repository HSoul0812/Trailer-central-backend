<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThirdPartyProviderToCollector extends Migration
{
    private $table = 'collector';
    private $column = 'third_party_provider';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, $this->column)) {
                Schema::table($this->table, function (Blueprint $table) {
                    $table->string($this->column)->nullable();
                });
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
        if (Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn($this->column);
            });
        }
    }
}
