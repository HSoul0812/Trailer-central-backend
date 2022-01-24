<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterQbAccountsAllowNullableFields extends Migration
{
    private $tableName = 'qb_accounts';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE qb_accounts CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE qb_accounts CHANGE name name VARCHAR(255) NOT NULL');
    }
}
