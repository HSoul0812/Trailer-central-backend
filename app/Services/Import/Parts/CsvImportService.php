<?php

namespace App\Services\Import\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Exceptions\Services\Import\Parts\EmptySKUException;
use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Part;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;
use App\Models\User\DealerLocation;
use App\Repositories\Bulk\Parts\BulkUploadRepositoryInterface;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use Closure;
use DB;
use Exception;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemInterface;
use Log;
use Storage;

/**
 * This class can be used to process the part bulk upload
 * the importer itself is very forgiving, it will try its
 * best to not stop the import process if something is wrong
 * the only case that it would stop processing the import
 * is when there is a required header missing from the CSV
 * file, otherwise it will keep process as usual
 *
 * @author Pond
 */
class CsvImportService implements CsvImportServiceInterface
{
    // Maximum length of string that we'll keep in the validation_errors column
    // if the error length is longer than this, we'll write the error string
    // to S3 and store the S3 file URI on the validation_errors column instead
    const MAX_VALIDATION_ERROR_CHAR_COUNT = 3072;

    // Path on S3 to store the validation error file
    const S3_VALIDATION_ERRORS_PATH = 'parts/validation-errors/%s';

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
    const HEADER_SHIPPING_FEE = 'Shipping Fee';
    const HEADER_IS_ACTIVE = 'Is Active';
    const HEADER_IS_TAXABLE = 'Is Taxable';

    // The separator to separate each images from the image header
    const HEADER_IMAGE_SEPARATOR = ',';

    // Example: Bin Hello World Qty, the <name> is "Hello World", this is case-sensitive
    const HEADER_BIN_QTY_REGEX = '/Bin\s+(?<name>.+)\s+Qty/';

    const HEADER_RULE_REQUIRED = 'required';
    const HEADER_RULE_OPTIONAL = 'optional';

    /**
     * All the valid headers. We use associative array for performance
     * Any columns that's not in this array are ignored
     *
     * @var string[]
     */
    const HEADER_RULES = [
        // Required headers
        self::HEADER_TYPE => self::HEADER_RULE_REQUIRED,
        self::HEADER_SKU => self::HEADER_RULE_REQUIRED,
        self::HEADER_BRAND => self::HEADER_RULE_REQUIRED,
        self::HEADER_TITLE => self::HEADER_RULE_REQUIRED,
        self::HEADER_CATEGORY => self::HEADER_RULE_REQUIRED,
        self::HEADER_PRICE => self::HEADER_RULE_REQUIRED,

        // Optional headers
        self::HEADER_VENDOR => self::HEADER_RULE_OPTIONAL,
        self::HEADER_SUBCATEGORY => self::HEADER_RULE_OPTIONAL,
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
        self::HEADER_SHIPPING_FEE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_IS_ACTIVE => self::HEADER_RULE_OPTIONAL,
        self::HEADER_IS_TAXABLE => self::HEADER_RULE_OPTIONAL,
    ];

    const MEMORY_CACHE_KEY_VENDORS = 'vendors';
    const MEMORY_CACHE_KEY_BRANDS = 'brands';
    const MEMORY_CACHE_KEY_TYPES = 'types';
    const MEMORY_CACHE_KEY_CATEGORIES = 'categories';
    const MEMORY_CACHE_KEY_BINS = 'bins';
    const MEMORY_CACHE_KEY_DEFAULT_LOCATION = 'default_location';

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
     * The $binHeaderIndexes keeps the header indexes for the bin
     * columns, this will allow is to process the bin data
     * separately from the other main columns
     *
     * @var array
     */
    protected $binHeaderIndexes = [];

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
     * We'll keep the list of processed SKU in this list
     * we'll use this information to skip the duplicate
     * SKU records in the CSV file
     *
     * @var string[]
     */
    protected $processedSKU = [];

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

    public function setBulkUpload(BulkUpload $bulkUpload): CsvImportService
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
        Log::info('Starting import for bulk upload ID: ' . $this->bulkUpload->id);

        $status = BulkUpload::COMPLETE;

        try {
            $this->importCSV();
        } catch (Exception $exception) {
            Log::info($message = $exception->getMessage());
            $this->errors[] = $message;
            $status = BulkUpload::VALIDATION_ERROR;
        }

        $this->bulkUploadRepository->update([
            'id' => $this->bulkUpload->id,
            'status' => $status,
            'validation_errors' => $this->getValidationErrors(),
        ]);
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

            $sku = data_get($data, $this->headerIndexes[self::HEADER_SKU]);

            if (empty($sku)) {
                $this->errors[] = "Issue with line #$line: SKU is empty, skipping this line.";

                return;
            }

            // Skip this line if we've processed this SKU already
            if (array_key_exists($sku, $this->processedSKU)) {
                $this->errors[] = "Issue with line #$line: duplicate SKU $sku, skipping this line.";

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
        // Ensure that the header is unique, we only want the first
        // header index if the header is duplicated.
        //
        // According to the doc if array_unique https://www.php.net/manual/en/function.array-unique.php
        // the method will retain the key and value of the first found element
        $headers = array_unique($headers);

        $this->headerIndexes = array_flip($headers);
        $requiredHeaders = $this->getRequiredHeaders();

        // When we found a required headers, we'll remove them one by one
        // from the $requiredHeaders array, then we can use it later to
        // check if all the required headers are present in the file or not
        foreach ($requiredHeaders as $header => $ignore) {
            if (array_key_exists($header, $this->headerIndexes)) {
                unset($requiredHeaders[$header]);
            }
        }

        if (count($requiredHeaders) > 0) {
            $message = sprintf("Missing required headers: %s. Action: Cancel the import!", implode(', ', array_keys($requiredHeaders)));
            throw new Exception($message);
        }

        foreach ($headers as $index => $header) {
            // Do nothing if this header exists in the header rules array
            if (array_key_exists($header, self::HEADER_RULES)) {
                continue;
            }

            unset($this->headerIndexes[$header]);

            // If the bin name can be extracted from the header name
            // we'll store it in the $binHeaderIndexes variable to be
            // processed later
            if ($binName = $this->getBinNameFromHeader($header)) {
                $this->binHeaderIndexes[$binName] = $index;

                $this->cacheBinsInMemory();
                continue;
            }

            // If the header doesn't exist in the rule, we unset it from
            // the interested indexes
            Log::info("Found invalid headers: $header, ignore this header...");
        }
    }

    /**
     * Get the bin name from the header name
     *
     * @param string $header
     * @return string|null
     */
    private function getBinNameFromHeader(string $header): ?string
    {
        preg_match(self::HEADER_BIN_QTY_REGEX, $header, $matches, PREG_OFFSET_CAPTURE);

        return data_get($matches, 'name.0');
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

        // We set this new index so the PartRepository doesn't
        // delete the images if there is no images index
        $partData['delete_images_if_no_index'] = false;

        Log::info("Importing part SKU " . $partData['sku']);

        /** @var Part $part */
        $part = $this->partsRepository->createOrUpdate($partData);

        $this->processBinsForPart($part, $data, $line);

        // Note that we've processed this SKU
        $this->processedSKU[$part->sku] = true;

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

        $headerHandlers = $this->getHeaderHandlers();

        // We only loop through the indexes that we have in the file
        foreach ($this->headerIndexes as $header => $index) {
            // Don't need to do anything if we don't have a handler for this header
            if (!array_key_exists($header, $headerHandlers)) {
                Log::info("No handler for header $header, skipping this header...");
                continue;
            }

            $value = data_get($data, $index);

            try {
                /**
                 * @throws EmptySKUException
                 */
                call_user_func_array($headerHandlers[$header], [
                    &$part,
                    $value,
                    $line,
                ]);
            } catch (EmptySKUException $exception) {
                $this->errors[] = $exception->getMessage();
            }
        }

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
        // so, we're doing it now to save our future time
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

    /**
     * Get the handlers for all the headers that we accept
     *
     * @return Closure[]
     */
    private function getHeaderHandlers(): array
    {
        return [
            self::HEADER_VENDOR => function (array &$part, ?string $value, int $line) {
                if ($this->storeErrorIfValueIsEmpty(self::HEADER_VENDOR, $line, $value)) {
                    return;
                }

                $part['vendor_id'] = $this->getVendorIdByName($value);
                $this->storeErrorIfValueDoesNotExistInDB(self::HEADER_VENDOR, $line, $part['vendor_id'], $value);
            },
            self::HEADER_BRAND => function (array &$part, ?string $value, int $line) {
                if ($this->storeErrorIfValueIsEmpty(self::HEADER_BRAND, $line, $value)) {
                    return;
                }

                $part['brand_id'] = $this->getBrandIdByName($value);
                $this->storeErrorIfValueDoesNotExistInDB(self::HEADER_BRAND, $line, $part['brand_id'], $value);
            },
            self::HEADER_TYPE => function (array &$part, ?string $value, int $line) {
                if ($this->storeErrorIfValueIsEmpty(self::HEADER_TYPE, $line, $value)) {
                    return;
                }

                $part['type_id'] = $this->getTypeIdByName($value);
                $this->storeErrorIfValueDoesNotExistInDB(self::HEADER_TYPE, $line, $part['type_id'], $value);
            },
            self::HEADER_CATEGORY => function (array &$part, ?string $value, int $line) {
                if ($this->storeErrorIfValueIsEmpty(self::HEADER_CATEGORY, $line, $value)) {
                    return;
                }

                $part['category_id'] = $this->getCategoryIdByName($value);
                $this->storeErrorIfValueDoesNotExistInDB(self::HEADER_CATEGORY, $line, $part['category_id'], $value);
            },
            self::HEADER_SUBCATEGORY => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_SUBCATEGORY, $line, $value);
                $part['subcategory'] = $this->sanitizeValueToString($value, '');
            },
            self::HEADER_TITLE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_TITLE, $line, $value);
                $part['title'] = $this->sanitizeValueToString($value, '');
            },
            self::HEADER_SKU => function (array &$part, ?string $value, int $line) {
                // To reach this point, SKU must be a non-empty value already
                $part['sku'] = $this->sanitizeValueToString($value);
            },
            self::HEADER_PRICE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_PRICE, $line, $value);
                $part['price'] = $this->sanitizeValueToNumber($value, 0);
            },
            self::HEADER_DEALER_COST => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_DEALER_COST, $line, $value);
                $part['dealer_cost'] = $this->sanitizeValueToNumber($value, 0);
            },
            self::HEADER_MSRP => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_MSRP, $line, $value);
                $part['msrp'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_WEIGHT => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_WEIGHT, $line, $value);
                $part['weight'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_WEIGHT_RATING => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_WEIGHT_RATING, $line, $value);
                $part['weight_rating'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_DESCRIPTION => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_DESCRIPTION, $line, $value);
                $part['description'] = $this->sanitizeValueToString($value);
            },
            self::HEADER_SHOW_ON_WEBSITE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_SHOW_ON_WEBSITE, $line, $value);
                $part['show_on_website'] = $this->sanitizeValueToBoolean($value, false);
            },
            self::HEADER_IMAGE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_IMAGE, $line, $value);
                $part['images'] = $this->getPartImages($value);
            },
            self::HEADER_STOCK_MIN => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_STOCK_MIN, $line, $value);
                $part['stock_min'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_STOCK_MAX => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_STOCK_MAX, $line, $value);
                $part['stock_max'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_VIDEO_EMBED_CODE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_VIDEO_EMBED_CODE, $line, $value);
                $part['video_embed_code'] = $this->sanitizeValueToString($value);
            },
            self::HEADER_ALTERNATE_PART_NUMBER => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_ALTERNATE_PART_NUMBER, $line, $value);
                $part['alternative_part_number'] = $this->sanitizeValueToString($value);
            },
            self::HEADER_SHIPPING_FEE => function (array &$part, ?string $value, int $line) {
                $this->storeErrorIfValueIsEmpty(self::HEADER_SHIPPING_FEE, $line, $value);
                $part['shipping_fee'] = $this->sanitizeValueToNumber($value);
            },
            self::HEADER_IS_ACTIVE => function (array &$part, ?string $value, int $line) {
                $part['is_active'] = $this->sanitizeValueToBoolean($value, false);
            },
            self::HEADER_IS_TAXABLE => function (array &$part, ?string $value, int $line) {
                $part['is_taxable'] = $this->sanitizeValueToBoolean($value, true);
            },
        ];
    }

    /**
     * Get the line issue message string
     *
     * @param int $line
     * @param string $message
     * @return string
     */
    private function getLineIssueMessage(int $line, string $message): string
    {
        return "Issue with line #$line: $message.";
    }

    /**
     * Get the value is empty message
     *
     * @param string $header
     * @return string
     */
    private function getValueIsEmptyMessage(string $header): string
    {
        return sprintf("the %s is empty", $header);
    }

    /**
     * Get the message to show when the value doesn't exist in DB
     *
     * @param string $header
     * @param string $value
     * @return string
     */
    private function getValueDoesNotExistInTheSystemMessage(string $header, string $value): string
    {
        return sprintf("cannot find %s %s in the system", $header, $value);
    }

    /**
     * @param string $header
     * @param int $line
     * @param string|null $value
     * @return bool Return true if the value is empty
     */
    private function storeErrorIfValueIsEmpty(string $header, int $line, ?string $value): bool
    {
        if (!empty($value)) {
            return false;
        }

        $this->errors[] = $this->getLineIssueMessage(
            $line,
            $this->getValueIsEmptyMessage($header)
        );

        return true;
    }

    /**
     * Store the error if value does not exist in DB
     *
     * @param string $header
     * @param int $line
     * @param $dataFromDB
     * @param string|null $value
     * @return void
     */
    private function storeErrorIfValueDoesNotExistInDB(string $header, int $line, $dataFromDB, ?string $value): void
    {
        if (!empty($dataFromDB)) {
            return;
        }

        $this->errors[] = $this->getLineIssueMessage(
            $line,
            $this->getValueDoesNotExistInTheSystemMessage($header, $value)
        );
    }

    /**
     * Get the validation errors string
     *
     * @return string
     */
    private function getValidationErrors(): string
    {
        $jsonEncodedValidationErrors = json_encode($this->errors);

        if (strlen($jsonEncodedValidationErrors) > self::MAX_VALIDATION_ERROR_CHAR_COUNT) {
            // Example filename: 1001-part-bulk-upload-123-20220926.txt
            $fileName = sprintf(
                "%d-part-bulk-upload-%d-%s.txt",
                $this->bulkUpload->dealer_id,
                $this->bulkUpload->id,
                now()->format('Ymd')
            );

            $filePath = sprintf(self::S3_VALIDATION_ERRORS_PATH, $fileName);

            $this->storage->put($filePath, implode(PHP_EOL, $this->errors));

            return $this->storage->url($filePath);
        }

        return $jsonEncodedValidationErrors;
    }

    /**
     * This method cache the dealer's bin in the memory for fast lookup
     * @return void
     */
    private function cacheBinsInMemory(): void
    {
        // No need to rerun the query if the cache existed
        if (array_key_exists(self::MEMORY_CACHE_KEY_BINS, $this->memoryCache)) {
            return;
        }

        $this->memoryCache[self::MEMORY_CACHE_KEY_BINS] = DB::table(Bin::getTableName())
            ->where('dealer_id', $this->bulkUpload->dealer_id)
            ->get(['id', 'bin_name'])
            ->keyBy('bin_name');
    }

    /**
     * Process the bin association with the part
     *
     * @param Part $part
     * @param array $data
     * @param int $line
     * @return void
     */
    private function processBinsForPart(Part $part, array $data, int $line): void
    {
        // If we don't have any bin related headers, this block won't get processed
        foreach ($this->binHeaderIndexes as $binName => $index) {
            // Do not process this header if the index doesn't exist in the row
            if (!Arr::has($data, $index)) {
                continue;
            }

            // Do not process this header if the data is an empty string
            // this way the part bin qty can still be in DB and stays intact
            $quantity = trim($data[$index]);
            if ($quantity === '') {
                continue;
            }

            // If the given data is not a float (numeric), we'll also skip this header
            $quantity = filter_var($quantity, FILTER_VALIDATE_FLOAT);
            if ($quantity === false) {
                continue;
            }

            $bin = data_get($this->memoryCache[self::MEMORY_CACHE_KEY_BINS], $binName);

            // If we can't find this bin from the cache, we'll create a new bin for it
            if ($bin === null) {
                $defaultLocation = $this->getDealerDefaultLocation();

                $bin = Bin::create([
                    'dealer_id' => $this->bulkUpload->dealer_id,
                    'location' => $defaultLocation->dealer_location_id,
                    'bin_name' => $binName,
                ]);

                // Make sure to add this new bin to the bin cache, so we don't need to
                // create it again and again
                $this->memoryCache[self::MEMORY_CACHE_KEY_BINS][$binName] = $bin;
            }

            // A simple work of associate the part and bin together
            $partBinQty = BinQuantity::firstOrNew([
                'part_id' => $part->id,
                'bin_id' => $bin->id,
            ]);

            $partBinQty->qty = $quantity;

            $partBinQty->save();
        }
    }

    /**
     * Get the default dealer location
     *
     * @return DealerLocation
     */
    private function getDealerDefaultLocation(): DealerLocation
    {
        if (!array_key_exists(self::MEMORY_CACHE_KEY_DEFAULT_LOCATION, $this->memoryCache)) {
            $defaultLocation = DealerLocation::query()
                ->where('dealer_id', $this->bulkUpload->dealer_id)
                ->where('is_default', true)
                ->first(['dealer_location_id']);

            $this->memoryCache[self::MEMORY_CACHE_KEY_DEFAULT_LOCATION] = $defaultLocation;
        }

        return $this->memoryCache[self::MEMORY_CACHE_KEY_DEFAULT_LOCATION];
    }

    /**
     * Get the part images
     *
     * @param string|null $value
     * @return array
     */
    private function getPartImages(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $images = explode(self::HEADER_IMAGE_SEPARATOR, $value);

        return collect($images)
            // We'll transform each image by trimming it
            ->map(function (string $image) {
                return trim($image);
            })
            // As a precaution, we only use the one that's not empty
            ->filter(function (string $image) {
                return !empty($image);
            })
            // Reindex the indexes so it can be sequentially again
            ->values()
            // Then we map it into a proper image array
            ->map(function (string $image, int $index) {
                return [
                    'url' => $image,
                    'position' => $index,
                ];
            })
            ->toArray();
    }

    /**
     * Sanitize the given value to boolean
     *
     * @param string|null $value
     * @param bool $default
     * @return bool
     */
    private function sanitizeValueToBoolean(?string $value, bool $default = false): bool
    {
        if (is_null($value) || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
