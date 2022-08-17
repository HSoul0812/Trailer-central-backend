<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RunPermissionSeeders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeders = [
            ['--class' => CreateLumenRolesSeeder::class],
            ['--class' => CreateDefaultPermissionUsersSeeder::class]
        ];

        foreach ($seeders as $seed) {
            \Artisan::call('db:seed', $seed);
        }
    }
}
