<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDmsCustomerAllowNullableFields extends Migration
{
    private $tableName = 'dms_customer';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
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
            $table->string('display_name', 255)->nullable(false)->change();
        });
    }
}
