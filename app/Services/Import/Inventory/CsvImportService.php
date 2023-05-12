<?php

namespace App\Services\Import\Inventory;

use App\Events\Inventory\InventoryUpdated;
use App\Exceptions\Inventory\InventoryException;
use App\Helpers\ConvertHelper;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\Category;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\User\DealerLocation;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Bulk\Inventory\BulkUpload;
use App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface;

/**
 * Class CSVImportService
 * @package App\Services\Import\Inventory
 */
class CsvImportService implements CsvImportServiceInterface
{
    public const MAX_VALIDATION_ERROR_CHAR_COUNT = 3072;
    private const S3_VALIDATION_ERRORS_PATH = 'inventory/validation-errors/%s';

    /**
     * @var BulkUploadRepositoryInterface
     */
    protected $bulkUploadRepository;

    /**
     * @var
     */
    protected $bulkUpload;

    /**
     * @var array
     */
    private static $_categoryToEntityTypeId = array();

    /**
     * @var array
     */
    private static $_attributes = array();

    /**
     * @var array
     */
    private static $_labels = array();

    /**
     * @var ConvertHelper
     */
    private $convertHelper;

    /**
     * @private
     */
    public const IM_IGNORE = 0;

    /**
     * @private
     */
    public const IM_REPLACE = 1;

    /**
     * @private
     */
    public const IM_APPEND = 2;

    /**
     * @var int
     */
    protected $imageMode = 0;

    /**
     * @var int
     */
    protected $appendIndex = null;

    /**
     * @var bool
     */
    protected $inventoryUpdate = false;

    /**
     * @var array
     */
    protected $allowedHeaderValues = [
        "inventory_id" => false,
        "on website & classifieds" => true,
        "stock" => true,
        "title" => true,
        "model" => true,
        "brand" => true,
        "manufacturer" => true,
        "location_phone" => true,
        "location_zip" => true,
        "description" => true,
        "description_html" => true,
        "vin" => true,
        "category" => true,
        "price" => true,
        "hidden_price" => true,
        "sales_price" => true,
        "website_price" => true,
        "msrp" => true,
        "year" => true,
        "condition" => true,
        "length" => true,
        "width" => true,
        "height" => true,
        "weight" => true,
        "status" => true,
        "cost_of_unit" => true,
        "cost_of_shipping" => true,
        "cost_of_prep" => true,
        "total_of_cost" => true,
        "minimum_selling_price" => true,
        "notes" => true,
        "images" => true,
        "gvwr" => true,
        "axle_capacity" => true,
        "payload_capacity" => true,
        "is_special" => true,
        "is_featured" => true,
        "is_archived" => true,
        "show_on_website" => true,
        "append_images" => true,
        "replace_images" => true,
        "video_embed_code" => true,
        "show_on_auction123" => true,
        "show_on_rvt" => true
    ];

    // mapping between import column names and field names in database:
    // array keys are field names in the database
    // array values are column names in the import file
    /**
     * @var array
     */
    private static $_columnMap = array(
        "inventory_id" => "identifier",
        "on website & classifieds" => array("on website & classifieds", "on website and classifieds", "show on website"),
        "stock" => array("stock", "stock #", "stock#", "stock nr", "stock number"),
        "title" => "title",
        "model" => array("model", "model#", "model no", "model no."),
        "brand" => "brand",
        "manufacturer" => array("manufacturer", "mfg"),
        "location_phone" => array("location", "location_phone", "location phone"),
        "location_zip" => array("location zip"),
        "description" => array("description", "desc", "info"),
        "description_html" => array("description_html", "desc_html", "info_html"),
        "vin" => array("vin", "vin#"),
        "category" => "category",
        "price" => array("price", "sellingprice"),
        "hidden_price" => array("hidden price"),
        "sales_price" => array("sales price", "sale price", "sales only price", "sale only price"),
        "website_price" => array("website price", "website only price"),
        "msrp" => array("msrp", "mfg price"),
        "year" => "year",
        "condition" => "condition",
        "length" => array("length", "length (ft)", "length ft", "floor length", "floor length (ft)", "floor length ft"),
        "width" => array("width", "width (ft)", "width ft"),
        "height" => array("height", "height (ft)", "height ft"),
        "weight" => array("weight", "weight (lbs)", "weight lbs", "curb weight"),
        "status" => "status",

        "cost_of_unit" => array("unit cost", "admin-unit cost", "cost of unit", "admin-cost of unit"),
        "cost_of_shipping" => array("shipping cost", "admin-shipping cost", "cost of shipping", "admin-cost of shipping"),
        "cost_of_prep" => array("prep cost", "admin-prep cost", "cost of prep", "admin-cost of prep"),
        "total_of_cost" => array("total cost", "admin-total cost"),
        "minimum_selling_price" => array("min sell price", "admin-min sell price", "msp"),
        "notes" => "notes",
        "images" => array("images", "photourls", "images (comma seperated)", "images (comma separated)"),
        "gvwr" => array("gvwr", "gvwr (lbs)", "gvwr lbs", "gross weight"),
        "axle_capacity" => array("axle capacity", "axle capacity (lbs)", "axle capacity lbs", "axle (lbs)", "axle lbs"),
        "payload_capacity" => array("payload capacity", "payload weight", "payload capacity (lbs)", "payload capacity lbs", "payload (lbs)", "payload lbs"),
        "is_special" => array("is_special", "is special", "is on special", "special", "website special"),
        "is_featured" => array("is_featured", "is featured", "featured", "website featured"),
        "is_archived" => array("archived", "is archived"),
        "show_on_website" => array("show on website" , "hidden", "is hidden"),
        "append_images" => array("append images on import", "append image", "append images", "use images", "use image"),
        "replace_images" => array("replace images", "replace_images"),
        "image_mode" => array("image mode", "images mode", "img mode"),
        "video_embed_code" => array("video_embed_code", "video embed code"),
        "show_on_auction123" => array("show_on_auction123", "show on auction123", "Show on Auction123", "Auction123"),
        "show_on_rvt" => array("show_on_rvt", "show on rvt", "Show on Rvt", "Show on RVT", "RVT"),
    );

    /**
     * @var array
     */
    private static $_columnValidation = array(
        "inventory_id" => array("type" => "string", "regex" => "([a-zA-Z0-9]+)"),
        "on website & classifieds" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
        "stock" => array("type" => "string", "unique" => true),
        "title" => array("type" => "string", "length" => 255, "regex" => "[\w\s\d\.'\"\\/\*\+\?]*"),
        "manufacturer" => array("type" => "string"),
        "model" => array("type" => "string", "length" => 255, "regex" => "[\w\s\d\.'\"\\/\*\+\?]*"),
        "brand" => array(
            "type" => "enum",
            "list" => array()
        ),
        "description" => array("type" => "string"),
        "description_html" => array("type" => "string"),
        "location" => array("type" => "string"),
        "category" => array(
            "type" => "enum",
            /**
             * These are ONLY specialized aliases that have existed for one reason or another.  Do NOT modify this list.  Preferably, do not ADD anymore to
             * this list.  The real categories are added via query in _configure() below... in which the categories and legacy categories are added.
             */
            "list" => array(
                "camping" => "camping_rv",
                "rv" => "camping_rv",
                "camping rv" => "camping_rv",
                "cargo" => "cargo_enclosed",
                "enclosed" => "cargo_enclosed",
                "cargo enclosed" => "cargo_enclosed",
                "car" => "car_racing",
                "racing" => "car_racing",
                "car racing" => "car_racing",
                "carhauler" => "car_racing",
                "car hauler" => "car_racing",
                "flatbed trailer" => "flatbed",
                "part" => "other",
                "stock" => "stock_stock-combo",
                "stock trailer" => "stock_stock-combo",
                "stock_trailer" => "stock_stock-combo",
                "stock stock-combo" => "stock_stock-combo",
                "vending" => "vending_concession",
                "vending concession" => "vending_concession",
                "vending_consession" => "vending_concession",
                "class a" => "class_a",
                "class b" => "class_b",
                "class c" => "class_c",
                "truck bed" => "bed_equipment",
                "bed equipment" => "bed_equipment",
                "tow dolly" => "tow_dolly",
                "equipment trailer" => "equipment",
                "dolly" => "tow_dolly",
                "vehicle atv" => "vehicle_atv",
                "vehicle car" => "vehicle_car",
                "vehicle golf cart" => "golf_cart",
                "vehicle motorcycle" => "vehicle_motorcycle",
                "vehicle truck" => "vehicle_truck",
                "vehicle suv" => "vehicle_suv",
                "sport side-by-side" => "sport_side-by-side",
                "utility side-by-side" => "utility_side-by-side",
                "Utility Side-by-Side (UTV)" => "utility_side-by-side",
                "snowmobile vehicle" => "vehicle_snowmobile",
                "personal watercraft" => "personal_watercraft",
                "canoe" => "canoe-kayak",
                "kayak" => "canoe-kayak",
                "power boat" => "powerboat",
                "equip tractor" => "equip_tractor",
                "equip attachment" => "equip_attachment",
                "equip farm" => "equip_farm-ranch",
                "equip ranch" => "equip_farm-ranch",
                "equip lawn" => "equip_lawn",
                "rv.class-a" => "class_a",
                "rv.class-b" => "class_b",
                "rv.class-c" => "class_c",
                "semi-trailer_reefer" => "semi_reefer",
                "semi-trailer_grain-hopper" => "semi_grain-hopper",
                "semi-trailer_livestock" => "semi_livestock",
            )
        ),
        "vin" => array(
            "type" => "string"
        ),
        "price" => array("type" => "decimal"),
        "hidden_price" => array("type" => "decimal"),
        "year" => array("type" => "date", "format" => "Y"),
        "condition" => array(
            "type" => "enum",
            "list" => array(
                "new" => "new",
                "used" => "used",

                "remanufactured" => "remanufactured",
                // is the dashed version necessary?
                "re-manufactured" => "remanufactured",
                "remfg" => "remanufactured",
            )
        ),
        "length" => array("type" => "measurement"),
        "width" => array("type" => "measurement"),
        "height" => array("type" => "measurement"),
        "weight" => array("type" => "string"),
        /*"type"                  => array(
            "type" => "enum",
            "list" => array(
                "trailer"       => "1",
                "other"         => "1",
                "horsetrailer"  => "2",
                "rv"            => "3",
                "vehicle"       => "4",
                "watercraft"    => "5",
                "equipment"     => "6",
                "semitrailer"   => "7",
                "sportsvehicle" => "8",
            )
        ),*/
        "status" => array(
            "type" => "enum",
            "list" => array(
                "available" => 1,
                "sold" => 2,
                "on order" => 3,
                "pending sale" => 4,
                "special order" => 5
            )
        ),
        "is_featured" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
        "is_special" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
        "is_archived" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0",
            )
        ),
        "show_on_website" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
        "cost_of_unit" => array("type" => "decimal"),
        "cost_of_shipping" => array("type" => "decimal"),
        "cost_of_prep" => array("type" => "decimal"),
        "total_of_cost" => array("type" => "decimal"),
        "minimum_selling_price" => array("type" => "decimal"),
        "notes" => array("type" => "string"),
        "images" => array("type" => "string"),
        "gvwr" => array("type" => "string"),
        "axle_capacity" => array("type" => "string"),
        "msrp" => array("type" => "msrp"),
        "location_phone" => array("type" => "location_phone"),
        "show_on_auction123" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
        "show_on_rvt" => array(
            "type" => "enum",
            "list" => array(
                "yes" => "1",
                "no" => "0"
            )
        ),
    );

    /**
     * @var array
     */
    private static $_columnRequired = array(
        "inventory_id" => false,
        "stock" => true,
        "title" => true,
        "manufacturer" => true,
        "model" => true,
        "category" => false,
        "price" => true,
        "year" => true,
        "condition" => true,
        "location_phone" => true
    );

    /**
     * @var array
     */
    private $requiredBrandCategories = [
        EntityType::ENTITY_TYPE_RV,
        EntityType::ENTITY_TYPE_WATERCRAFT
    ];

    /**
     * @var string[]
     */
    public static $locationColumns = array(
        "location city",
        "location country",
        "location postal code",
        "location region",
        "location name"
    );

    /**
     * @var string[]
     */
    public static $ignorableColumns = array(
        "number of images",
        "admin-notes",
        "created at date"
    );

    /**
     * @var array
     */
    protected $inventory = [];

    /**
     * @var array Array of validation errors per row
     */
    private $validationErrors = [];

    /**
     * @var array Array of all errors
     */
    private $globalListOfErrors = [];

    /**
     * @var array Array of CSV headers. Of the form `array[index] = value` where index is the position and value is the header
     */
    private $indexToheaderMapping = [];

    /**
     * @var InventoryServiceInterface $inventoryService
     */
    private $inventoryService;

    /**
     * @param BulkUploadRepositoryInterface $bulkUploadRepository
     * @param InventoryServiceInterface $inventoryService
     */
    public function __construct(
        BulkUploadRepositoryInterface $bulkUploadRepository,
        InventoryServiceInterface $inventoryService
    ) {
        $this->bulkUploadRepository = $bulkUploadRepository;
        $this->inventoryService = $inventoryService;

        $this->convertHelper = new ConvertHelper();
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        Log::info('Starting import for inventory bulk upload ID: ' . $this->bulkUpload->id);

        // Set attributes
        $this->setAttributes();

        // Set categories
        $this->setCategories();

        // Set brands
        $this->setBrands();

        /* For testing purposes only
        Log::debug("Attributes: " . json_encode(self::$_attributes));
        Log::debug("Allowed: " . json_encode($this->allowedHeaderValues));
        Log::debug("Validation: " . json_encode(self::$_columnValidation));
        Log::debug("Mapping: " . json_encode(self::$_columnMap));*/

        try {
            if (!$this->validate()) {
                Log::info('Invalid bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
                $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => $this->outputValidationErrors()]);
                return false;
            }
        } catch (\Exception $ex) {
            Log::info('Exception on bulk upload ID: ' . $this->bulkUpload->id . ' setting validation_errors...');
            Log::debug($ex->getTraceAsString());
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => $this->outputValidationErrors()]);
            return false;
        }

        if (empty($this->validationErrors)) {
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::COMPLETE]);
        } else {
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::COMPLETE, 'validation_errors' => json_encode($this->validationErrors)]);
        }

        Log::info('Validation passed for inventory bulk upload ID: ' . $this->bulkUpload->id . ' import success...');
        return true;
    }

    /**
     * @param BulkUpload $bulkUpload
     * @return void
     */
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
        if ($lineNumber === 1) {
            return;
        }

        /*Log::info('Importing bulk uploaded inventory. Inventory Bulk Upload #' . $this->bulkUpload->id . PHP_EOL);
        Log::debug($this->inventory);*/

        try {
            $this->inventory['dealer_id'] = $this->bulkUpload->dealer_id;

            if ($this->inventoryUpdate) {
                $inventory = $this->inventoryService->update(array_merge($this->inventory, ['updateAttributes' => true]));
            } else {
                $inventory = $this->inventoryService->create($this->inventory);
            }

            event(new InventoryUpdated($inventory, [
                'description' => 'Created/updated using inventory bulk uploader'
            ]));
        } catch (\Exception | InventoryException $ex) {
            $this->validationErrors[] = 'Error occurred ' . (!empty($this->inventory['stock']) ? ' with stock: ' . $this->inventory['stock'] : ' on row: ' . $lineNumber);
            $this->bulkUploadRepository->update(['id' => $this->bulkUpload->id, 'status' => BulkUpload::VALIDATION_ERROR, 'validation_errors' => json_encode($this->validationErrors)]);
            Log::info('Error found on inventory for inventory bulk upload : ' . $this->bulkUpload->id . ' : ' . $ex->getTraceAsString() . json_encode($this->validationErrors));
            Log::info("Index to header mapping: {$this->indexToheaderMapping}");
            throw new \Exception("Error creating/updating Inventory");
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
        $this->streamCsv(function ($csvData, $lineNumber) {
            // for each column
            foreach ($csvData as $index => $value) {
                // for the header line
                if ($lineNumber === 1) {
                    // if column header is not allowed
                    $value = trim($value);
                    if (!$this->isAllowedHeader($value)) {
                        $this->validationErrors[$lineNumber][] = $this->printError($lineNumber, $index + 1, "Invalid Header: " . $value);
                        Log::info("Invalid Header: " . $value);
                    // else, the column header is allowed
                    } else {
                        $this->indexToheaderMapping[$index] = strtolower($value);
                        $appendImages = array_search(strtolower($value), self::$_labels);

                        if ($appendImages == 'append_images') {
                            $this->appendIndex = $index;
                        }
                    }
                // for lines > 1
                } else {
                    if ($this->appendIndex) {
                        $isAppend = $csvData[$this->appendIndex];
                        $isAppend = self::handleBoolean($isAppend);

                        if ($isAppend === true) {
                            $this->imageMode = self::IM_APPEND;
                        }
                    }

                    $header = array_search(strtolower($this->indexToheaderMapping[$index]), array_map('strtolower', self::$_labels));
                    // Log::debug(array("header" => $header, 'headerMapping' => $this->indexToheaderMapping[$index]));

                    if ($header) {
                        if ($errorMessage = $this->isDataInvalid($header, $value)) {
                            $this->validationErrors[$lineNumber][] = $this->printError($lineNumber, $index + 1, $errorMessage);

                            // Include errors to a global list to evaluate later
                            if (!array_key_exists($errorMessage, $this->globalListOfErrors)) {
                                $this->globalListOfErrors[$errorMessage] = 1;
                            } else {
                                $this->globalListOfErrors[$errorMessage] += 1;
                            }
                        }
                    }
                }
            }

            //Log::debug(self::$_labels);

            // if there's an error return false, if not, import part
            if ($lineNumber != 1) {
                if (isset($this->validationErrors[$lineNumber]) && count($this->validationErrors[$lineNumber]) > 0) {
                    // Remove duplicate errors on other units to avoid having a massive validation errors list
                    foreach ($this->validationErrors[$lineNumber] as $keyError => $error) {
                        $exploded = trim(explode('in line', $error)[0]);
                        if (isset($this->globalListOfErrors[$exploded]) && $this->globalListOfErrors[$exploded] > 1) {
                            unset($this->validationErrors[$lineNumber][$keyError]);
                            $this->globalListOfErrors[$exploded] -= 1;

                            // If there's no errors left, remove from list
                            if (empty($this->validationErrors[$lineNumber])) {
                                unset($this->validationErrors[$lineNumber]);
                            }
                        }
                    }

                    $this->inventory = [];

                    return false;
                } else {
                    Log::info("Importing...");
                    $this->import($csvData, $lineNumber);

                    $this->inventory = [];
                }
            } else {
                if (!$this->validateHeaders()) {
                    throw new \Exception("Missing required headers");
                }
            }

            return true;
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
            throw new \Exception('Could not open stream for reading file: [' . $this->bulkUpload->import_source . ']');
        }

        // iterate through all lines
        $lineNumber = 1;
        while (!feof($stream)) {
            $isEmptyRow = true;
            $csvData = fgetcsv($stream);

            // see if the row is empty,
            // one value is enough to indicate non empty row
            foreach ($csvData as $value) {
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

    /**
     * @return void
     */
    private function setAttributes()
    {
        $attributes = Attribute::all();
        foreach ($attributes as $attribute) {
            self::$_attributes[$attribute->code] = $attribute->attribute_id;
            self::$_columnRequired[$attribute->code] = false;
            $this->allowedHeaderValues[$attribute->code] = true;
            self::$_columnMap[$attribute->code] = array($attribute->code, strtolower($attribute->name), $attribute->name);

            // If aliases are available
            if ($attribute->aliases) {
                foreach (explode(",", $attribute->aliases) as $alias) {
                    self::$_columnMap[$attribute->code][] = $alias;
                }
            }

            if ($attribute->type == "select") {
                $list = array();
                $values = explode(',', $attribute->values);

                foreach ($values as $value) {
                    $group = explode(':', $value);
                    @$list[strtolower($group[1])] = $group[0];
                }

                self::$_columnValidation[$attribute->code] = array(
                    'type' => 'enum',
                    'list' => $list
                );
            } else {
                self::$_columnValidation[$attribute->code] = array("type" => "string");
            }
        }
    }

    /**
     * @return void
     */
    private function setCategories()
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            self::$_columnValidation['category']['list'][$category->category] = $category->legacy_category;
            self::$_columnValidation['category']['list'][$category->legacy_category] = $category->legacy_category;

            if (!empty($category->alt_category)) {
                self::$_columnValidation['category']['list'][$category->alt_category] = $category->legacy_category;
            }

            self::$_categoryToEntityTypeId[$category->legacy_category] = $category->entity_type_id;
        }
    }

    /**
     * @return void
     */
    private function setBrands()
    {
        $brands = Brand::all();

        foreach ($brands as $brand) {
            self::$_columnValidation['brand']['list'][strtolower($brand->name)] = $brand->name;
        }
    }

    /**
     * @param $val
     * @return bool
     */
    private function isAllowedHeader($val): bool
    {
        $val = strtolower($val);

        if (in_array($val, self::$locationColumns) || in_array($val, self::$ignorableColumns)) {
            return true;
        }

        foreach ($this->allowedHeaderValues as $columnName => $required) {
            $mapKey = self::$_columnMap[$columnName];

            if (is_array($mapKey) && in_array($val, $mapKey)) {
                //Log::info("Setting A: " . $val . " on " . $columnName);
                self::$_labels[$columnName] = $val;
                return true;
            } else {
                $columnIndex = array_search($val, self::$_columnMap);

                if ($columnIndex == $columnName) {
                    //Log::info("Setting B: " . $val . " on " . $columnName);
                    self::$_labels[$columnName] = $val;
                    return true;
                }
            }

            if ($val == $columnName && array_key_exists($val, self::$_columnRequired)) {
                //Log::info("Setting C: " . $val . " on " . $columnName);
                self::$_labels[$columnName] = $val;
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function validateHeaders(): bool
    {
        $errors = [];

        foreach (self::$_columnRequired as $column => $required) {
            if (!in_array($column, array_keys(self::$_labels)) && $required) {
                $errors[] = "$column is a required column.";
            }
        }

        if (!empty($errors)) {
            $this->validationErrors = array_merge($this->validationErrors, $errors);
            return false;
        }

        return true;
    }

    /**
     * @param $line
     * @param $column
     * @param $errorMessage
     * @return string
     */
    private function printError($line, $column, $errorMessage): string
    {
        return "$errorMessage in line $line at column $column";
    }

    /**
     * Sanitize string Value
     *
     * @param string $value
     * @return bool|string
     */
    private function sanitizeValue(string $value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING, array('flags' => FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_HIGH));
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
        if (!$type) {
            return false;
        } elseif ((in_array($type, self::$locationColumns) || in_array($type, self::$ignorableColumns))) {
            return false;
        }

        // Validate if the required column isn't empty
        if (array_key_exists($type, self::$_columnRequired)) {
            if (self::$_columnRequired[$type] && !($value === "0" || $value)) {
                return $type . " is a required field, can't be empty";
            }
        }

        Log::info('Type: ' . $type . ' Value: ' . $value);

        switch ($type) {
            case 'stock':
                $this->inventoryUpdate = false;

                $inventories = Inventory::where([
                    'stock' => $value,
                    'dealer_id' => $this->bulkUpload->dealer_id
                ]);

                if ($inventories->count() == 1) {
                    $inventoryByStock = $inventories->first();

                    if ($inventoryByStock) {
                        $this->inventoryUpdate = true;
                        $inventoryId = $inventoryByStock->inventory_id;
                        $this->inventory["inventory_id"] = $inventoryId;
                        $this->inventory["is_archived"] = 0;

                        Log::debug("Updating inventory stock '{$value}' (Id: {$inventoryId}) via import.");
                    }
                } else {
                    if ($inventories->count() > 1) {
                        return "Duplicate Stock # '{$value}' found in database.";
                    }
                }

                $this->inventory[$type] = $value;
                break;

            case 'category':
                if (isset(self::$_columnValidation[$type]['list'][strtolower($value)])) {
                    $this->inventory[$type] = self::$_columnValidation[$type]['list'][strtolower($value)];
                }

                if (!empty($this->inventory[$type]) && isset(self::$_categoryToEntityTypeId[$this->inventory[$type]])) {
                    $this->inventory["entity_type_id"] = self::$_categoryToEntityTypeId[$this->inventory[$type]];
                } else {
                    // this should really fail further up the line
                    $this->inventory["entity_type_id"] = 1;
                    // return "Category $value is not a valid category"; Allowing units as Rade requested
                }
                break;

            case 'brand':
                if (isset(self::$_columnValidation[$type]['list'][strtolower($value)])) {
                    $this->inventory[$type] = self::$_columnValidation[$type]['list'][strtolower($value)];
                } else {
                    if (isset($this->inventory["entity_type_id"]) && in_array($this->inventory["entity_type_id"], $this->requiredBrandCategories)) {
                        return "A valid brand name is required for Recreational Vehicles and Watercraft";
                    }
                }

                break;

            case 'status':
            case 'is_special':
            case 'is_featured':
            case 'show_on_website':
            case 'show_on_auction123':
            case 'show_on_rvt':
                if (isset(self::$_columnValidation[$type]['list'][strtolower($value)])) {
                    $this->inventory[$type] = self::$_columnValidation[$type]['list'][strtolower($value)];
                }
                break;

            case 'append_images':
                $value = self::handleBoolean($value);

                if ($value === true) {
                    $this->imageMode = self::IM_APPEND;
                } elseif ($value === false) {
                    // this would normally set it to normal, so just leave it alone since it already defaults to that
                    // this also allows the inverse (append_images) to set a mode...
                } else {
                    return "Value for append images column must be a valid boolean-type (yes, y, no, n, or 0/1)";
                }
                break;

            case 'replace_images':
                $value = self::handleBoolean($value);

                if ($value === true) {
                    $this->imageMode = self::IM_REPLACE;
                } elseif ($value === false) {
                    // this would normally set it to normal, so just leave it alone since it already defaults to that
                    // this also allows the inverse (append_images) to set a mode...
                } else {
                    return "Value for replace images column must be a valid boolean-type (yes, y, no, n, or 0/1)";
                }
                break;

            case 'image_mode':
                $value = trim(strtolower($value));

                if ($this->imageMode !== self::IM_IGNORE) {
                    return "If you specify 'image mode' column, you must NOT specify append or replace image columns. These cannot be combined; use one or the other.";
                } else {
                    if ($value === 'ignore' || $value === 'i') {
                        $this->imageMode = self::IM_IGNORE;
                    } elseif ($value === 'replace' || $value === 'r') {
                        $this->imageMode = self::IM_REPLACE;
                    } elseif ($value === 'append' || $value === 'a') {
                        $this->imageMode = self::IM_APPEND;
                    } else {
                        return "Value for image mode column must be one of 'ignore', 'append' or 'replace'";
                    }
                }
                break;

            case 'images':
                // Stop sending images here, InventoryService will handle...
                if (!empty($value)) {
                    $images = explode(',', $value);
                    $images = array_map('trim', $images);
                    $images = array_filter($images);

                    if (count($images) > 0) {
                        if ($this->imageMode == self::IM_APPEND) {
                            foreach ($images as $image) {
                                $this->inventory['new_images'][] = [
                                    'url' => $image
                                ];
                            }
                        } elseif ($this->imageMode == self::IM_REPLACE) {
                            foreach ($images as $image) {
                                $this->inventory['existing_images'][] = [
                                    'url' => $image
                                ];
                            }
                        }
                    }
                }

                break;

            case 'inventory_id':
                $inventory = Inventory::find($value);

                if ($inventory) {
                    Logger::getLogger('import')->debug("Updating inventory '{$value}' (Id: {$inventory->inventory_id}) via import.");
                    $this->inventory = $inventory;
                } else {
                    return "Unknown inventory identifier '{$value}' found in import.";
                }
                break;

            case 'width':
            case 'height':
            case 'length':
                $unitSuffix = array(
                    '"' => 'inches',
                    'inch' => 'inches',
                    'inches' => 'inches',
                    'in' => 'inches',
                    'in.' => 'inches',
                    '\'' => 'feet',
                    'feet' => 'feet',
                    'foot' => 'feet',
                    'ft' => 'feet',
                    'ft.' => 'feet'
                );

                // convert "3 feet" etc into proper display modes
                if (preg_match("/^([0-9\.,]*) ?('|\"|feet|foot|ft|ft\.|inch|inches|in|in\.)?$/", $value, $matches)) {
                    $parsedValue = $matches[1];

                    if (isset($matches[2])) {
                        $suffix = $matches[2];
                        $parsedSuffix = $unitSuffix[$suffix];
                    } else {
                        $parsedSuffix = 'feet';
                    }

                    $this->inventory[$type] = $parsedSuffix === 'feet' ? $parsedValue : round($parsedValue / 12, 2);
                    $this->inventory[$type . '_display_mode'] = $parsedSuffix;
                } else {
                    // width & length => convert to decimal feet (e.g. 4.66 rather than 4' 8")
                    // Logger::getLogger('import')->debug("[{$filename}, {$row}, {$columnIndex}] - converting '{$value}' to feet (decimal)");
                    $this->inventory[$type] = round($this->convertHelper->toFeetDecimal(str_replace(',', '', $value)), 2);
                    $this->inventory[$type . '_display_mode'] = 'feet';
                }

                if (is_numeric($value)) {
                    $this->inventory[$type] = $value;
                    $this->inventory[$type . '_inches'] = $value * 12;
                }
                break;

            case 'weight':
            case 'gvwr':
            case 'axle_capacity':
                $this->inventory[$type] = round($this->convertHelper->toPoundsDecimal(str_replace(',', '', $value)), 2);
                break;

            // this is dumb how this is handled... this should be able to tell these are all "money", for example
            case 'price':
            case 'sales_price':
            case 'website_price':
            case 'cost_of_unit':
            case 'cost_of_shipping':
            case 'cost_of_prep':
            case 'total_of_cost':
            case 'minimum_selling_price':
                $this->inventory[$type] = floatval(str_replace(array(',', '$', ' '), '', $value));
                break;

            case 'location_phone':
                // lookup location by phone number
                $phone = str_replace(array('(', ')', ' ', '-'), '', $value);
                $dealerLocations = DealerLocation::where([
                    'dealer_id' => $this->bulkUpload->dealer_id
                ]);

                // If no dealerLocation is found by phone, use default dealer location
                $dealerLocation = $dealerLocations->where(function ($query) use ($phone) {
                    $query->where(['phone' => $phone])
                        ->orWhere(['is_default' => 1]);
                })->first();

                // If no location found return default error
                if (is_null($dealerLocation)) {
                    return "Location based on phone number '{$value}' not found and no location has been found for this dealer.";
                }

                $this->inventory['dealer_location_id'] = $dealerLocation->dealer_location_id;
                break;

            case 'axles':
                if ($value >= 3) {
                    $this->inventory[$type] = '3 or more';
                } else {
                    $this->inventory[$type] = $value;
                }
                break;

            case 'description':
                $this->inventory[$type] = strip_tags($value);
                break;
        }

        if (key_exists($type, self::$_attributes)) {
            if (isset(self::$_columnValidation[$type]['list'][strtolower($value)]) && !empty($value)) {
                $this->inventory['attributes'][] = array(
                    'attribute_id' => self::$_attributes[$type],
                    'value' => self::$_columnValidation[$type]['list'][strtolower($value)]
                );
            } elseif (!isset(self::$_columnValidation[$type]['list']) && !empty($value)) {
                $this->inventory['attributes'][] = array(
                    'attribute_id' => self::$_attributes[$type],
                    'value' => $value
                );
            }
        } else {
            if (!isset($this->inventory[$type])) {
                $this->inventory[$type] = $value;
            }
        }

        return false;
    }

    /**
     * @return false|string
     */
    private function outputValidationErrors()
    {
        $jsonEncodedValidationErrors = json_encode($this->validationErrors);
        if (strlen($jsonEncodedValidationErrors) > self::MAX_VALIDATION_ERROR_CHAR_COUNT) {
            $filePath = sprintf(self::S3_VALIDATION_ERRORS_PATH, uniqid() . '.txt');
            Storage::disk('s3')->put($filePath, implode(PHP_EOL, $this->validationErrors));
            return "Log too big, please follow this link to see what failed: " . Storage::disk('s3')->url($filePath);
        }
        return $jsonEncodedValidationErrors;
    }

    /**
     * @param $value
     * @return bool|null
     */
    private static function handleBoolean($value): ?bool
    {
        if (trim($value) == "1" || trim(strtolower($value)) === 'yes' || trim(strtolower($value)) === 'y') {
            return true;
        } elseif (trim($value) == "0" || trim(strtolower($value)) === 'no' || trim(strtolower($value)) === 'n') {
            return false;
        } else {
            $length = strlen(trim($value));
            if ($length === 0) {
                return false;
            }
        }

        return null;
    }
}
