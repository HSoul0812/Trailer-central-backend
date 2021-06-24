<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Services\Parts\PartServiceInterface;
use App\Traits\S3\StreamTrait;

class NTPStagCommand extends Command
{
    const MISC_PART_TYPE_ID = 27;
    const OTHER_BRAND_ID = 144;
    const OTHER_CATEGORY_ID = 901;
    
    use StreamTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "parts:import:ntpstag {s3-bucket} {s3-key} {dealerId}";
    
    /**     
     * @var int
     */
    protected $dealerId;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PartServiceInterface $partService)
    { 
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->s3Key = $this->argument('s3-key');   
        $this->dealerId = $this->argument('dealerId');
        
        $this->stream(function($stream, $lineNumber) use ($partService) {
            $data = fgets($stream);
            $sku = trim(substr($data, 0, 7));
            $description = trim(substr($data, 8, 25));
            $vendorProductNumber = trim(substr($data, 33, 24));
            $suggestedRetail = (float)substr($data, 58, 7) * 0.01;
            $customerCost = (float)substr($data, 65, 7) * 0.01;
            
            try {
                $part = $partService->create([
                    'sku' => $sku,
                    'title' => substr($description, 0, 50),
                    'description' => $description,
                    'alternative_part_number' => $vendorProductNumber,
                    'price' => $suggestedRetail,
                    'dealer_cost' => $customerCost,
                    'latest_cost' => $customerCost,
                    'dealer_id' => $this->dealerId,
                    'type_id' => self::MISC_PART_TYPE_ID,
                    'brand_id' => self::OTHER_BRAND_ID,
                    'category_id' => self::OTHER_CATEGORY_ID
                ], []);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
                return;
            }            
            
            $this->info("Part with SKU {$part->sku} created");
        });
    }
}
