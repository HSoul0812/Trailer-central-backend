<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Console\Application as Artisan;

class GenerateDealerCrmUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('user:generate-crm-users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
