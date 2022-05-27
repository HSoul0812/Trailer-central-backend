<?php

namespace App\Jobs\Integration\Facebook\Catalog;

use App\Exceptions\Integration\Facebook\EmptyCatalogPayloadListingsException;
use App\Exceptions\Integration\Facebook\FailedCreateTempCatalogCsvException;
use App\Exceptions\Integration\Facebook\MissingCatalogFeedPathException;
use App\Jobs\Job;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VehicleJob extends Job
{
    use Dispatchable;

    /**
     * Specific Data Types
     */
    const TC_PRIVACY_POLICY_URL = 'https://trailercentral.com/privacy-policy/';

    /**
     * Default Inventory URL
     */
    const DEFAULT_INVENTORY_DOMAIN = 'https://trailertrader.com';

    /**
     * Facebook Vehicle Types
     */
    const BOAT = 'BOAT';
    const CAR = 'CAR_TRUCK';
    const COMMERCIAL = 'COMMERCIAL';
    const CYCLE = 'MOTORCYCLE';
    const SPORT = 'POWERSPORT';
    const CAMPER = 'RV_CAMPER';
    const TRAILER = 'TRAILER';

    /**
     * Facebook Body Styles
     */
    const CONVERTIBLE = 'CONVERTIBLE';
    const COUPE = 'COUPE';
    const CROSSOVER = 'CROSSOVER';
    const HATCHBACK = 'HATCHBACK';
    const MINIVAN = 'MINIVAN';
    const TRUCK = 'TRUCK';
    const SEDAN = 'SEDAN';
    const SMALL = 'SMALL_CAR';
    const SUV = 'SUV';
    const VAN = 'VAN';
    const WAGON = 'WAGON';
    const OTHER = 'OTHER';

    /**
     * Facebook Availability
     */
    const AVAILABLE = 'AVAILABLE';
    const UNAVAILABLE = 'NOT_AVAILABLE';
    const PENDING = 'PENDING';

    /**
     * Vehicle Type Map
     *
     * @var type 
     */
    private $vehicleMap = array(
        'atv' => self::TRAILER,
        'bass_boats' => self::BOAT,
        'bed_equipment' => self::COMMERCIAL,
        'boat_trailer' => self::TRAILER,
        'camping_rv' => self::CAMPER,
        'canoe-kayak' => self::BOAT,
        'cargo_enclosed' => self::TRAILER,
        'car_racing' => self::TRAILER,
        'class_a' => self::CAMPER,
        'class_b' => self::CAMPER,
        'class_c' => self::CAMPER,
        'ice-fish_house' => self::TRAILER,
        'camper_popup' => self::CAMPER,
        'jon_boat' => self::BOAT,
        'dump' => self::TRAILER,
        'equipment' => self::TRAILER,
        'cruiser_race' => self::BOAT,
        'high_performance_boat' => self::BOAT,
        'cruiser_sail' => self::BOAT,
        'express_cruiser' => self::BOAT,
        'deck_boat' => self::BOAT,
        'equip_attachment' => self::COMMERCIAL,
        'equip_farm-ranch' => self::COMMERCIAL,
        'equip_fuel_solutions' => self::COMMERCIAL,
        'runabout_boat' => self::BOAT,
        'surf_boat' => self::BOAT,
        'Center Console' => self::BOAT,
        'equip_generator' => self::COMMERCIAL,
        'equip_lawn' => self::COMMERCIAL,
        'equip_power_washer' => self::COMMERCIAL,
        'equip_salt_spreader' => self::COMMERCIAL,
        'equip_snow_plow' => self::COMMERCIAL,
        'equip_tillage' => self::COMMERCIAL,
        'equip_tractor' => self::COMMERCIAL,
        'equip_combine-heads' => self::COMMERCIAL,
        'equip_farm-ranch' => self::COMMERCIAL,
        'equip_grain-handling' => self::COMMERCIAL,
        'equip_hay_forage' => self::COMMERCIAL,
        'equip_lawn' => self::COMMERCIAL,
        'equip_livestock' => self::COMMERCIAL,
        'bowrider' => self::BOAT,
        'equip_material-handling' => self::COMMERCIAL,
        'sport_fishing' => self::BOAT,
        'yacht' => self::BOAT,
        'expandable' => self::CAMPER,
        'ski_waterboard' => self::BOAT,
        'fifth_wheel_campers' => self::TRAILER,
        'fishing_boat' => self::BOAT,
        'flatbed' => self::TRAILER,
        'golf_cart' => self::SPORT,
        'horse' => self::TRAILER,
        'inflatable' => self::BOAT,
        'semi_dump' => self::COMMERCIAL,
        'park_model' => self::CAMPER,
        'semi_grain-hopper' => self::COMMERCIAL,
        'semi_tanker' => self::COMMERCIAL,
        'semi_hopper_trailers' => self::COMMERCIAL,
        'fifth_wheel_campers' => self::CAMPER,
        'destination_trailer' => self::CAMPER,
        'vehicle_snowmobile' => self::SPORT,
        'motorcycle' => self::TRAILER,
        'utility_side-by-side' => self::SPORT,
        'vehicle_scooter' => self::CYCLE,
        'outboard_motors' => self::BOAT,
        'semitruck_standard' => self::COMMERCIAL,
        'semitruck_tanker_truck' => self::COMMERCIAL,
        'semitruck_flatbed_truck' => self::COMMERCIAL,
        'semitruck_dump_truck' => self::COMMERCIAL,
        'vehicle_upright' => self::SPORT,
        'other' => self::COMMERCIAL,
        'park-model' => self::TRAILER,
        'personal_watercraft' => self::BOAT,
        'equip_snow_blower' => self::COMMERCIAL,
        'pontoon_boat' => self::BOAT,
        'powerboat' => self::BOAT,
        'equip_earth-mover' => self::COMMERCIAL,
        'restroom_shower' => self::TRAILER,
        'sailboat' => self::BOAT,
        'semi_dryvan' => self::COMMERCIAL,
        'semi_double' => self::COMMERCIAL,
        'semi_flatbed' => self::COMMERCIAL,
        'semi_lowboy' => self::COMMERCIAL,
        'semi_livestock' => self::COMMERCIAL,
        'semi_reefer' => self::COMMERCIAL,
        'snowmobile' => self::TRAILER,
        'sport-go_cart' => self::SPORT,
        'sport_side-by-side' => self::SPORT,
        'stacker' => self::TRAILER,
        'Stock' => self::TRAILER,
        'stock-stock_combo' => self::TRAILER,
        'stock_stock-combo' => self::TRAILER,
        'tent-camper' => self::CAMPER,
        'trailer_ice_fish' => self::TRAILER,
        'truck_boxes' => self::TRAILER,
        'truck_camper' => self::CAMPER,
        'tow_dolly' => self::TRAILER,
        'toy' => self::CAMPER,
        'utility' => self::TRAILER,
        'utility_side-by-side' => self::SPORT,
        'vehicle_atv' => self::SPORT,
        'vehicle_car' => self::CAR,
        'vehicle_motorcycle' => self::CYCLE,
        'vehicle_scooter' => self::CYCLE,
        'vehicle_suv' => self::CAR,
        'vehicle_truck' => self::CAR,
        'vending_concession' => self::TRAILER,
        'vehicle_semi_truck' => self::COMMERCIAL,
        'watercraft' => self::TRAILER,
        '' => self::TRAILER
    );

    /**
     * Body Map
     *
     * @var type 
     */
    private $bodyMap = array(
        'vehicle_car' => self::OTHER,
        'vehicle_motorcycle' => self::OTHER,
        'vehicle_scooter' => self::OTHER,
        'vehicle_suv' => self::SUV,
        'vehicle_truck' => self::TRUCK,
        'vehicle_semi_truck' => self::TRUCK,
    );

    /**
     * Facebook Fuel Map
     * 
     * @var array
     */
    private $fuelMap = [
        'Gas' => 'GASOLINE',
        'Electric' => 'ELECTRIC',
        'Diesel' => 'DIESEL',
        'FlexFuel' => 'FLEX'
    ];
    
    /**
     * Facebook Drive Train Map
     * 
     * @var array
     */
    private $driveMap = [
        'all_wheel_drive' => 'AWD',
        'front_wheel_drive' => 'FWD',
        'rear_wheel_drive' => 'RWD'
    ];

    /**
     * Facebook Catalog CSV Columns
     *
     * @var type 
     */
    private $csvColumns = array(
        'fb_page_id',
        'vehicle_id',
        'title',
        'description',
        'url',
        'make',
        'model',
        'year',
        'mileage.value',
        'mileage.unit',
        'transmission',
        'image',
        'body_style',
        'vin',
        'price',
        'exterior_color',
        'state_of_vehicle',
        'fuel_type',
        'condition',
        'sale_price',
        'availability',
        'vehicle_type',
        'stock_number',
        'dealer_id',
        'dealer_name',
        'dealer_phone',
        'address',
        'dealer_communication_channel',
        'dealer_privacy_policy_url'
    );


    /**
     * @var string $feedPath
     */
    private $feedPath;

    /**
     * @var array $integration
     */
    private $integration;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdclass $integration, string $feedPath)
    {
        // Set Feed Path/Integration to Process
        $this->feedPath = $feedPath;
        $this->integration = $integration;

        // Log Construct
        Log::channel('facebook')->info('Constructed VehicleJob for Catalog #' . $this->integration->catalog_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $log = Log::channel('facebook');
        $log->info('Handling VehicleJob for Catalog #' . $this->integration->catalog_id);

        // Integration Empty?
        if(empty($this->integration) || empty($this->integration->listings)) {
            // We shouldn't be here if the integration has no listings, but throw an error just in case!
            throw new EmptyCatalogPayloadListingsException;
        }

        // No Feed URL?
        if(empty($this->feedPath)) {
            throw new MissingCatalogFeedPathException;
        }

        // Create Filename With Headers
        $file = $this->createCsv();

        // Process Integration
        $log->info('Inserting ' . count($this->integration->listings) . ' Listings Into CSV File ' . $this->feedPath);
        foreach($this->integration->listings as $listing) {
            try {
                $this->insertCsvRow($file, $listing);
            } catch(\Exception $e) {
                $log->error("Exception returned processing listing #" . $listing->vehicle_id .
                            " on catalog # " . $this->integration->catalog_id . "; " . 
                            $e->getMessage() . ": " . $e->getTraceAsString());
            }
        }

        // Store Final CSV
        return $this->storeCsv($file, $this->feedPath);
    }


    /**
     * Create Temp CSV With Headers
     * 
     * @return File
     */
    private function createCsv() {
        // Create File
        $file = fopen('php://temp/maxmemory:1048576', 'w');
        if($file === FALSE) {
            throw new FailedCreateTempCatalogCsvException;
        }

        // Add Headers
        fputcsv($file, $this->csvColumns);

        // Return Temp CSV File
        return $file;
    }

    /**
     * Insert CSV Row
     * 
     * @return File
     */
    private function insertCsvRow($file, $listing) {
        // Clean Up Results
        $clean = $this->cleanCsvRow($listing);

        // Create Row
        $row = array();
        foreach($this->csvColumns as $k => $column) {
            $row[$k] = '';
            if(isset($clean->{$column}) && $clean->{$column} !== null) {
                $row[$k] = $clean->{$column};
            }
        }
        ksort($row);

        // Add Cleaned Results to CSV
        fputcsv($file, $row);

        // Return Result
        return $file;
    }

    /**
     * Get Cleaned CSV Row
     * 
     * @param array $listing
     * @return array of cleaned listing results
     */
    private function cleanCsvRow($listing) {
        // Get Inventory URL
        $listing->url = $this->getInventoryUrl($listing->vehicle_id);

        // Format Phone Number
        $listing->dealer_phone = $this->formatDealerPhone($listing->dealer_phone);

        // Append Brand to Manufacturer
        if(isset($listing->brand)) {
            $listing->make .= ' ' . $listing->brand;
            unset($listing->brand);
        }

        // Fix Mileage
        $listing->{'mileage.value'} = !empty($listing->mileage_value) ? $listing->mileage_value : '0';
        $listing->{'mileage.unit'} = $listing->mileage_unit;

        // Encode Images
        if(is_array($listing->image)) {
            $listing->image = json_encode($listing->image);
        }

        // Fix Availability
        if($listing->availability === '2') {
            $listing->availability = self::UNAVAILABLE;
        } elseif($listing->availability === '4') {
            $listing->availability = self::PENDING;
        } elseif($listing->availability === '2') {
            $listing->availability = self::UNAVAILABLE;
        } else {
            $listing->availability = self::AVAILABLE;
        }

        // Fix Transmission
        if(empty($listing->transmission)) {
            $listing->transmission = 'other';
        }
        $listing->transmission = strtoupper($listing->transmission);

        // Handle Mapping
        $listing->vehicle_type = $this->mapVehicleType($listing->vehicle_type);
        $listing->body_style = $this->mapBodyStyle($listing->body_style);
        $listing->drivetrain = $this->mapDriveTrain($listing->drivetrain);
        $listing->fuel_type = $this->mapFuelType($listing->fuel_type);

        // Fix State of Vehicle
        if(!empty($listing->state_of_vehicle) && $listing->state_of_vehicle !== 'New') {
            $listing->state_of_vehicle = 'Used';
        } else {
            $listing->state_of_vehicle = 'New';
        }

        // Handle Address
        $listing->address = json_encode([
            'addr1' => $listing->address_addr1,
            'city' => $listing->address_city,
            'region' => $listing->address_region,
            'country' => $listing->address_country,
            'postal' => $listing->address_postal
        ]);

        // Append Description
        $listing->description = isset($listing->description) ? trim($listing->description) : '';
        if($listing->real_dealer_id == 8757 && !empty($listing->description)) {
            $listing->description .= 'In some cases, pricing may not include freight, prep, doc/title fees, additional equipment, or sales tax';
        }

        // Fix Privacy Policy
        $listing->dealer_privacy_policy_url = $this->getPrivacyPolicyUrl($listing->vehicle_id);

        // Return Cleaned Up Listing Array
        return $listing;
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param File $file temporary file to store results for
     * @param string $filePath full filename path on S3
     * @return string
     */
    private function storeCsv($file, $filePath) {
        $log = Log::channel('facebook');
        try {
            // Get Temp File Contents
            rewind($file);
            $csv = stream_get_contents($file);
            fclose($file); // releases the memory (or tempfile)
        } catch(\Exception $e) {
            $log->error('Exception returned loading CSV ' . $file . ' contents: ' . $e->getMessage());
        }

        // Return Stored File
        try {
            $saved = Storage::disk('s3')->put($filePath, $csv);
        } catch(\Exception $e) {
            $log->error('Exception returned sending file ' . $filePath . ' to S3: ' . $e->getMessage());
        }

        // Inserted File
        $log->info('Inserted ' . count($this->integration->listings) . ' Listings Into CSV File ' . $filePath);

        // Return File
        return $saved;
    }


    /**
     * Fix Formatting Dealer Phone Correctly
     * 
     * @param string $phone
     * @return formatted dealer phone
     */
    private function formatDealerPhone($phone) {
        // Get Dealer Phone
        $clean = trim(preg_replace('/[^0-9]/', '', $phone));

        // Normal Phone Number
        if(\strlen($clean) === 10) {
            $clean = '+1 ' . $clean;
        }
        // Phone With Starting 1
        elseif(\strlen($clean) === 11) {
            $clean = '+1 ' . substr($clean, 1);
        }

        // Return Clean With + at Start
        return $clean;
    }

    /**
     * Get Inventory URL From Inventory ID
     * 
     * @param int $inventoryId
     * @return return privacy policy url
     */
    private function getPrivacyPolicyUrl($inventoryId) {
        return self::TC_PRIVACY_POLICY_URL;
    }

    /**
     * Get Inventory URL From Inventory ID
     * 
     * @param int $inventoryId
     * @return string direct link to exact domain inventory URL
     */
    private function getInventoryUrl($inventoryId) {
        // Get Inventory Item for Vehicle
        $inventory = Inventory::find($inventoryId);

        // Get URL
        $url = $inventory->getUrl();

        // Website Domain Exists?
        if(!empty($inventory->user->website->domain)) {
            // Return Website Domain
            return 'https://' . $inventory->user->website->domain . $url;
        }

        // Use Default Domain Instead?
        if(!empty($url) && $inventory->show_on_website && $inventory->user->clsf_active) {
            return self::DEFAULT_INVENTORY_DOMAIN . $url;
        }

        // Return Empty URL
        return $this->getDefaultInventoryDomain();
    }


    /**
     * Map Vehicle Type Based on Category
     * 
     * @param string $cat
     * @return string final vehicle type
     */
    private function mapVehicleType($cat) {
        // Set Default Type
        $type = self::TRAILER;

        // Check Mapping
        if(isset($this->vehicleMap[$cat])) {
            $type = $this->vehicleMap[$cat];
        }

        // Return Result
        return $type;
    }

    /**
     * Map Body Style Based on Category
     * 
     * @param string $cat
     * @return string final body style
     */
    private function mapBodyStyle($cat) {
        // Set Default Type
        $style = 'OTHER';

        // Check Mapping
        if(isset($this->bodyMap[$cat])) {
            $style = $this->bodyMap[$cat];
        }

        // Return Result
        return $style;
    }

    /**
     * Map Fuel Type Based on Existing Fuel Type
     * 
     * @param string $fuel
     * @return string final fuel type
     */
    private function mapFuelType($fuel) {
        // Set Default Type
        $type = 'OTHER';

        // Check Mapping
        if(isset($this->fuelMap[$fuel])) {
            $type = $this->fuelMap[$fuel];
        }

        // Return Result
        return $type;
    }

    /**
     * Map Drive Train
     * 
     * @param string $drive
     * @return string final drive train
     */
    private function mapDriveTrain($drive) {
        // Set Default Type
        $train = 'OTHER';

        // Check Mapping
        if(isset($this->driveMap[$drive])) {
            $train = $this->driveMap[$drive];
        }

        // Return Result
        return $train;
    }


    /**
     * Get Default Inventory Domain
     * 
     * @return string default inventory domain
     */
    private function getDefaultInventoryDomain() {
        // Get Environment Variables
        $domain = config('oauth.fb.catalog.domain');
        if(!empty($domain)) {
            return $domain;
        }

        // Return Default Inventory Domain
        return self::DEFAULT_INVENTORY_DOMAIN;
    }
}
