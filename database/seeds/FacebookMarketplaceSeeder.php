<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;

class FacebookMarketplaceSeeder extends Seeder
{
    const DEALER_SUCCESS_ID = 1119;
    const DEALER_FAIL_ID = 1120;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(MarketplaceRepositoryInterface $repo)
    {
        

        //insert Dealer Location
        $location1Id = DB::table('dealer_location')->insertGetId([
            'dealer_id' => self::DEALER_SUCCESS_ID,
            'name' => 'location ioan #1'
        ]);
        $location2Id = DB::table('dealer_location')->insertGetId([
            'dealer_id' => self::DEALER_FAIL_ID,
            'name' => 'location ioan #2'
        ]);

        //insert FB Marketplace
        $marketPlace1Id = DB::table('fbapp_marketplace')->insertGetId([
            'dealer_id' => self::DEALER_SUCCESS_ID,
            'dealer_location_id' => $location1Id,
            'page_url' => "httsp://fabebook.com/ioan1",
            'fb_username' => 'ioan1@facebook.com',
            'fb_password' => bcrypt('fakepassword'),
        ]);
        $marketPlace2Id = DB::table('fbapp_marketplace')->insertGetId([
            'dealer_id' => self::DEALER_FAIL_ID,
            'dealer_location_id' => $location2Id,
            'page_url' => "httsp://fabebook.com/ioan2",
            'fb_username' => 'ioan2@facebook.com',
            'fb_password' => bcrypt('fakepassword'),
        ]);

        // create inventory for dealer
        for($i=0;$i<=10;$i++) {
            DB::table('inventory')->insert([
                'dealer_id' => self::DEALER_SUCCESS_ID,
                'dealer_location_id' => $location1Id,
                'title' => 'Title-'.$i,
                'status' => 1,
                'is_archived' => 0,
                'show_on_website' =>1,
                'created_at' => Carbon::now()->toDateTimeString(),
                'stock' => "Stock".rand(1,9)
            ]);
        }

        $failedInvetoryId = DB::table('inventory')->insertGetId([
            'dealer_id' => self::DEALER_FAIL_ID,
            'dealer_location_id' => $location2Id,
            'title' => 'Title',
            'status' => 1,
            'is_archived' => 0,
            'show_on_website' =>1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'stock' => "Stock".rand(1,9)
        ]);

        
        $integrations = $repo->getAll(['dealer_id' => self::DEALER_SUCCESS_ID]);
      
        DB::table('inventory')
        ->select('inventory.inventory_id')
        ->where('dealer_id', '=', self::DEALER_SUCCESS_ID)
        ->where('is_archived', '=', 0)
        ->where('status', '<>', 2)
        ->where('status', '<>', 6)
        ->where('show_on_website', '=', 1)
        ->orderBy('created_at', 'ASC')
        ->chunk(5, function (Collection $inventory) use($integrations) {
            $inventoryListing = [];
            $countOfInventory = 0;

            foreach ($inventory as $inventory) {
                foreach ($integrations as $integration) {
                    $inventoryListing[] = [
                        'marketplace_id' => $integration->id,
                        'inventory_id' => $inventory->inventory_id,
                        'facebook_id' => $this->bigRandomNumber(0, 999999999),
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
        });

        //insert FB_errors
        DB::table('fbapp_errors')->insertGetId([
            'marketplace_id' => $marketPlace2Id,
            'inventory_id' => $failedInvetoryId,
            'action' => 'action',
            'step' => 'login',
            'error_type' => 1,
            'error_message' => 'Incorrent credentials',
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addMonth()->toDateTimeString()
        ]);


    }

    private function bigRandomNumber($min, $max) {
        $difference   = bcadd(bcsub($max,$min),1);
        $rand_percent = bcdiv(mt_rand(), mt_getrandmax(), 8); // 0 - 1.0
        return bcadd($min, bcmul($difference, $rand_percent, 8), 0);
      }
}
