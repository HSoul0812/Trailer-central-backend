<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDealerEmployeeAllowNullableFields extends Migration
{
    private $tableName = 'dealer_employee';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('first_name', 50)->nullable()->default(null)->change();
            $table->string('last_name', 50)->nullable()->default(null)->change();
            $table->string('display_name', 255)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('first_name', 50)->nullable(false)->change();
            $table->string('last_name', 50)->nullable(false)->change();
            $table->string('display_name', 255)->nullable(false)->change();
        });
    }
}
