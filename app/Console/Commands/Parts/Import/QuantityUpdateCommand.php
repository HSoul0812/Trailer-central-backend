<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Traits\StreamCSVTrait;
use App\Services\Parts\PartServiceInterface;
use App\Models\Parts\Part;
use App\Models\Parts\Bin;

/**
 * Takes a CSV file with 2 columns:
 * barcode quantity
 * 
 * And updates the parts quantity based on what is on the file
 *
 * @author Eczek
 */
class QuantityUpdateCommand extends Command 
{
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "parts:import:quantities {s3-bucket} {s3-key} {dealerId} {binId}";
    
    /**     
     * @var int
     */
    private $dealerId;
    
    /**
     *
     * @var int
     */
    private $binId;
    
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
        $this->binId = $this->argument('binId');
        
        $this->info("Starting to process CSV file");
        
        $parts = Part::where('dealer_id', $this->dealerId)->get();
        
        $this->info("Setting Parts to 0");
        foreach($parts as $part) {
            $bin = $part->bins()->where('bin_id', $this->binId)->first();
            
            $partService->update($part->toArray(), [
                [
                   'quantity' => 0,
                   'bin_id' => $this->binId,
                   'old_quantity' => $bin ? $bin->qty : 0
                ]                
            ]);    
        }
        
        $this->info("Done setting parts to 0");
        
        $this->streamCsv(function($csvData, $lineNumber) use ($partService) { 
            if ($lineNumber === 1) {
                return;
            }
            
            list($partId, $quantity) = $csvData;
            $part = Part::findOrFail($partId);
            $bin = $part->bins()->where('bin_id', $this->binId)->first();
            
            $partService->update($part->toArray(), [
                [
                   'quantity' => $quantity,
                   'bin_id' => $this->binId,
                   'old_quantity' => $bin ? $bin->qty : 0
                ]                
            ]);             
            
            $this->info("Part {$partId} updated");
            
        });    
        $this->info("CSV file processed");
    }
    
}
