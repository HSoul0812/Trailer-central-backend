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

class ProductJob extends Job
{
    use Dispatchable;

    /**
     * @const int Google Categories
     */
    const GOOGLE_TRAILER = 4027; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Motor Vehicle Trailers
    const GOOGLE_TRAILER_BOAT = 1133; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Motor Vehicle Trailers > Boat Trailers
    const GOOGLE_TRAILER_HORSE = 4037; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Motor Vehicle Trailers > Horse & Livestock Trailers
    const GOOGLE_TRAILER_TRAVEL = 4243; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Motor Vehicle Trailers > Travel Trailers
    const GOOGLE_TRAILER_CARGO = 4044; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Motor Vehicle Trailers > Utility & Cargo Trailers
    const GOOGLE_VEHICLE = 1267; // Vehicles & Parts > Vehicles > Motor Vehicles
    const GOOGLE_VEHICLE_CARS = 916; // Vehicles & Parts > Vehicles > Motor Vehicles > Cars, Trucks & Vans
    const GOOGLE_VEHICLE_GOLF = 3931; // Vehicles & Parts > Vehicles > Motor Vehicles > Golf Carts
    const GOOGLE_VEHICLE_MOTORCYCLE = 919; // Vehicles & Parts > Vehicles > Motor Vehicles > Motorcycles & Scooters
    const GOOGLE_VEHICLE_PART_BOXES = 8378; // Vehicles & Parts > Vehicle Parts & Accessories > Vehicle Storage & Cargo > Truck Bed Storage Boxes & Organizers
    const GOOGLE_SPORT = 503031; // Vehicles & Parts > Vehicles > Motor Vehicles > Off-Road and All-Terrain Vehicles
    const GOOGLE_SPORT_ATVS = 3018; // Vehicles & Parts > Vehicles > Motor Vehicles > Off-Road and All-Terrain Vehicles > ATVs & UTVs
    const GOOGLE_SPORT_GOKART = 2528; // Vehicles & Parts > Vehicles > Motor Vehicles > Off-Road and All-Terrain Vehicles > Go Karts & Dune Buggies
    const GOOGLE_SPORT_RV = 920; // Vehicles & Parts > Vehicles > Motor Vehicles > Recreational Vehicles
    const GOOGLE_SPORT_SNOW = 3549; // Vehicles & Parts > Vehicles > Motor Vehicles > Snowmobiles
    const GOOGLE_BOAT = 3540; // Vehicles & Parts > Vehicles > Watercraft
    const GOOGLE_BOAT_MOTOR = 3095; // Vehicles & Parts > Vehicles > Watercraft > Motor Boats
    const GOOGLE_BOAT_PERSONAL = 1130; // Vehicles & Parts > Vehicles > Watercraft > Personal Watercraft
    const GOOGLE_BOAT_SAIL = 3087; // Vehicles & Parts > Vehicles > Watercraft > Sailboats
    const GOOGLE_BOAT_YACHTS = 5644; // Vehicles & Parts > Vehicles > Watercraft > Yachts
    const GOOGLE_BOAT_PART_DOCK = 3315; // Vehicles & Parts > Vehicle Parts & Accessories > Watercraft Parts & Accessories > Docking & Anchoring
    const GOOGLE_EQUIP = 3798; // Home & Garden > Lawn & Garden > Outdoor Power Equipment
    const GOOGLE_EQUIP_CHAINSAW = 3610; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Chainsaws
    const GOOGLE_EQUIP_GRASS = 2218; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Grass Edgers
    const GOOGLE_EQUIP_HEDGE = 3120; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Hedge Trimmers
    const GOOGLE_EQUIP_DETHATCHER = 500034; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Aerators & Dethatchers
    const GOOGLE_EQUIP_MOWER = 694; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Mowers
    const GOOGLE_EQUIP_MOWER_RIDING = 3311; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Mowers > Riding Mowers
    const GOOGLE_EQUIP_MOWER_ROBOTIC = 6788; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Mowers > Robotic Mowers
    const GOOGLE_EQUIP_MOWER_TOW = 6258; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Mowers > Tow-Behind Mowers
    const GOOGLE_EQUIP_MOWER_BEHIND = 3730; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Mowers > Walk-Behind Mowers
    const GOOGLE_EQUIP_VACUUM = 6789; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Lawn Vacuums
    const GOOGLE_EQUIP_LEAF = 3340; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Leaf Blowers
    const GOOGLE_EQUIP_BASE = 7332; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Outdoor Power Equipment Base Units
    const GOOGLE_EQUIP_SET = 7245; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Outdoor Power Equipment Sets
    const GOOGLE_EQUIP_SWEEPER = 500016; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Power Sweepers
    const GOOGLE_EQUIP_TILLER = 2204; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Power Tillers & Cultivators
    const GOOGLE_EQUIP_WASHER = 1226; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Pressure Washers
    const GOOGLE_EQUIP_SNOW = 1541; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Snow Blowers
    const GOOGLE_EQUIP_TRACTOR = 5866; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Tractors
    const GOOGLE_EQUIP_WEED = 1223; // Home & Garden > Lawn & Garden > Outdoor Power Equipment > Weed Trimmers
    const GOOGLE_EQUIP_PART = 4564; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories
    const GOOGLE_EQUIP_PART_SAW = 4565; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Chainsaw Accessories
    const GOOGLE_EQUIP_PART_SAW_BAR = 4647; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Chainsaw Accessories > Chainsaw Bars
    const GOOGLE_EQUIP_PART_SAW_CHAIN = 4646; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Chainsaw Accessories > Chainsaw Chains
    const GOOGLE_EQUIP_PART_GRASS = 7563; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Grass Edger Accessories
    const GOOGLE_EQUIP_PART_HEDGE = 7265; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Hedge Trimmer Accessories
    const GOOGLE_EQUIP_PART_MOWER = 4566; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories
    const GOOGLE_EQUIP_PART_MOWER_ATTACH = 6542; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Brush Mower Attachments
    const GOOGLE_EQUIP_PART_MOWER_BAG = 4645; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Bags
    const GOOGLE_EQUIP_PART_MOWER_BELT = 4643; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Belts
    const GOOGLE_EQUIP_PART_MOWER_BLADE = 4641; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Blades
    const GOOGLE_EQUIP_PART_MOWER_COVER = 4642; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Covers
    const GOOGLE_EQUIP_PART_MOWER_MULCH = 499923; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Mulch Kits
    const GOOGLE_EQUIP_PART_MOWER_PLUG = 499960; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Mulch Plugs & Plates
    const GOOGLE_EQUIP_PART_MOWER_PULLEY = 4644; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Pulleys & Idlers
    const GOOGLE_EQUIP_PART_MOWER_TUBE = 499872; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Tire Tubes
    const GOOGLE_EQUIP_PART_MOWER_TIRE = 6095; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Tires
    const GOOGLE_EQUIP_PART_MOWER_WHEEL = 6094; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Mower Wheels
    const GOOGLE_EQUIP_PART_MOWER_STRIPING = 499921; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Striping Kits
    const GOOGLE_EQUIP_PART_MOWER_SWEEPER = 6541; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Lawn Mower Accessories > Lawn Sweepers
    const GOOGLE_EQUIP_PART_LEAF = 7168; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Leaf Blower Accessories
    const GOOGLE_EQUIP_PART_LEAF_TUBE = 7171; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Leaf Blower Accessories > Leaf Blower Tubes
    const GOOGLE_EQUIP_PART_ATTACH = 8485; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments
    const GOOGLE_EQUIP_PART_ATTACH_GRASS = 7564; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Grass Edger Attachments
    const GOOGLE_EQUIP_PART_ATTACH_LEAF = 8487; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Ground & Leaf Blower Attachments
    const GOOGLE_EQUIP_PART_ATTACH_HEDGE = 7334; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Hedge Trimmer Attachments
    const GOOGLE_EQUIP_PART_ATTACH_SAW = 8489; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Pole Saw Attachments
    const GOOGLE_EQUIP_PART_ATTACH_TILLER = 8488; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Tiller & Cultivator Attachments
    const GOOGLE_EQUIP_PART_ATTACH_WEED = 7335; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Multifunction Outdoor Power Equipment Attachments > Weed Trimmer Attachments
    const GOOGLE_EQUIP_PART_ATTACH_OUTDOOR = 7333; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Outdoor Power Equipment Batteries
    const GOOGLE_EQUIP_PART_ATTACH_WASHER = 6328; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Pressure Washer Accessories
    const GOOGLE_EQUIP_PART_ATTACH_SNOW = 4567; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Snow Blower Accessories
    const GOOGLE_EQUIP_PART_TRACTOR = 5867; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Tractor Parts & Accessories
    const GOOGLE_EQUIP_PART_TRACTOR_TIRES = 499880; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Tractor Parts & Accessories > Tractor Tires
    const GOOGLE_EQUIP_PART_TRACTOR_WHEELS = 499881; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Tractor Parts & Accessories > Tractor Wheels
    const GOOGLE_EQUIP_PART_WEED = 7169; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Weed Trimmer Accessories
    const GOOGLE_EQUIP_PART_WEED_BLADE = 7170; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Weed Trimmer Accessories > Weed Trimmer Blades & Spools
    const GOOGLE_EQUIP_PART_WEED_COVER = 8034; // Home & Garden > Lawn & Garden > Outdoor Power Equipment Accessories > Weed Trimmer Accessories > Weed Trimmer Spool Covers
    const GOOGLE_HARDWARE_GENERATOR = 1218; // Hardware > Power & Electrical Supplies > Generators

    /**
     * @const array Google Category Map
     */
    const GOOGLE_CAT_MAP = [
        4027 => ['car_racing', 'dump', 'fiber_splicing', 'flatbed', 'ice-fish_house', 'restroom_shower', 'stacker', 'tow_dolly',
                    'vending_concession', 'tank_trailer', 'van_bodies', 'other', 'trailer_fuel', 'dump_bodies', 'truck_bodies', 'tiny_house',
                    'landscape', 'deckover', 'kuv_bodies', 'service_bodies', 'platform_bodies', 'gooseneck_bodies', 'saw_bodies', 'hay',
                    'speciality', 'dump_bin', 'rollster', 'ice_shack', 'semi_flatbed', 'semi_double', 'semi_dryvan', 'semi_lowbow',
                    'semi_reefer', 'semi_grain-hopper', 'semi_tanker', 'semi_dump', 'semi_hopper_trailers', 'semi_curtainside',
                    'semi_container', 'semi_detach', 'semi_other', 'semi_drop', 'semi_tilt', 'semi_drop-van', 'semi_highboy', 'semi_btrain',
                    'semi_bulk', 'semi_dolley', 'semi_livefloor', 'semi_log', 'semi_belt'],
        1137 => ['watercraft', 'boat_trailer'],
        4037 => ['horse', 'semi_horse', 'stock_stock-combo', 'equip_livestock', 'semi_livestock', 'equip_livestock'],
        4243 => ['camping_rv', 'camper_popup', 'camper_aframe', 'camper_teardrop', 'expandable', 'fifth_wheel_campers', 'tent-camper', 'truck_camper'],
        4044 => ['utility', 'utility_side-by-side', 'equip_utility_vehicles', 'cargo_enclosed'],
        1267 => ['vehicle_other'],
        916 => ['vehicle_car', 'vehicle_dump', 'vehicle_commercial', 'vehicle_semi_truck', 'vehicle_passenger_van', 'vehicle_truck', 'vehicle_van',
                'semitruck_dump_truck', 'semitruck_flatbed_truck', 'semitruck_heavy', 'semitruck_highway', 'semitruck_offroad', 'semitruck_other',
                'semitruck_standard', 'semitruck_tanker_truck', 'semitruck_vocational'],
        3931 => ['golf_cart'],
        919 => ['motorcycle', 'vehicle_scooter', 'vehicle_motorcycle', 'vehicle_motorcycle_custom', 'vehicle_motorcycle_dirt',
                'vehicle_motorcycle_dual', 'vehicle_motorcycle_enduro', 'vehicle_motorcycle_performance', 'vehicle_motorcycle_sport',
                'vehicle_motorcycle_street', 'vehicle_motorcycle_super', 'vehicle_motorcycle_touring'],
        3018 => ['atv', 'vehicle_atv', 'utility_side-by-side'],
        2528 => ['sport-go_cart'],
        8378 => ['truck_boxes'],
        920 => ['camping_rv', 'class_a', 'class_b', 'class_bplus', 'class_c', 'park_model', 'rv_other'],
        3549 => ['snowmobile'],
        3540 => ['watercraft_other', 'non_power_boat', 'stabilizer'],
        3095 => ['cruiser_race', 'express_cruiser', 'high_performance_boat', 'inboard_motors', 'jet_boat', 'jet_motors', 'outboard_motors', 'pontoon_boat',
                    'runabout', 'center_console', 'dual_console', 'side_console', 'cuddy_cabin', 'powerboat', 'sport_fishing', 'multihulls'],
        1130 => ['bass_boat', 'bay_boat', 'bowrider', 'canoe-kayak', 'catamaran', 'fishing_boat', 'inflatable', 'inflatable_boat', 'jon_boat', 'paddle_boat',
                    'personal_watercraft', 'row_boat', 'ski_waterboard', 'surf_boat', 'flats_boat'],
        3087 => ['cruiser_sail', 'day_sail', 'sailboat', 'sail_board'],
        5644 => ['houseboat', 'trawler', 'yacht'],
        3315 => ['equip_boat_lifts', 'equip_docks'],
        3798 => ['equipment', 'bed_equipment', 'equip_combine-heads', 'equip_concrete', 'equip_construction', 'equip_farm-ranch', 'equip_grain-handling',
                    'equip_hay_forage', 'equip_material-handling', 'equip_salt_spreader', 'equip_fuel_solutions', 'equip_earth-mover', 'equip_saddle',
                    'equip_racks', 'equip_hitches', 'equip_chemical', 'equip_loader', 'equip_storage_shipping', 'equip_forestry', 'equip_excavators',
                    'equip_telehandlers', 'equip_lifts', 'equip_rollers', 'equip_sweepers', 'equip_dozers', 'equip_trenchers', 'equip_buckets',
                    'equip_forks', 'equip_grapples', 'equip_augers', 'equip_compressors', 'equip_electrical', 'equip_other', 'equip_compaction',
                    'equip_skid', 'equip_miniskid', 'equip_chippers', 'equip_stump', 'equip_log', 'equip_fire', 'equip_conveyor', 'equip_brushcutter',
                    'equip_fabrication', 'equip_welding', 'equip_liftgates', 'equip_ecranes', 'equip_hcranes', 'equip_vcranes', 'equip_hoists',
                    'equip_sbcranes', 'equip_controls', 'equip_accessories', 'equip_carts', 'equip_cabinets', 'equip_drawers', 'equip_tactical',
                    'equip_forwarders', 'equip_ballmounts', 'equip_knuckle-boom-cranes', 'equip_fences', 'equip_backhoes', 'equip_bale_spears',
                    'equip_balers', 'equip_blade_scrapers', 'equip_combine', 'equip_components', 'equip_cultivators', 'equip_disc_harrow',
                    'equip_air_drill', 'equip_fertilizer_applicators', 'equip_fertilizer_tanks', 'equip_ground_care', 'equip_harrows',
                    'equip_harvesting_equipment', 'equip_hay_rakes', 'equip_headers', 'equip_implements', 'equip_irrigation', 'equip_planting_equipment',
                    'equip_seeders', 'equip_sprayers', 'equip_spreaders', 'equip_swathers', 'equip_post_hole_diggers', 'equip_stationary_engines',
                    'equip_trailers', 'equip_utility_vehicles', 'equip_aggregate', 'equip_camps_dorms', 'equip_cranes', 'equip_drilling',
                    'equip_forklifts', 'equip_graders', 'equip_mining', 'equip_rock_gravel', 'equip_scrapers', 'equip_slashers', 'equip_stackers',
                    'equip_trimmers', 'equip_heaters', 'equip_blower', 'equip_handheld', 'equip_rakes', 'equip_blades', 'equip_rotarycutter',
                    'equip_brooms', 'equip_cutter', 'equip_soil', 'equip_posts', 'equip_sulkies', 'equip_bundles', 'equip_ladderacks', 'equip_replacement'],
        3610 => ['equip_chainsaw'],
        3120 => ['equip_trimmers'],
        694 => ['equip_lawn', 'equip_lawn_mowers'],
        6789 => ['equip_vacuum'],
        3340 => ['equip_blower'],
        2204 => ['equip_tillage'],
        1226 => ['equip_power_washer'],
        5866 => ['equip_tractor'],
        1541 => ['equip_plows', 'equip_snow_blower', 'equip_snow_plow'],
        8485 => ['equip_attachment'],
        1218 => ['equip_generator']
    ];

    /**
     * Facebook Availability
     */
    const AVAILABLE = 'in stock';
    const UNAVAILABLE = 'out of stock';
    const ONORDER = 'available for order';
    const PENDING = 'pending';


    /**
     * Facebook Catalog CSV Columns
     *
     * @var type 
     */
    private $csvColumns = array(
        'id',
        'title',
        'description',
        'rich_text_description',
        'availability',
        'condition',
        'visibility',
        'price',
        'sale_price',
        'link',
        'image_link',
        'additional_image_link',
        'brand',
        'color',
        'material',
        'product_type',
        'google_product_category'
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
        Log::channel('facebook')->info('Constructed ProductJob for Catalog #' . $this->integration->catalog_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $log = Log::channel('facebook');
        $log->info('Handling ProductJob for Catalog #' . $this->integration->catalog_id);

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
                $log->error("Exception returned processing listing #" . $listing->id .
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
        // Fix Category
        $category = $this->getGoogleCategory($listing->google_product_category);
        if(!empty($category)) {
            $listing->google_product_category = $category;
        } else {
            unset($listing->google_product_category);
        }

        // Get Inventory URL
        $link = $this->getInventoryUrl($listing->id);
        if(!empty($link)) {
            $listing->link = $link;
        }

        // Encode Images
        if(is_array($listing->additional_image_link)) {
            $listing->additional_image_link = implode(",", $listing->additional_image_link);
        }

        // Fix Availability
        if($listing->availability === '4') {
            $listing->availability = self::PENDING;
        } elseif($listing->availability === '3') {
            $listing->availability = self::ONORDER;
        } elseif($listing->availability === '2') {
            $listing->availability = self::UNAVAILABLE;
        } else {
            $listing->availability = self::AVAILABLE;
        }


        // Append Description
        $listing->description = isset($listing->description) ? trim($listing->description) : '';
        if($listing->real_dealer_id == 8757 && !empty($listing->description)) {
            $listing->description .= 'In some cases, pricing may not include freight, prep, doc/title fees, additional equipment, or sales tax';
        }

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
     * Get Google Category Mapping
     * 
     * @param string $category
     * @return string
     */
    private function getGoogleCategory(string $category): int {
        // Loop Category Types
        foreach(self::GOOGLE_CAT_MAP as $googles => $ours) {
            if(in_array($category, $ours)) {
                return $googles;
            }
        }

        // None Found
        return 0;
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
}
