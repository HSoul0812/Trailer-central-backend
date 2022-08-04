<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;
use App\Services\Inventory\InventoryServiceInterface;
use App\Models\Inventory\Inventory;
use RuntimeException;
use App\Traits\StreamCSVTrait;

class ImportImagesFromCSV extends Command {
    
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:import-images {s3-bucket} {s3-key} {dealerId} {lowerIndex} {upperIndex}";
   
    
    /**
     * @var int 
     */
    private $dealerId;
    
    /**    
     * @var array
     */
    private $columnToHeaderMapping = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->s3Key = $this->argument('s3-key');   
        $this->dealerId = $this->argument('dealerId');
        $lowerIndex = $this->argument('lowerIndex');
        $upperIndex = $this->argument('upperIndex');
        
        $inventoryService = app(InventoryServiceInterface::class);
        
        $this->streamCsv(function($csvData, $lineNumber) use ($inventoryService, $lowerIndex, $upperIndex) {
            if ($lineNumber === 1) {
                $this->columnToHeaderMapping = $csvData;
                return;
            }            
            
            if ($lineNumber < $lowerIndex) {
                return;
            }
            
            if ($lineNumber > $upperIndex) {
                throw new RuntimeException('Processing out of bounds');
            }
            
            $this->info("Starting inventory image import");
            
            $inventoryData = $this->mapInventoryValueToKey($csvData);

            if (empty($inventoryData['images']) || empty($inventoryData['stock #'])) {
                $this->error("Skipping inventory due to empty images or empty stock");
                return;
            }
            
            $stock = $inventoryData['stock #'];
                        
            $images = array_map(function($imageData) {
                return [
                    'url' => $imageData
                ];
            }, explode(',', $inventoryData['images']));
            
            $inventory = Inventory::where('stock', $stock)->where('is_archived', 0)->where('dealer_id', $this->dealerId)->first();
            
            if (empty($inventory)) {
                $this->error("{$stock} inventory is archived or does not exist");
                return;
            }
            
//            if ($inventory->images()->count() > 2) {
//                $this->error("{$inventory->inventory_id} {$stock} already has real images");
//                return;
//            }
            
            $inventory->images()->delete();
            
            try {
                $inventoryService->update([
                    'inventory_id' => $inventory->inventory_id,
                    'overlay_enabled' => false,
                    'title' => $inventory->title,
                    'dealer_id' => $this->dealerId,
                    'new_images' => $images,
                    'has_stock_images' => 0
                ]);
            } catch (\Exception $ex) {
                $this->error("Exception updating images for {$stock}");
                return;
            }
            
            
            $this->info("Uploaded images for {$inventory->inventory_id} {$stock}");
        });        
    }
    
    /**
     * Maps the data in $data based on the array position using $this->columnToHeaderMapping. So basically if
     * , in $this->columnToHeaderMapping, we have: 
     *    0 => "pdfs",
     *    1 => "images",
     *    2 => "description",
     * 
     * And in $data we have:
     *
     *    0 => "randomurl",
     *    1 => "123",
     *    2 => "Great showroom",
     * 
     * Then the result would be an array with key - values:
     * 
     *    "pdfs" => "randomurl",
     *    "images" => "123",
     *    "description" => "Great showroom"
     * 
     * @param array $data
     */
    private function mapInventoryValueToKey($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$this->columnToHeaderMapping[$key]] = $value;
        }
        return $result;
    }
}
