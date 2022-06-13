<?php

namespace App\Services\Import\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Services\Import\Parts\CsvImportServiceInterface;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Models\Parts\Vendor;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Type;
use App\Models\Parts\Part;
use App\Models\Parts\Bin;
use App\Models\Bulk\Parts\BulkUpload;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 *
 *
 * @author Eczek
 */
class CsvImportService implements CsvImportServiceInterface
{
    
    const MAX_VALIDATION_ERROR_CHAR_COUNT = 3072;    
    
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
    const STOCK_MIN = 'Stock Minimum';
    const STOCK_MAX = 'Stock Maximum';
    const VIDEO_EMBED_CODE = 'Video Embed Code';
    const ALTERNATE_PART_NUMBER = 'Alternate Part Number';
    const PART_ID = 'Part ID';

    const BIN_ID = '/Bin\s+\d+\s+ID/i';
    const BIN_QTY = '/Bin\s+\d+\s+qty/i';
    
    private const S3_VALIDATION_ERRORS_PATH = 'parts/validation-errors/%s';

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
        self::STOCK_MIN => true,
        self::STOCK_MAX => true,
        self::VIDEO_EMBED_CODE => true,
        self::ALTERNATE_PART_NUMBER => true,
        self::PART_ID => true
    ];

    protected $optionalHeaderValues = [
        self::BIN_ID => true,
        self::BIN_QTY => true
    ];

    /**
     * @var array Array of validation errors per row
     */
    private $validationErrors = [];

    /**
     * @var array Array of CSV headers. Of the form `array[index] = value` where index is the position and value is the header
     */
    private $indexToheaderMapping = [];

    public function __construct(BulkUploadRepositoryInterface $bulkUploadRepository, PartRepositoryInterface $partRepository, BinRepositoryInterface $binRepository)
    {
        $this->bulkUploadRepository = $bulkUploadRepository;
        $this->partsRepository = $partRepository;
        $this->binRepository = $binRepository;
    }

    public function run(): bool
    {
        echo "Running...".PHP_EOL;
        Log::info('Starting import for bulk upload ID: ' . $this->bulkUpload->id);
        echo "Validating...".PHP_EOL;
        try {
           if (!$this->validate()) {
                Log::info('Invalid bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
                $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => $this->outputValidationErrors()]);
                return false;
            }
        } catch (\Exception $ex) {
            $this->validationErrors[] = $ex->getMessage();

            Log::info('Invalid bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => $this->outputValidationErrors()]);
            return false;
        }

        if (empty($this->validationErrors)) {
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::COMPLETE]);
        } else {
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::COMPLETE, 'validation_errors' => json_encode($this->validationErrors)]);
        }

        Log::info('Validation passed for bulk upload ID: ' . $this->bulkUpload->id . ' import success...');
        return true;
    }

    public function setBulkUpload(BulkUpload $bulkUpload)
    {
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * Execute the import process
     *
     * @throws \Exception
     */
    protected function import($csvData, $lineNumber)
    {
        echo "Importing.... 123".PHP_EOL;

        if ($lineNumber === 1) {
            return;
        }

        //echo 'Importing bulk uploaded part on bulk upload : ' . $this->bulkUpload->id . ' with data ' . json_encode($csvData).PHP_EOL;
        Log::info('Importing bulk uploaded part on bulk upload : ' . $this->bulkUpload->id . ' with data ' . json_encode($csvData));

        try {
            // Get Part Data
            $partData = $this->csvToPartData($csvData);

            echo "Importing Stock: ".$partData['sku'].PHP_EOL;
            $part = $this->partsRepository->createOrUpdate($partData);
            if (!$part) {
                $this->validationErrors[] = "Image inaccesible";
                $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
                //Log::info('Error found on part for bulk upload : ' . $this->bulkUpload->id . ' : ' . $ex->getTraceAsString());
                throw new \Exception("Image inaccesible");
            }

            event(new PartQtyUpdated($part, null, [
                'description' => 'Created/updated using bulk uploader'
            ]));

        } catch (\Exception $ex) {
            $this->validationErrors[] = $ex->getTraceAsString();
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
            Log::info('Error found on part for bulk upload : ' . $this->bulkUpload->id . ' : ' . $ex->getTraceAsString() . json_encode($this->validationErrors));
            Log::info("Index to header mapping: {$this->indexToheaderMapping}");
            //throw new \Exception("Image inaccesible");
        }

    }

    /**
     * Validate the csv file, its headers and content
     *
     * @return bool
     * @throws \Exception
     */
    protected function validate(): bool
    {
        $this->streamCsv(function($csvData, $lineNumber) {
            // for each column
            foreach($csvData as $index => $value) {
                // for the header line
                if ($lineNumber === 1) {
                    // if column header is optional
                    if($this->isOptionalHeader($value)) {
                        $this->allowedHeaderValues[$value] = 'allowed';
                        $this->indexToheaderMapping[$index] = $value;

                    // if column header is not in default $this->allowedHeaderValues
                    } elseif (!$this->isAllowedHeader($value)) {
                        $this->validationErrors[] = $this->printError($lineNumber, $index + 1, "Invalid Header: ".$value);

                    // else, the column header is allowed
                    } else {
                        $this->allowedHeaderValues[$value] = 'allowed';
                        $this->indexToheaderMapping[$index] = $value;
                    }

                // for lines > 1
                } else {
                    if ($errorMessage = $this->isDataInvalid($this->indexToheaderMapping[$index], $value)) {
                        $this->validationErrors[] = $this->printError($lineNumber, $index + 1, $errorMessage);
                    }
                }
            }

            // validate allowed headers
            foreach($this->allowedHeaderValues as $header => $headerValue) {
                if ($headerValue !== 'allowed') {
                    $this->validationErrors[] = $this->printError(1, $header, "Header ".$header." not present.");
                }
            }

            // if there's an return false, if not, import part
            if (count($this->validationErrors) > 0) {
                return false;
            } else {
                $this->import($csvData, $lineNumber);
            }
        });

        return true;
    }

    /**
     * Applies `$callback($row, $lineNumber)` to each line of the csv
     *
     * @param $callback
     * @throws \Exception
     */
    private function streamCsv($callback)
    {
        $adapter = Storage::disk('s3')->getAdapter();
        $client = $adapter->getClient();
        $client->registerStreamWrapper();

        // open the file from s3
        if (!($stream = fopen("s3://{$adapter->getBucket()}/{$this->bulkUpload->import_source}", 'r'))) {
            throw new \Exception('Could not open stream for reading file: ['.$this->bulkUpload->import_source.']');
        }

        // iterate through all lines
        $lineNumber = 1;
        while (!feof($stream)) {
            $isEmptyRow = true;
            $csvData = fgetcsv($stream);

            // see if the row is empty,
            // one value is enough to indicate non empty row
            foreach($csvData as $value) {
                if (!empty($value)) {
                    $isEmptyRow = false;
                    break;
                }
            }

            // skip empty rows
            if ($isEmptyRow) {
                continue;
            }

            // apply $callback to the row
            $callback($csvData, $lineNumber++);
            flush();
        }
    }

    private function isAllowedHeader($val): bool
    {
        return isset($this->allowedHeaderValues[$val]);
    }

    private function isOptionalHeader($val): bool
    {
        // In Optional Headers?
        if(isset($this->optionalHeaderValues[$val])) {
            return true;
        }

        // Loop Optional Headers
        foreach($this->optionalHeaderValues as $regex => $allowed) {
            // Pattern is Regex?
            if(@preg_match($regex, null) !== false){
                if(preg_match($regex, $val)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function printError($line, $column, $errorMessage): string
    {
        return "$errorMessage in line $line at column $column";
    }

    private function csvToPartData($csvData): array
    {
        $formattedImages = [];

        // $keyToIndexMapping[header] basically points to nth column given a column header name
        $keyToIndexMapping = [];
        foreach($this->indexToheaderMapping as $index => $value) {
            $keyToIndexMapping[$value] = $index;
        }

        $vendor = Vendor::where('name', $csvData[$keyToIndexMapping[self::VENDOR]])
            ->where('dealer_id', $this->bulkUpload->dealer_id)
            ->first();

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
        $part['description'] = $this->sanitizeValue($csvData[$keyToIndexMapping[self::DESCRIPTION]]);
        $part['show_on_website'] = strtolower($csvData[$keyToIndexMapping[self::SHOW_ON_WEBSITE]]) === 'yes';
        $part['title'] = $this->sanitizeValue($csvData[$keyToIndexMapping[self::TITLE]]);
        $part['stock_min'] = $csvData[$keyToIndexMapping[self::STOCK_MIN]] ?? null;
        $part['stock_max'] = $csvData[$keyToIndexMapping[self::STOCK_MAX]] ?? null;
        $part['alternative_part_number'] = $csvData[$keyToIndexMapping[self::ALTERNATE_PART_NUMBER]] ?? null;

        if (isset($keyToIndexMapping[self::PART_ID]) && isset($csvData[$keyToIndexMapping[self::PART_ID]])) {
            $part['id'] = $csvData[$keyToIndexMapping[self::PART_ID]];
        }
        
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
     * Sanitize string Value
     *
     * @param string $value
     * @return bool|string
     */
    private function sanitizeValue(string $value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING, array('flags' => FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_HIGH));;
    }

    /**
     * Returns true if valid or error message if invalid
     *
     * @param string $type
     * @param string $value
     * @return bool|string
     */
    private function isDataInvalid(string $type, string $value)
    {
        switch($type) {
            case self::VENDOR:
                if (!empty($value)) {
                    $vendor = Vendor::where('name', $value)->where('dealer_id', $this->bulkUpload->dealer_id)->first();
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
                break;
            case self::ALTERNATE_PART_NUMBER:
                break;
            case self::PART_ID:
                if (!empty($value)) {
                    $part = Part::where('id', $value)->where('dealer_id', $this->bulkUpload->dealer_id)->first();
                    if (empty($part)) {
                        return "Part ID {$value} does not exist in the system.";
                    }
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
            case self::STOCK_MIN:
            case self::STOCK_MAX:
                if (!empty($value)) {
                    if (!is_numeric($value) || $value < 0) {
                        return "Stock Min/Max should be a positive number";
                    }
                }
                break;
        }
        
        return false;
    }
    
    private function outputValidationErrors()
    {
        $jsonEncodedValidationErrors = json_encode($this->validationErrors);
        if (strlen($jsonEncodedValidationErrors) > self::MAX_VALIDATION_ERROR_CHAR_COUNT) {
            $filePath = sprintf(self::S3_VALIDATION_ERRORS_PATH, uniqid().'.txt');
            Storage::disk('s3')->put($filePath, implode(PHP_EOL, $this->validationErrors));
            return json_encode(Storage::disk('s3')->url($filePath));
        }
        return $jsonEncodedValidationErrors;
    }
    
    
}
