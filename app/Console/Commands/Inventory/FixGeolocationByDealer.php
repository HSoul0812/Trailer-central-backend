<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder as GrimzyBuilder;
use App\Repositories\User\GeoLocationRepositoryInterface;
use Grimzy\LaravelMysqlSpatial\Types\Point;

/**
 * Class FixGeolocationByDealer
 * @package App\Console\Commands\Inventory
 */
class FixGeolocationByDealer extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:fix-geolocation-by-dealer {dealer_id?}";
        
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {                      
        $geoLocationRepo = app(GeoLocationRepositoryInterface::class);
        $inventories = $this->getInventoryQuery()->get();

        foreach($inventories as $inventory) {
            $geoLocation = $geoLocationRepo->get(['zip' => $inventory->dealerLocation->postalcode]);
            $inventory->geolocation = new Point($geoLocation->latitude, $geoLocation->longitude);
            $inventory->save();
            $this->info("Saved Inventory: {$inventory->inventory_id}");
        }
        
        return true;
    }
    
    private function getInventoryQuery(): GrimzyBuilder
    {
        $dealerId = $this->argument('dealer_id');
        
        $query = Inventory::where('dealer_id', $dealerId);
        
        return $query;
    }

}
