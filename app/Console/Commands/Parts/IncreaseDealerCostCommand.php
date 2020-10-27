<?php

namespace App\Console\Commands\Parts;

use Illuminate\Console\Command;
use App\Repositories\Parts\PartRepositoryInterface;

/**
 * Sets parts price to dealer cost + (dealer cost * {markup})
 *
 * @author Eczek
 */
class IncreaseDealerCostCommand extends Command 
{
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "parts:increase-dealer-cost {dealerId} {markup}";
        
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PartRepositoryInterface $partRepo)
    { 
        $dealerId = $this->argument('dealerId');
        $markup = (float)$this->argument('markup');   
        
        $this->info("Starting to process parts");
        
        $parts = $partRepo->getAllByDealerId($dealerId);
        
        foreach($parts as $part) {
            $costIncrease = $markup * $part->dealer_cost;
            $part->price = (float)number_format($part->dealer_cost + $costIncrease, 2, '.', '');            
            $part->save();
            $this->info("Saved part {$part->sku} to $ {$part->price}");
        }
        
        $this->info("Done...");
    }
    
}
