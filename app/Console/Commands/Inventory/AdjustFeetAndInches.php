<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;

class AdjustFeetAndInches extends Command {
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:adjust-feet-inches";
    
    /**     
     * @var App\Repositories\Inventory\InventoryRepository
     */
    protected $inventoryRepository;
    
    public function __construct(InventoryRepositoryInterface $inventoryRepo)
    {
        parent::__construct();

        $this->inventoryRepository = $inventoryRepo;
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $inventory = $this->inventoryRepository->getAll([], true);
         
        foreach($inventory as $item) {
            
            if ($item->length && empty($item->length_inches)) {
                $item->length_inches = round($item->length * 12, 0);
            } else if ($item->length_inches) {
                $item->length = round($item->length_inches / 12, 0);
            }
            
            if ($item->width && empty($item->width_inches)) {                
                $item->width_inches = round($item->width * 12, 0);
            } else if ($item->width_inches) {                
                $item->width = round($item->width_inches / 12, 2);
            } 
            
            if ($item->height && empty($item->height_inches)) {
                $item->height_inches = round($item->height * 12, 0);
            } else if ($item->height_inches) {
                $item->height = round($item->height_inches /12, 0);
            }
            
            echo "Stock: {$item->stock}" . PHP_EOL . 
                 "Height: {$item->height}" . PHP_EOL .
                 "Height Inches: {$item->height_inches}" . PHP_EOL .
                 "Width: {$item->width}" . PHP_EOL .
                 "Width Inches: {$item->width_inches}" . PHP_EOL .
                 "Length: {$item->length}" . PHP_EOL .
                 "Length Inches: {$item->length_inches}" . PHP_EOL;
                 
            $item->save();
        }
    }
    
}
