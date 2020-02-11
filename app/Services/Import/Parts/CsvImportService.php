<?php

namespace App\Services\Import\Parts;

use App\Services\Import\Parts\CsvImportServiceInterface;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;
use App\Repositories\Bulk\BinRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Models\Parts\Vendor;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Type;
use App\Models\Parts\Part;
use App\Models\Parts\Bin;
use App\Models\Bulk\Parts\BulkUpload;
use App\Repositories\Parts\PartRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * 
 *
 * @author Eczek
 */
class CsvImportService implements CsvImportServiceInterface 
{
    
    const VENDOR = 'Vendor';
    const BRAND = 'Brand';
    const TYPE = 'Type';
    const CATEGORY = 'Category';
    const SUBCATEGORY = 'Subcategory';
    const TITLE = 'Title';
    const SKU = 'SKU';
    const PRICE = 'Price';
    const DEALER_COST = 'Dealer Cost';
    const MSRP = 'MSRP';
    const WEIGHT = 'Weight';
    const WEIGHT_RATING = 'Weight Rating';
    const DESCRIPTION = 'Description';
    const SHOW_ON_WEBSITE = 'Show on website';
    const IMAGE = 'Image';
    const VIDEO_EMBED_CODE = 'Video Embed Code';
    const BIN_ID = '/Bin \d+ ID/';
    const BIN_QTY = '/Bin \d+ qty/';
    const BIN_ID_1 = 'Bin 1 ID';
    const BIN_QTY_1 = 'Bin 1 qty';
    const BIN_ID_2 = 'Bin 2 ID';
    const BIN_QTY_2 = 'Bin 2 qty';
    const BIN_ID_3 = 'Bin 3 ID';
    const BIN_QTY_3 = 'Bin 3 qty';
    const BIN_ID_4 = 'Bin 4 ID';
    const BIN_QTY_4 = 'Bin 4 qty';
    const BIN_ID_5 = 'Bin 5 ID';
    const BIN_QTY_5 = 'Bin 5 qty';
    const BIN_ID_6 = 'Bin 6 ID';
    const BIN_QTY_6 = 'Bin 6 qty';
    const BIN_ID_7 = 'Bin 7 ID';
    const BIN_QTY_7 = 'Bin 7 qty';
    const BIN_ID_8 = 'Bin 8 ID';
    const BIN_QTY_8 = 'Bin 8 qty';
    const BIN_ID_9 = 'Bin 9 ID';
    const BIN_QTY_9 = 'Bin 9 qty';
    const BIN_ID_10 = 'Bin 10 ID';
    const BIN_QTY_10 = 'Bin 10 qty';
    
    protected $bulkUploadRepository;
    protected $partsRepository;
    protected $binRepository;
    protected $bulkUpload;
    
    protected $allowedHeaderValues = [
        self::VENDOR => true,
        self::BRAND => true,
        self::TYPE => true,
        self::CATEGORY => true,
        self::SUBCATEGORY => true,
        self::TITLE => true,
        self::SKU => true,
        self::PRICE => true,
        self::DEALER_COST => true,
        self::MSRP => true,
        self::WEIGHT => true,
        self::WEIGHT_RATING => true,
        self::DESCRIPTION => true,
        self::SHOW_ON_WEBSITE => true,
        self::IMAGE => true,
        self::VIDEO_EMBED_CODE => true,
        self::BIN_ID_1 => true,
        self::BIN_QTY_1 => true,
        self::BIN_ID_2 => true,
        self::BIN_QTY_2 => true,
        self::BIN_ID_3 => true,
        self::BIN_QTY_3 => true,
        self::BIN_ID_4 => true,
        self::BIN_QTY_4 => true,
        self::BIN_ID_5 => true,
        self::BIN_QTY_5 => true,
        self::BIN_ID_6 => true,
        self::BIN_QTY_6 => true,
        self::BIN_ID_7 => true,
        self::BIN_QTY_7 => true,
        self::BIN_ID_8 => true,
        self::BIN_QTY_8 => true,
        self::BIN_ID_9 => true,
        self::BIN_QTY_9 => true,
        self::BIN_ID_10 => true,
        self::BIN_QTY_10 => true
    ];

    private $validationErrors = [];
        
    private $indexToheaderMapping = [];
    
    public function __construct(BulkUploadRepositoryInterface $bulkUploadRepository, PartRepositoryInterface $partRepository, BinRepositoryInterface $binRepository)
    {
        $this->bulkUploadRepository = $bulkUploadRepository;
        $this->partsRepository = $partRepository;
        $this->binRepository = $binRepository;
    }
    
    public function run() 
    {
        echo "Running...".PHP_EOL;
        Log::info('Starting import for bulk upload ID: ' . $this->bulkUpload->id);
        echo "Validating...".PHP_EOL;
        try {
           if (!$this->validate()) {
                Log::info('Invalid bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
                $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
                return false;
            } 
        } catch (\Exception $ex) {
             Log::info('Invalid bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
            return false;
        }
        
        echo "Data Valid... Importing...".PHP_EOL;
        Log::info('Validation passed for bulk upload ID: ' . $this->bulkUpload->id . ' proceeding with import...');
        $this->import();
        return true;
    }
    
    public function setBulkUpload(BulkUpload $bulkUpload)
    {
        $this->bulkUpload = $bulkUpload;
    }
    
    protected function import() 
    {
        echo "Importing.... 123".PHP_EOL;
        $this->streamCsv(function($csvData, $lineNumber) {
            if ($lineNumber === 1) {
                return;
            }

            echo 'Importing bulk uploaded part on bulk upload : ' . $this->bulkUpload->id . ' with data ' . json_encode($csvData).PHP_EOL;
            Log::info('Importing bulk uploaded part on bulk upload : ' . $this->bulkUpload->id . ' with data ' . json_encode($csvData));

            try {
                // Get Part Data
                $partData = $this->csvToPartData($csvData);
                echo "Importing ".json_encode($partData).PHP_EOL;
                $part = $this->partsRepository->create($partData);
                if (!$part) {
                    $this->validationErrors[] = "Image inaccesible";
                    $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
                    Log::info('Error found on part for bulk upload : ' . $this->bulkUpload->id . ' : ' . $ex->getMessage());
                    throw new \Exception("Image inaccesible");
                }
            } catch (\Exception $ex) {
                $this->validationErrors[] = $ex->getMessage();
                $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
                Log::info('Error found on part for bulk upload : ' . $this->bulkUpload->id . ' : ' . $ex->getMessage());
                throw new \Exception("Image inaccesible");
            }
                       
        });            
        
        $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::COMPLETE]);
    }
    
    protected function validate() 
    {
        $this->streamCsv(function($csvData, $lineNumber) {            
            foreach($csvData as $index => $value) {
                if ($lineNumber === 1) {
                    if (!$this->isAllowedHeader($value)) {  
                        $this->validationErrors[] = $this->printError($lineNumber, $index + 1, "Invalid Header: ".$value);
                    } else {
                        $this->allowedHeaderValues[$value] = 'allowed';
                        $this->indexToheaderMapping[$index] = $value;
                    }                
                } else {
                    if ($errorMessage = $this->isDataValid($this->indexToheaderMapping[$index], $value)) {
                        $this->validationErrors[] = $this->printError($lineNumber, $index + 1, $errorMessage);
                    }
                }                    
            }                
        });
        
        foreach($this->allowedHeaderValues as $header => $headerValue) {
            if ($headerValue !== 'allowed') {
                $this->validationErrors[] = $this->printError(1, $header, "Header ".$header." not present.");
            }
        }
        
        if (count($this->validationErrors) > 0) {
            return false;
        }
        
        return true;
    }
    
    private function streamCsv($callback) 
    {
        $adapter = Storage::disk('s3')->getAdapter();
        $client = $adapter->getClient();
        $client->registerStreamWrapper();
        
        if (!($stream = fopen("s3://{$adapter->getBucket()}/{$this->bulkUpload->import_source}", 'r'))) {
            throw new \Exception('Could not open stream for reading file: ['.$this->bulkUpload->import_source.']');
        }
        
        $lineNumber = 1;
        while (!feof($stream)) {
             $isEmptyRow = true;            
             $csvData = fgetcsv($stream);
             
             foreach($csvData as $value) {
                 if (!empty($value)) {
                     $isEmptyRow = false;
                     break;
                 }
             }
             
             if ($isEmptyRow) {
                 continue;
             }
             
             $callback($csvData, $lineNumber++);
             flush();
        }
    }
    
    private function isAllowedHeader($val) 
    {
        return isset($this->allowedHeaderValues[$val]);
    }
    
    private function printError($line, $column, $errorMessage) 
    {
        return "$errorMessage in line $line at column $column";
    }
    
    private function csvToPartData($csvData) 
    {
        $formattedImages = [];
        $keyToIndexMapping = [];
        foreach($this->indexToheaderMapping as $index => $value) {
            $keyToIndexMapping[$value] = $index;
        }        
        
        $vendor = Vendor::where('name', $csvData[$keyToIndexMapping[self::VENDOR]])->first();
        
        $part = [];
        $part['dealer_id'] = $this->bulkUpload->dealer_id;
        $part['vendor_id'] = !empty($vendor) ? $vendor->id : null;
        $part['brand_id'] = Brand::where('name',  $csvData[$keyToIndexMapping[self::BRAND]])->first()->id;
        $part['type_id'] = Type::where('name',  $csvData[$keyToIndexMapping[self::TYPE]])->first()->id;
        $part['category_id'] = Category::where('name',  $csvData[$keyToIndexMapping[self::CATEGORY]])->first()->id;
        $part['subcategory'] = $csvData[$keyToIndexMapping[self::CATEGORY]];
        $part['sku'] = $csvData[$keyToIndexMapping[self::SKU]];
        $part['price'] = empty($csvData[$keyToIndexMapping[self::PRICE]]) ? 0 : $csvData[$keyToIndexMapping[self::PRICE]];
        $part['dealer_cost'] = empty($csvData[$keyToIndexMapping[self::DEALER_COST]]) ? 0 : $csvData[$keyToIndexMapping[self::DEALER_COST]];        
        $part['msrp'] = empty($csvData[$keyToIndexMapping[self::MSRP]]) ? 0 : $csvData[$keyToIndexMapping[self::MSRP]];
        $part['weight'] = $csvData[$keyToIndexMapping[self::WEIGHT]];
        $part['weight_rating'] = $csvData[$keyToIndexMapping[self::WEIGHT_RATING]];
        $part['description'] = $csvData[$keyToIndexMapping[self::DESCRIPTION]];        
        $part['show_on_website'] = strtolower($csvData[$keyToIndexMapping[self::SHOW_ON_WEBSITE]]) === 'yes' ? true : false;
        $part['title'] = $csvData[$keyToIndexMapping[self::TITLE]];
        
        if (isset($keyToIndexMapping[self::VIDEO_EMBED_CODE]) && isset($csvData[$keyToIndexMapping[self::VIDEO_EMBED_CODE]])) {
            $part['video_embed_code'] = $csvData[$keyToIndexMapping[self::VIDEO_EMBED_CODE]];
        }        
        
        if (!empty($csvData[$keyToIndexMapping[self::IMAGE]])) {
            $images = explode(',', $csvData[$keyToIndexMapping[self::IMAGE]]);
            foreach($images as $index => $imageUrl) {
                $formattedImages[] = [
                    'url' => $imageUrl,
                    'position' => $index
                ];
            }
            $part['images'] = $formattedImages;
        }

        // Get Bins
        $part['bins'] = $this->binRepository->getAllBinsCsv($part['dealer_id'], $csvData, $keyToIndexMapping);

        // Return Part Data
        return $part;          
    }
    
    /**
     * Returnst true if valid or error message if invalid
     * 
     * @param string $type
     * @param string $value 
     * @return bool|string
     */
    private function isDataValid($type, $value) 
    {       
        switch($type) {
            case self::VENDOR:
                if (!empty($value)) {
                    $vendor = Vendor::where('name', $value)->first();
                    if (empty($vendor)) {
                        return "Vendor {$value} does not exist in the system.";
                    }
                }
                break;
            case self::BRAND:
                if (empty($value)) {
                    return "Brand cannot be empty.";
                }
                
                $brand = Brand::where('name', $value)->first();
                if (empty($brand)) {
                    return "Brand {$value} does not exist in the system.";
                }
                break;
            case self::TYPE:
                if (empty($value)) {
                    return "Type cannot be empty.";
                }
                
                $type = Type::where('name', $value)->first();
                if (empty($type)) {
                    return "Type {$value} does not exist in the system.";
                }
                break;
            case self::SUBCATEGORY:
                if (empty($value)) {
                    return "Subcategory cannot be empty.";
                }
                break;
            case self::CATEGORY:
                if (empty($value)) {
                    return "Category cannot be empty.";
                }
                
                $category = Category::where('name', $value)->first();
                if (empty($category)) {
                    return "Category {$value} does not exist in the system.";
                }
                break;
            case self::SKU:
                if (empty($value)) {
                    return "SKU cannot be empty.";
                }
                
                $part = Part::where('sku', $value)->where('dealer_id', $this->bulkUpload->dealer_id)->first();
                if (!empty($part)) {
                    return "SKU {$value} already exists in the system.";
                }
                break;
            case self::SHOW_ON_WEBSITE:
                if (!empty($value)) {
                   if (strtolower($value) != 'no' && strtolower($value) != 'yes') {
                        return "Show on website {$value} is not valid. Needs to be yes or no.";
                   } 
                }
                break;
            case self::IMAGE:
                if (!empty($value)) {
                   $imageUrls = explode(',', $value);
                    foreach($imageUrls as $imageUrl) {
                        if (filter_var($imageUrl, FILTER_VALIDATE_URL) === FALSE) {
                            return "Images need to be comma separated and valid URLs";
                        }
                    }    
                }
                break;
            case (preg_match(self::BIN_ID, $type) ? true : false) :
                if (empty($value)) {
                    return "Bin cannot be empty.";
                }
                
                $bin = Bin::where('bin_name', $value)->where('dealer_id', $this->bulkUpload->dealer_id)->first();
                if (empty($bin)) {
                    return "Bin {$value} does not exist in the system.";
                }
                break;
            case (preg_match(self::BIN_QTY, $type) ? true : false) :
                if (empty($value) && !is_numeric($value)) {
                    return "Bin quantity cannot be empty.";
                }
                else if(!is_numeric($value)) {
                    return "Bin quantity must be numeric.";
                }
                break;
        }
    }
}
