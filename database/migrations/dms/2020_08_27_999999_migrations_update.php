<?php

use Illuminate\Database\Migrations\Migration;

class MigrationsUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new UpdateMigrations20200827();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // not possible to undo
    }
}
