<?php

namespace App\Jobs\Integration\Facebook;

use App\Jobs\Job;
use App\Models\Inventory\Inventory;
use App\Models\Integration\Facebook\Catalog;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Filesystem\Filesystem;
use League\Csv\Writer;

class CatalogJob extends Job
{
    use Dispatchable;

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
     * @var Catalog $catalog
     */
    private $catalog;

    /**
     * @var array $integration
     */
    private $integration;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Catalog $catalog, \stdclass $integration)
    {
        // Set Catalog/Integration to Process
        $this->catalog = $catalog;
        $this->integration = $integration;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Integration Empty?
        if(empty($this->integration) || empty($this->integration->listings)) {
            // We shouldn't be here if the integration has no listings, but throw an error just in case!
            throw new EmptyCatalogPayloadListingsException;
        }

        // No Feed URL?
        if(empty($this->catalog->feed_path)) {
            throw new MissingCatalogFeedPathException;
        }

        // Create Filename With Headers
        $file = $this->createCsv();

        // Process Integration
        foreach($this->integration->listings as $listing) {
            $this->insertCsvRow($file, $listing);
        }

        // Store Final CSV
        return $this->storeCsv($file, $this->catalog->feed_path);
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
        foreach($this->csvColumns as $column) {
            if(isset($clean->{$column})) {
                $row[$column] = $clean->{$column};
            }
        }

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

        // Append Brand to Manufacturer
        if(isset($listing->brand)) {
            $listing->make .= ' ' . $listing->brand;
            unset($listing->brand);
        }

        // Fix Mileage
        $listing->{'mileage.value'} = $listing->mileage_value;
        $listing->{'mileage.unit'} = $listing->mileage_unit;

        // Encode Images
        if(is_array($listing->image)) {
            $listing->image = json_encode($listing->image);
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
        if($listing->dealer_id == 8757) {
            $listing->description .= 'In some cases, pricing may not include freight, prep, doc/title fees, additional equipment, or sales tax';
        }

        // Return Cleaned Up Listing Array
        return $listing;
    }

    /**
     * Stores CSV on S3 and returns its URL
     *
     * @param string $filename full filename path on S3
     * @param File $file temporary file to store results for
     * @return string
     */
    private function storeCsv($file, $filename) {
        // Get Temp File Contents
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file); // releases the memory (or tempfile)

        // Return Stored File
        return Storage::disk('s3')->put($filename, $csv);
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

        // Website Domain Exists?
        if(!empty($inventory->user->website->domain)) {
            $url = $inventory->getUrl();

            // Domain/URL Exists?
            if(!empty($url)) {
                return 'https://' . $inventory->user->website->domain . $url;
            }
        }

        // Return Empty URL
        return '';
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
            $type = $this->bodyMap[$fuel];
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
}
