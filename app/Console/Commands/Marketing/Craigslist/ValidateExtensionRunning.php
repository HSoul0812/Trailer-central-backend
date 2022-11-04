<?php

namespace App\Console\Commands\Marketing\Facebook;

use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Class ImportMarketplaceListingsForDealer
 * 
 * @package App\Console\Commands\Marketing\Facebook
 */
class ImportMarketplaceListingsForDealer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:craigslist:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Craigslist Extension is still active.';

    /**
     * Create a new command instance.
     *
     * @param MarketplaceRepositoryInterface $repo
     * @return void
     */
    public function __construct(MarketplaceRepositoryInterface $repo)
    {
        parent::__construct();

        $this->repo = $repo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $dealerId = $this->argument('dealer');

        // Get Marketplace Accounts
        $integrations = $this->repo->getAll(['dealer_id' => $dealerId]);

        // Get Inventory Chunked
        DB::table('inventory')
            ->select('inventory.inventory_id')
            ->where('dealer_id', '=', $dealerId)
            ->where('is_archived', '=', 0)
            ->where('status', '<>', 2)
            ->where('status', '<>', 6)
            ->where('show_on_website', '=', 1)
            ->orderBy('created_at', 'ASC')
            ->chunk(500, function (Collection $inventory) use($integrations) {
                $inventoryListing = [];
                $countOfInventory = 0;

                foreach ($inventory as $inventory) {
                    foreach ($integrations as $integration) {
                        $inventoryListing[] = [
                            'marketplace_id' => $integration->id,
                            'inventory_id' => $inventory->inventory_id,
                            'facebook_id' => 0,
                            'account_type' => 'user',
                            'page_id' => 0,
                            'username' => $integration->fb_username,
                            'status' => 'active',
                            'created_at' => Carbon::now()->toDateTimeString(),
                            'updated_at' => Carbon::now()->toDateTimeString()
                        ];

                        $countOfInventory++;
                    }
                }

                DB::table('fbapp_listings')->insert($inventoryListing);

                $this->info("{$countOfInventory} marketplace listings have been inserted");
            });
    }
}
