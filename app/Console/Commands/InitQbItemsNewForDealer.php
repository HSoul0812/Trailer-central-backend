<?php

namespace App\Console\Commands;

use App\Services\Quickbooks;
use Illuminate\Console\Command;

class InitQbItemsNewForDealer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tc:init-qb-items-new {dealerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts initial data to qb_item_new for a given dealer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dealerId = $this->argument('dealerId');
        if (!$dealerId) {
            throw new \Exception("The dealerId argument is required");
        }

        $service = new Quickbooks\InitService();
        if ($service->initQbItemsNewForDealer($dealerId) !== true) {
            print "Could not complete command\n";
        } else {
            print "Done.\n";
        }
    }
}
