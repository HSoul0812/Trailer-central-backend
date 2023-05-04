<?php

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use Illuminate\Database\Migrations\Migration;

class CacheCrawlerIpAddresses extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Artisan::call(CacheCrawlerIpAddressesCommand::class);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
