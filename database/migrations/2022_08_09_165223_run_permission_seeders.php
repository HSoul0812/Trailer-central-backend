<?php

use Illuminate\Database\Migrations\Migration;

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
            // Need to add this otherwise the migration will stuck
            // on the production environment
            $seed['--force'] = true;

            \Artisan::call('db:seed', $seed);
        }
    }
}
