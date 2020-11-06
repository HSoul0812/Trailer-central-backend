<?php

namespace App\Jobs\Integration\Facebook;

use App\Jobs\Job;
use Illuminate\Foundation\Bus\Dispatchable;

class CatalogJob extends Job
{
    use Dispatchable;

    /**
     * Specific Dealer ID Overrides
     */
    const SOUTHERN_RV_DEALER_ID = 8338;

    /**
     * Facebook Categories
     */
    const WATERCRAFT = 1295;
    const CARS_TRUCKS = 6001;
    const MOTORCYCLES = 6024;
    const ATVS = 6723;
    const BOATS = 26429;
    const OUTDOOR_EQUIPMENT = 29518;
    const RV_MOTORHOMES = 50054;
    const COMMERCIAL_TRUCKS = 63732;
    const POWERSPORTS = 66466;
    const TRAILERS = 66468;
    const HEAVY_EQUIPMENT = 177641;
    const GOLF_CARTS = 181476;

    /**
     * Facebook Category Map
     *
     * @var type 
     */
    private $categoryMap = array(
        'atv' => self::TRAILERS,
        'bass_boats' => self::BOATS,
        'bed_equipment' => self::COMMERCIAL_TRUCKS,
        'boat_trailer' => self::TRAILERS,
        'camping_rv' => self::RV_MOTORHOMES,
        'canoe-kayak' => self::BOATS,
        'cargo_enclosed' => self::TRAILERS,
        'car_racing' => self::TRAILERS,
        'class_a' => self::RV_MOTORHOMES,
        'class_b' => self::RV_MOTORHOMES,
        'class_c' => self::RV_MOTORHOMES,
        'ice-fish_house' => self::TRAILERS,
        'camper_popup' => self::RV_MOTORHOMES,
        'jon_boat' => self::BOATS,
        'dump' => self::TRAILERS,
        'equipment' => self::TRAILERS,
        'cruiser_race' => self::BOATS,
        'high_performance_boat' => self::BOATS,
        'cruiser_sail' => self::BOATS,
        'express_cruiser' => self::BOATS,
        'deck_boat' => self::BOATS,
        'equip_attachment' => self::HEAVY_EQUIPMENT,
        'equip_farm-ranch' => self::OUTDOOR_EQUIPMENT,
        'equip_fuel_solutions' => self::HEAVY_EQUIPMENT,
        'runabout_boat' => self::BOATS,
        'surf_boat' => self::BOATS,
        'Center Console' => self::BOATS,
        'equip_generator' => self::OUTDOOR_EQUIPMENT,
        'equip_lawn' => self::OUTDOOR_EQUIPMENT,
        'equip_power_washer' => self::HEAVY_EQUIPMENT,
        'equip_salt_spreader' => self::HEAVY_EQUIPMENT,
        'equip_snow_plow' => self::HEAVY_EQUIPMENT,
        'equip_tillage' => self::HEAVY_EQUIPMENT,
        'equip_tractor' => self::HEAVY_EQUIPMENT,
        'equip_combine-heads' => self::HEAVY_EQUIPMENT,
        'equip_farm-ranch' => self::HEAVY_EQUIPMENT,
        'equip_grain-handling' => self::HEAVY_EQUIPMENT,
        'equip_hay_forage' => self::HEAVY_EQUIPMENT,
        'equip_lawn' => self::HEAVY_EQUIPMENT,
        'equip_livestock' => self::HEAVY_EQUIPMENT,
        'bowrider' => self::BOATS,
        'equip_material-handling' => self::HEAVY_EQUIPMENT,
        'sport_fishing' => self::BOATS,
        'yacht' => self::BOATS,
        'expandable' => self::RV_MOTORHOMES,
        'ski_waterboard' => self::BOATS,
        'fifth_wheel_campers' => self::TRAILERS,
        'fishing_boat' => self::BOATS,
        'flatbed' => self::TRAILERS,
        'golf_cart' => self::GOLF_CARTS,
        'horse' => self::TRAILERS,
        'inflatable' => self::BOATS,
        'semi_dump' => self::COMMERCIAL_TRUCKS,
        'park_model' => self::RV_MOTORHOMES,
        'semi_grain-hopper' => self::COMMERCIAL_TRUCKS,
        'semi_tanker' => self::COMMERCIAL_TRUCKS,
        'semi_hopper_trailers' => self::COMMERCIAL_TRUCKS,
        'fifth_wheel_campers' => self::RV_MOTORHOMES,
        'destination_trailer' => self::RV_MOTORHOMES,
        'vehicle_snowmobile' => self::POWERSPORTS,
        'motorcycle' => self::TRAILERS,
        'utility_side-by-side' => self::ATVS,
        'vehicle_scooter' => self::MOTORCYCLES,
        'outboard_motors' => self::BOATS,
        'semitruck_standard' => self::COMMERCIAL_TRUCKS,
        'semitruck_tanker_truck' => self::COMMERCIAL_TRUCKS,
        'semitruck_flatbed_truck' => self::COMMERCIAL_TRUCKS,
        'semitruck_dump_truck' => self::COMMERCIAL_TRUCKS,
        'vehicle_upright' => self::POWERSPORTS,
        'other' => self::COMMERCIAL_TRUCKS,
        'park-model' => self::TRAILERS,
        'personal_watercraft' => self::WATERCRAFT,
        'equip_snow_blower' => self::HEAVY_EQUIPMENT,
        'pontoon_boat' => self::BOATS,
        'powerboat' => self::BOATS,
        'equip_earth-mover' => self::HEAVY_EQUIPMENT,
        'restroom_shower' => self::TRAILERS,
        'sailboat' => self::BOATS,
        'semi_dryvan' => self::COMMERCIAL_TRUCKS,
        'semi_double' => self::COMMERCIAL_TRUCKS,
        'semi_flatbed' => self::COMMERCIAL_TRUCKS,
        'semi_lowboy' => self::COMMERCIAL_TRUCKS,
        'semi_livestock' => self::COMMERCIAL_TRUCKS,
        'semi_reefer' => self::COMMERCIAL_TRUCKS,
        'snowmobile' => self::TRAILERS,
        'sport-go_cart' => self::POWERSPORTS,
        'sport_side-by-side' => self::ATVS,
        'stacker' => self::TRAILERS,
        'Stock' => self::TRAILERS,
        'stock-stock_combo' => self::TRAILERS,
        'stock_stock-combo' => self::TRAILERS,
        'tent-camper' => self::RV_MOTORHOMES,
        'trailer_ice_fish' => self::TRAILERS,
        'truck_boxes' => self::TRAILERS,
        'truck_camper' => self::RV_MOTORHOMES,
        'tow_dolly' => self::TRAILERS,
        'toy' => self::RV_MOTORHOMES,
        'utility' => self::TRAILERS,
        'utility_side-by-side' => self::CARS_TRUCKS,
        'vehicle_atv' => self::ATVS,
        'vehicle_car' => self::CARS_TRUCKS,
        'vehicle_motorcycle' => self::MOTORCYCLES,
        'vehicle_scooter' => self::MOTORCYCLES,
        'vehicle_suv' => self::CARS_TRUCKS,
        'vehicle_truck' => self::CARS_TRUCKS,
        'vending_concession' => self::TRAILERS,
        'vehicle_semi_truck' => self::COMMERCIAL_TRUCKS,
        'watercraft' => self::TRAILERS,
        '' => self::TRAILERS
    );

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
