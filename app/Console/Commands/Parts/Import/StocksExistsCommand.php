<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Traits\StreamCSVTrait;
use App\Repositories\Parts\PartRepositoryInterface;

/**
 * Description of CompareStocksCommand
 *
 * @author Eczek
 */
class StocksExistsCommand extends Command 
{
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "parts:exist:stock {s3-bucket} {s3-key} {dealerId}";
    
    /**     
     * @var int
     */
    private $dealerId;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PartRepositoryInterface $partRepo)
    { 
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->s3Key = $this->argument('s3-key');   
        $this->dealerId = $this->argument('dealerId');
        
        $this->info("Starting to process CSV file");
        $this->streamCsv(function($csvData, $lineNumber) use ($partRepo) {      
            $sku = current($csvData);
            $part = $partRepo->getDealerSku($this->dealerId, $sku);
            
            if (empty($part)) {
                $this->error("Stock missing: {$sku}");
            }
            
        });    
        $this->info("CSV file processed");
    }
    
}
