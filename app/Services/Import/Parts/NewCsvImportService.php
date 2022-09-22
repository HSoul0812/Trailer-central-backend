<?php

namespace App\Services\Import\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use DB;
use Exception;
use League\Flysystem\FilesystemInterface;
use Log;
use Storage;

class NewCsvImportService implements CsvImportServiceInterface
{
    const MAX_VALIDATION_ERROR_CHAR_COUNT = 3072;

    const HEADER_VENDOR = 'Vendor';
    const HEADER_BRAND = 'Brand';
    const HEADER_TYPE = 'Type';
    const HEADER_CATEGORY = 'Category';
    const HEADER_SUBCATEGORY = 'Subcategory';
    const HEADER_TITLE = 'Title';
    const HEADER_SKU = 'SKU';
    const HEADER_PRICE = 'Price';
    const HEADER_DEALER_COST = 'Dealer Cost';
    const HEADER_MSRP = 'MSRP';
    const HEADER_WEIGHT = 'Weight';
    const HEADER_WEIGHT_RATING = 'Weight Rating';
    const HEADER_DESCRIPTION = 'Description';
    const HEADER_SHOW_ON_WEBSITE = 'Show on website';
    const HEADER_IMAGE = 'Image';
    const HEADER_STOCK_MIN = 'Stock Minimum';
    const HEADER_STOCK_MAX = 'Stock Maximum';
    const HEADER_VIDEO_EMBED_CODE = 'Video Embed Code';
    const HEADER_ALTERNATE_PART_NUMBER = 'Alternate Part Number';

    const HEADER_RULE_REQUIRED = 'required';
    const HEADER_RULE_OPTIONAL = 'optional';

    const PRIMARY_IDENTIFIER_HEADER = self::HEADER_SKU;

    /**
     * All the valid headers. We use associative array for performance
     * Any columns that's not in this array are ignored
     *
     * @var string[]
     */
    const HEADER_RULES = [
        self::HEADER_VENDOR => self::HEADER_RULE_OPTIONAL,
        self::HEADER_BRAND => self::HEADER_RULE_OPTIONAL,
        self::HEADER_TYPE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_CATEGORY => self::HEADER_RULE_OPTIONAL,
        self::HEADER_SUBCATEGORY => self::HEADER_RULE_REQUIRED,
        self::HEADER_TITLE => self::HEADER_RULE_REQUIRED,
        self::HEADER_SKU => self::HEADER_RULE_REQUIRED,
        self::HEADER_PRICE => self::HEADER_RULE_REQUIRED,
        self::HEADER_DEALER_COST => self::HEADER_RULE_OPTIONAL,
        self::HEADER_MSRP => self::HEADER_RULE_OPTIONAL,
        self::HEADER_WEIGHT => self::HEADER_RULE_OPTIONAL,
        self::HEADER_WEIGHT_RATING => self::HEADER_RULE_OPTIONAL,
        self::HEADER_DESCRIPTION => self::HEADER_RULE_OPTIONAL,
        self::HEADER_SHOW_ON_WEBSITE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_IMAGE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_STOCK_MIN => self::HEADER_RULE_OPTIONAL,
        self::HEADER_STOCK_MAX => self::HEADER_RULE_OPTIONAL,
        self::HEADER_VIDEO_EMBED_CODE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_ALTERNATE_PART_NUMBER => self::HEADER_RULE_OPTIONAL,
    ];

    const MEMORY_CACHE_KEY_VENDORS = 'vendors';
    const MEMORY_CACHE_KEY_BRANDS = 'brands';
    const MEMORY_CACHE_KEY_TYPES = 'types';
    const MEMORY_CACHE_KEY_CATEGORIES = 'categories';

    /** @var BulkUploadRepositoryInterface */
    protected $bulkUploadRepository;

    /** @var PartRepositoryInterface */
    protected $partsRepository;

    /** @var BinRepositoryInterface */
    protected $binRepository;

    /** @var BulkUpload */
    protected $bulkUpload;

    /** @var FilesystemInterface */
    protected $storage;

    /**
     * The headerIndexes variable plays a very important role
     * for this service, it enables a way to map between the
     * data row and the header row, only the header that will
     * be processed will be in this array (as a key of the array)
     *
     * @var array
     */
    protected $headerIndexes = [];

    /**
     * Another integral part of this command, we'll store the
     * database data in here for performance of the service
     *
     * Note: Only store the one with small amount of rows
     *
     * @var array
     */
    protected $memoryCache = [];

    /**
     * While the code is running, we'll store the error in this
     * variable and then dump all of them in the validation_error
     * column before exiting the class
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @param BulkUploadRepositoryInterface $bulkUploadRepository
     * @param PartRepositoryInterface $partsRepository
     * @param BinRepositoryInterface $binRepository
     */
    public function __construct(BulkUploadRepositoryInterface $bulkUploadRepository, PartRepositoryInterface $partsRepository, BinRepositoryInterface $binRepository)
    {
        $this->bulkUploadRepository = $bulkUploadRepository;
        $this->partsRepository = $partsRepository;
        $this->binRepository = $binRepository;
        $this->storage = Storage::disk('s3');
    }

    public function setBulkUpload(BulkUpload $bulkUpload): NewCsvImportService
    {
        $this->bulkUpload = $bulkUpload;

        return $this;
    }

    /**
     * The main method to use this service
     *
     * @return void
     */
    public function run()
    {
        // Temp code
        if (file_exists(storage_path('logs/pond.log'))) {
            unlink(storage_path('logs/pond.log'));
        }

        config(['logging.default' => 'pond']);

        Log::info('Starting import for bulk upload ID: ' . $this->bulkUpload->id);

        // Reason to stop processing
        // 1. One of the required header doesn't exist
        try {
            $this->importCSV();
        } catch (Exception $exception) {

        }
    }

    /**
     * In this method, we set the closure for each line
     * that will be read by the streamCSV() method
     * basically, we just want to process the header if the line number is 1
     * otherwise, we'll process the line as the data line
     * @return void
     * @throws Exception
     */
    private function importCSV()
    {
        $this->streamCsv(function (array $data, int $line) {
            if ($line === 1) {
                $this->processHeaders($data);

                return;
            }

            $this->processData($data, $line);
        });
    }

    /**
     * In this method we read the file from S3 line by line
     * and for each line we send the data to process, one by one
     * also, we're skipping empty row
     *
     * @throws Exception
     */
    private function streamCsv(callable $callback)
    {
        $adapter = $this->storage->getAdapter();
        $adapter->getClient()->registerStreamWrapper();
        $fileURI = sprintf("s3://%s/%s", $adapter->getBucket(), $this->bulkUpload->import_source);

        if (!$stream = fopen($fileURI, 'r')) {
            throw new Exception('Could not open stream for reading file: [' . $this->bulkUpload->import_source . ']');
        }

        $line = 1;
        while (!feof($stream)) {
            $isEmptyRow = true;
            $csvData = fgetcsv($stream);

            // Once found the first value, it's not an empty row anymore
            foreach ($csvData as $value) {
                if (empty($value)) {
                    continue;
                }

                $isEmptyRow = false;
                break;
            }

            if ($isEmptyRow) {
                continue;
            }

            // We trim all the data before passing it back
            $csvData = array_map(function (string $data) {
                return trim($data);
            }, $csvData);

            $callback($csvData, $line++);
            flush();
        }

        fclose($stream);
    }

    /**
     * Process the CSV headers
     * In this method we ensure that all the required headers are in the CSV file
     * we'll also store each header index, so we can use it later on
     *
     * @throws Exception
     */
    private function processHeaders(array $headers)
    {
        $headerAsKeys = array_flip($headers);
        $requiredHeaders = $this->getRequiredHeaders();

        // When we found a required headers, we'll remove them one by one
        // from the $requiredHeaders array, then we can use it later to
        // check if all the required headers are present in the file or not
        foreach ($requiredHeaders as $header => $ignore) {
            if (array_key_exists($header, $headerAsKeys)) {
                unset($requiredHeaders[$header]);
            }
        }

        if (count($requiredHeaders) > 0) {
            $message = sprintf("Missing required headers: %s.", implode(', ', array_keys($requiredHeaders)));
            throw new Exception($message);
        }

        $this->headerIndexes = array_flip($headers);

        foreach ($headers as $header) {
            // If the header doesn't exist in the rule, we unset it from
            // the interested indexes
            if (!array_key_exists($header, self::HEADER_RULES)) {
                unset($this->headerIndexes[$header]);
            }
        }
    }

    /**
     * Get the required header as an array
     * the returned array will have column name as a key
     * just like the HEADER_RULES variable
     *
     * @return array
     */
    private function getRequiredHeaders(): array
    {
        return array_filter(self::HEADER_RULES, function (string $rule) {
            return $rule === self::HEADER_RULE_REQUIRED;
        });
    }

    /**
     * Process the given row data
     *
     * @param array $data
     * @param int $line
     * @return void
     */
    private function processData(array $data, int $line)
    {
        $partData = $this->getPartDataFromCsvData($data, $line);
        $partData['dealer_id'] = $this->bulkUpload->dealer_id;

        Log::info("Importing part SKU " . $partData['sku']);

        $part = $this->partsRepository->createOrUpdate($partData);

        event(new PartQtyUpdated($part, null, [
            'description' => 'Created/updated using bulk uploader'
        ]));
    }

    /**
     * Get the part data from the given row array data
     * for each column, the code will transform the raw value
     * into a proper value before storing it in the part array
     *
     * @param array $data
     * @param int $line
     * @return array
     */
    private function getPartDataFromCsvData(array $data, int $line): array
    {
        $part = [];

        // We only loop through the indexes that we have in the file
        foreach ($this->headerIndexes as $header => $index) {
            // For each index, we process them differently
            $value = $data[$index];

            switch ($header) {
                case self::HEADER_VENDOR:
                    $part['vendor_id'] = $this->getVendorIdByName($value);
                    if (empty($part['vendor_id'])) {
                        $this->errors[] = "Issue with line #$line: cannot find vendor $value in the system.";
                    }
                    break;

                case self::HEADER_BRAND:
                    $part['brand_id'] = $this->getBrandIdByName($value);
                    if (empty($part['brand_id'])) {
                        $this->errors[] = "Issue with line #$line: cannot find brand $value in the system.";
                    }
                    break;

                case self::HEADER_TYPE:
                    $part['type_id'] = $this->getTypeIdByName($value);
                    if (empty($part['type_id'])) {
                        $this->errors[] = "Issue with line #$line: cannot find part type $value in the system.";
                    }
                    break;

                case self::HEADER_CATEGORY:
                    $part['category_id'] = $this->getCategoryIdByName($value);
                    if (empty($part['category_id'])) {
                        $this->errors[] = "Issue with line #$line: cannot find part category $value in the system.";
                    }
                    break;

                case self::HEADER_SUBCATEGORY:
                    $part['subcategory'] = $this->sanitizeValueToString($value);
                    break;

                case self::HEADER_TITLE:
                    $part['title'] = $this->sanitizeValueToString($value);
                    break;

                case self::HEADER_SKU:
                    $part['sku'] = $this->sanitizeValueToString($value);
                    break;

                case self::HEADER_PRICE:
                    $part['price'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_DEALER_COST:
                    $part['dealer_cost'] = $this->sanitizeValueToNumber($value, 0);
                    break;

                case self::HEADER_MSRP:
                    $part['msrp'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_WEIGHT:
                    $part['weight'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_WEIGHT_RATING:
                    $part['weight_rating'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_DESCRIPTION:
                    $part['description'] = $this->sanitizeValueToString($value);
                    break;

                case self::HEADER_SHOW_ON_WEBSITE:
                    $part['show_on_website'] = $this->sanitizeValueToNumber($value, 0);
                    break;

                case self::HEADER_IMAGE:
                    // TODO: Implement the real logic here
                    $part['images'] = null;
                    break;

                case self::HEADER_STOCK_MIN:
                    $part['stock_min'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_STOCK_MAX:
                    $part['stock_max'] = $this->sanitizeValueToNumber($value);
                    break;

                case self::HEADER_VIDEO_EMBED_CODE:
                    $part['video_embed_code'] = $this->sanitizeValueToString($value);
                    break;

                case self::HEADER_ALTERNATE_PART_NUMBER:
                    $part['alternative_part_number'] = $this->sanitizeValueToString($value);
                    break;
            }
        }

        // TODO: Process Bins columns

        return $part;
    }

    /**
     * Get the vendor id by the given vendor name
     * returns null if it doesn't exist in the database
     *
     * @param string|null $name
     * @return int|null
     */
    private function getVendorIdByName(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        if (!array_key_exists(self::MEMORY_CACHE_KEY_VENDORS, $this->memoryCache)) {
            $vendors = DB::table(Vendor::getTableName())
                ->where('dealer_id', $this->bulkUpload->dealer_id)
                ->get(['id', 'name'])
                ->keyBy('name');

            $this->memoryCache[self::MEMORY_CACHE_KEY_VENDORS] = $vendors;
        }

        return optional($this->memoryCache[self::MEMORY_CACHE_KEY_VENDORS]->get($name))->id;
    }

    /**
     * Get the brand id by the given brand name
     * returns null if it doesn't exist in the database
     *
     * @param string|null $name
     * @return int|null
     */
    private function getBrandIdByName(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        if (!array_key_exists(self::MEMORY_CACHE_KEY_BRANDS, $this->memoryCache)) {
            $this->memoryCache[self::MEMORY_CACHE_KEY_BRANDS] = DB::table(Brand::getTableName())->get(['id', 'name'])->keyBy('name');
        }

        return optional($this->memoryCache[self::MEMORY_CACHE_KEY_BRANDS]->get($name))->id;
    }

    /**
     * Get the type id by the given type name
     * returns null if it doesn't exist in the database
     *
     * @param string|null $name
     * @return int|null
     */
    private function getTypeIdByName(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        if (!array_key_exists(self::MEMORY_CACHE_KEY_TYPES, $this->memoryCache)) {
            $this->memoryCache[self::MEMORY_CACHE_KEY_TYPES] = DB::table(Type::getTableName())->get(['id', 'name'])->keyBy('name');
        }

        return optional($this->memoryCache[self::MEMORY_CACHE_KEY_TYPES]->get($name))->id;
    }

    /**
     * Get the category id by the given category name
     * returns null if it doesn't exist in the database
     *
     * @param string|null $name
     * @return int|null
     */
    private function getCategoryIdByName(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        if (!array_key_exists(self::MEMORY_CACHE_KEY_CATEGORIES, $this->memoryCache)) {
            $this->memoryCache[self::MEMORY_CACHE_KEY_CATEGORIES] = DB::table(Category::getTableName())->get(['id', 'name'])->keyBy('name');
        }

        return optional($this->memoryCache[self::MEMORY_CACHE_KEY_CATEGORIES]->get($name))->id;
    }

    /**
     * Sanitize the given value to string, you can provide default value to override null
     *
     * @param string|null $value
     * @param mixed $default
     * @return string|null
     */
    private function sanitizeValueToString(?string $value, $default = null): ?string
    {
        if (empty($value)) {
            return $default;
        }

        // The FILTER_SANITIZE_STRING flag inside the https://www.php.net/manual/en/filter.filters.sanitize.php
        // will be deprecated in PHP 8.1, they suggest that we use the htmlspecialchars() method instead
        // so we're doing it now to save our future time
        return htmlspecialchars($value);
    }

    /**
     * Sanitize the given value to number, you can provide default value to override null
     *
     * @param string|null $value
     * @param mixed $default
     * @return float|null
     */
    private function sanitizeValueToNumber(?string $value, $default = null): ?float
    {
        if (empty($value)) {
            return $default;
        }

        $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($filteredValue === false) {
            return $default;
        }

        return $filteredValue;
    }
}
