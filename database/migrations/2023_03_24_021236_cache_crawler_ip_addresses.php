<?php

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use Illuminate\Database\Migrations\Migration;

class CacheCrawlerIpAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call(CacheCrawlerIpAddressesCommand::class);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
