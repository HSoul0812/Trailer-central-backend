<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Traits\StreamCSVTrait;
use Illuminate\Support\Facades\Log;

class ShowroomCsvImport extends Command {
    
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "showroom:import {s3-bucket} {s3-key}";
    
    /**
     * @var App\Repositories\Showroom\ShowroomRepositoryInterface
     */
    private $showroomRepo;
    
    /**    
     * @var array
     */
    private $columnToHeaderMapping = [];
        
    public function __construct() {
        $this->showroomRepo = app(ShowroomRepositoryInterface::class);        
        parent::__construct();        
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->s3Key = $this->argument('s3-key');   
        
        $this->streamCsv(function($csvData, $lineNumber) {
            if ($lineNumber === 1) {
                $this->columnToHeaderMapping = $csvData;
                return;
            }            
            
            $showroomData = $this->mapShowroomValueToKey($csvData);
            $showroomData['images'] = $this->convertAssetToUrlArray($showroomData['images']);
            $showroomData['files'] = $this->convertAssetToUrlArray($showroomData['pdfs']);

            if (empty($showroomData['msrp'])) {
                unset($showroomData['msrp']);
            } else {
                $showroomData['msrp'] = $this->priceToFloat($showroomData['msrp']);
            }
            
            if (!empty($showroomData['floorplan'])) {
                $showroomData['floorplan'] = $showroomData['floorplan'];
                if (!in_array($showroomData['floorplan'], $showroomData['images'])) {
                    $showroomData['images'][] = $showroomData['floorplan'];    
                }
            }
            
            if (!empty($showroomData['options'])) {
                $showroomData['description'] .= '#### Options' . PHP_EOL . PHP_EOL;
                $showroomData['description'] .= $showroomData['options'];
                unset($showroomData['options']);
            }
            
            $showroomData['model_merge'] = "AUTO-IMPORTED";
            
            try {
                die(var_dump($this->showroomRepo->create($showroomData)));
            } catch (\Exception $ex) {
                Log::info("showroom-imports: issue importing line number {$lineNumber}");
            }            
           
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
    private function mapShowroomValueToKey($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$this->columnToHeaderMapping[$key]] = $value;
        }
        return $result;
    }
    
    private function convertAssetToUrlArray($assets)
    {
        if (empty($assets)) {
            return [];
        }
        
        $assets = explode(',', $assets); 
        $urlAssets = [];

        foreach($assets as $asset) {
            $asset = trim($asset);

            if (empty($asset)) {
                continue;
            }

            $urlAssets[] = $asset;                
        }

        $assets = $urlAssets;    
        
        return $assets;
    }
    
    private function priceToFloat($s)
    {
        $s = str_replace(',', '.', $s);
        $s = preg_replace("/[^0-9\.]/", "", $s);
        $s = str_replace('.', '',substr($s, 0, -3)) . substr($s, -3);

        return (float) $s;
    }
}
