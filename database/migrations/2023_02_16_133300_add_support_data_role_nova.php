<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class addSupportDataRoleNova extends Migration
{

    private const TARGET_TABLE = 'roles';

    private const TARGET_DATA = [
        'name' => 'DataSupport', // add this combination to avoid overlap if Data is a reserved mysql word
        'guard_name' => 'nova',
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        DB::table(self::TARGET_TABLE)->insert(self::TARGET_DATA);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        DB::table(self::TARGET_TABLE)->where(self::TARGET_DATA)->delete();
    }
}
