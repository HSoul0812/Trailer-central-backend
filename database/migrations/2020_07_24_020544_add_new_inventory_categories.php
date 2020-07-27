<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewInventoryCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('inventory_category')->truncate();

        DB::table('inventory_category')->insertOrIgnore([
            // Trailer Category
            ['entity_type_id' => 1, 'category' => 'trailer.atv', 'label' => 'ATV Trailer', 'legacy_category' => 'atv'],
            ['entity_type_id' => 1, 'category' => 'trailer.boat_trailer', 'label' => 'Boat Trailer', 'legacy_category' => 'boat_trailer'],
            ['entity_type_id' => 1, 'category' => 'trailer.cargo_enclosed', 'label' => 'Cargo / Enclosed Trailer', 'legacy_category' => 'cargo_enclosed'],
            ['entity_type_id' => 1, 'category' => 'trailer.car_racing', 'label' => 'Car / Racing Trailer', 'legacy_category' => 'car_racing'],
            ['entity_type_id' => 1, 'category' => 'trailer.dump', 'label' => 'Dump Trailer', 'legacy_category' => 'dump'],
            ['entity_type_id' => 1, 'category' => 'trailer.equipment', 'label' => 'Equipment Trailer', 'legacy_category' => 'equipment'],
            ['entity_type_id' => 1, 'category' => 'trailer.fiber_splicing', 'label' => 'Fiber Splicing', 'legacy_category' => 'fiber_splicing'],
            ['entity_type_id' => 1, 'category' => 'trailer.flatbed', 'label' => 'Flatbed Trailer', 'legacy_category' => 'flatbed'],
            ['entity_type_id' => 1, 'category' => 'trailer.ice-fish_house', 'label' => 'Ice/Fish House Trailer', 'legacy_category' => 'ice-fish_house'],
            ['entity_type_id' => 1, 'category' => 'trailer.motorcycle', 'label' => 'Motorcycle Trailer', 'legacy_category' => 'motorcycle'],
            ['entity_type_id' => 1, 'category' => 'trailer.restroom_shower', 'label' => 'Restroom / Shower Trailer', 'legacy_category' => 'restroom_shower'],
            ['entity_type_id' => 1, 'category' => 'trailer.snowmobile', 'label' => 'Snowmobile Trailer', 'legacy_category' => 'snowmobile'],
            ['entity_type_id' => 1, 'category' => 'trailer.stacker', 'label' => 'Stacker', 'legacy_category' => 'stacker'],
            ['entity_type_id' => 1, 'category' => 'trailer.stock_stock-combo', 'label' => 'Stock / Stock Combo Trailer', 'legacy_category' => 'stock_stock-combo'],
            ['entity_type_id' => 1, 'category' => 'trailer.tow_dolly', 'label' => 'Tow Dolly', 'legacy_category' => 'tow_dolly'],
            ['entity_type_id' => 1, 'category' => 'trailer.bed_equipment', 'label' => 'Truck Bed', 'legacy_category' => 'bed_equipment'],
            ['entity_type_id' => 1, 'category' => 'trailer.truck_boxes', 'label' => 'Truck Boxes (Livestock and Dog)', 'legacy_category' => 'truck_boxes'],
            ['entity_type_id' => 1, 'category' => 'trailer.utility', 'label' => 'Utility Trailer', 'legacy_category' => 'utility'],
            ['entity_type_id' => 1, 'category' => 'trailer.vending_concession', 'label' => 'Vending / Concession Trailer', 'legacy_category' => 'vending_concession'],
            ['entity_type_id' => 1, 'category' => 'trailer.watercraft', 'label' => 'Watercraft Trailer', 'legacy_category' => 'watercraft'],
            ['entity_type_id' => 1, 'category' => 'trailer.tank_trailer', 'label' => 'Tank Trailer', 'legacy_category' => 'tank_trailer'],
            ['entity_type_id' => 1, 'category' => 'trailer.van_bodies', 'label' => 'Van Bodies', 'legacy_category' => 'van_bodies'],
            ['entity_type_id' => 1, 'category' => 'trailer.other', 'label' => 'Other Trailer', 'legacy_category' => 'other'],

            // RV Category
            ['entity_type_id' => 3, 'category' => 'rv.class_a', 'label' => 'Class A', 'legacy_category' => 'class_a'],
            ['entity_type_id' => 3, 'category' => 'rv.class_b', 'label' => 'Class B', 'legacy_category' => 'class_b'],
            ['entity_type_id' => 3, 'category' => 'rv.class_c', 'label' => 'Class C', 'legacy_category' => 'class_c'],
            ['entity_type_id' => 3, 'category' => 'rv.camper_popup', 'label' => 'Popup Camper', 'legacy_category' => 'camper_popup'],
            ['entity_type_id' => 3, 'category' => 'rv.tent-camper', 'label' => 'Tent Camper', 'legacy_category' => 'tent-camper'],
            ['entity_type_id' => 3, 'category' => 'rv.toy', 'label' => 'Toy Hauler', 'legacy_category' => 'toy'],
            ['entity_type_id' => 3, 'category' => 'rv.camping_rv', 'label' => 'Travel Trailer', 'legacy_category' => 'camping_rv'],
            ['entity_type_id' => 3, 'category' => 'rv.truck_camper', 'label' => 'Truck Bed Camper', 'legacy_category' => 'truck_camper'],
            ['entity_type_id' => 3, 'category' => 'rv.expandable', 'label' => 'Expandable Camper Trailer', 'legacy_category' => 'expandable'],
            ['entity_type_id' => 3, 'category' => 'rv.destination_trailer', 'label' => 'Destination Trailer', 'legacy_category' => 'destination_trailer'],
            ['entity_type_id' => 3, 'category' => 'rv.fifth_wheel_campers', 'label' => 'Fifth Wheel Campers', 'legacy_category' => 'fifth_wheel_campers'],
            ['entity_type_id' => 3, 'category' => 'rv.park_model', 'label' => 'Park Model', 'legacy_category' => 'park_model'],

            // Vehicle Category
            ['entity_type_id' => 4, 'category' => 'vehicle.vehicle_car', 'label' => 'Car', 'legacy_category' => 'vehicle_car'],
            ['entity_type_id' => 4, 'category' => 'vehicle.vehicle_motorcycle', 'label' => 'Motorcycle', 'legacy_category' => 'vehicle_motorcycle'],
            ['entity_type_id' => 4, 'category' => 'vehicle.vehicle_truck', 'label' => 'Truck', 'legacy_category' => 'vehicle_truck'],
            ['entity_type_id' => 4, 'category' => 'vehicle.vehicle_suv', 'label' => 'SUV', 'legacy_category' => 'vehicle_suv'],
            ['entity_type_id' => 4, 'category' => 'vehicle.vehicle_semi_truck', 'label' => 'Semi Truck', 'legacy_category' => 'vehicle_semi_truck'],

            // Sports Vehicle Category
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.vehicle_atv', 'label' => 'ATV', 'legacy_category' => 'vehicle_atv'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.sport-go_cart', 'label' => 'Go Cart', 'legacy_category' => 'sport-go_cart'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.golf_cart', 'label' => 'Golf Cart', 'legacy_category' => 'golf_cart'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.sport_side-by-side', 'label' => 'Sport Side-by-Side', 'legacy_category' => 'sport_side-by-side'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.utility_side-by-side', 'label' => 'Utility Side-by-Side (UTV)', 'legacy_category' => 'utility_side-by-side'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.vehicle_scooter', 'label' => 'Scooter', 'legacy_category' => 'vehicle_scooter'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.vehicle_snowmobile', 'label' => 'Snowmobile Vehicle', 'legacy_category' => 'vehicle_snowmobile'],
            ['entity_type_id' => 8, 'category' => 'sportsvehicle.vehicle_upright', 'label' => 'Upright Vehicle', 'legacy_category' => 'vehicle_upright'],

            // Watercraft Category
            ['entity_type_id' => 5, 'category' => 'watercraft.personal_watercraft', 'label' => 'PWC (Personal Watercraft)', 'legacy_category' => 'personal_watercraft'],
            ['entity_type_id' => 5, 'category' => 'watercraft.bass_boat', 'label' => 'Bass Boat', 'legacy_category' => 'bass_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.canoe-kayak', 'label' => 'Canoe / Kayak', 'legacy_category' => 'canoe-kayak'],
            ['entity_type_id' => 5, 'category' => 'watercraft.fishing_boat', 'label' => 'Fishing Boat', 'legacy_category' => 'fishing_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.inflatable', 'label' => 'Dinghy/Inflatable', 'legacy_category' => 'inflatable'],
            ['entity_type_id' => 5, 'category' => 'watercraft.powerboat', 'label' => 'Power Boat', 'legacy_category' => 'powerboat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.sailboat', 'label' => 'Sailboat', 'legacy_category' => 'sailboat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.pontoon_boat', 'label' => 'Pontoon Boat', 'legacy_category' => 'pontoon_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.outboard_motors', 'label' => 'Outboard Motors', 'legacy_category' => 'outboard_motors'],
            ['entity_type_id' => 5, 'category' => 'watercraft.deck_boat', 'label' => 'Deck Boat', 'legacy_category' => 'deck_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.high_performance_boat', 'label' => 'High Performance Boat', 'legacy_category' => 'high_performance_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.surf_boat', 'label' => 'Surf Boat', 'legacy_category' => 'surf_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.runabout_boat', 'label' => 'Runabout Boat', 'legacy_category' => 'runabout_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.express_cruiser', 'label' => 'Express/Cruiser', 'legacy_category' => 'express_cruiser'],
            ['entity_type_id' => 5, 'category' => 'watercraft.cruiser_sail', 'label' => 'Cruiser (Sail)', 'legacy_category' => 'cruiser_sail'],
            ['entity_type_id' => 5, 'category' => 'watercraft.cruiser_race', 'label' => 'Cruiser (Power)', 'legacy_category' => 'cruiser_race'],
            ['entity_type_id' => 5, 'category' => 'watercraft.center_console', 'label' => 'Center Console', 'legacy_category' => 'center_console'],
            ['entity_type_id' => 5, 'category' => 'watercraft.jon_boat', 'label' => 'Jon Boat', 'legacy_category' => 'jon_boat'],
            ['entity_type_id' => 5, 'category' => 'watercraft.bowrider', 'label' => 'Bowrider', 'legacy_category' => 'bowrider'],
            ['entity_type_id' => 5, 'category' => 'watercraft.yacht', 'label' => 'Yacht', 'legacy_category' => 'yacht'],
            ['entity_type_id' => 5, 'category' => 'watercraft.ski_waterboard', 'label' => 'Ski/Wakeboard', 'legacy_category' => 'ski_waterboard'],
            ['entity_type_id' => 5, 'category' => 'watercraft.sport_fishing', 'label' => 'Sport Fishing', 'legacy_category' => 'sport_fishing'],
            ['entity_type_id' => 5, 'category' => 'watercraft.jet_boat', 'label' => 'Jet Boat', 'legacy_category' => 'jet_boat'],

            // Equipment Category
            ['entity_type_id' => 6, 'category' => 'equipment.equip_attachment', 'label' => 'Attachment', 'legacy_category' => 'equip_attachment'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_combine-heads', 'label' => 'Combine Heads', 'legacy_category' => 'equip_combine-heads'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_concrete', 'label' => 'Concrete', 'legacy_category' => 'equip_concrete'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_construction', 'label' => 'Construction', 'legacy_category' => 'equip_construction'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_farm-ranch', 'label' => 'Farm / Ranch', 'legacy_category' => 'equip_farm-ranch'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_grain-handling', 'label' => 'Grain Handling', 'legacy_category' => 'equip_grain-handling'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_hay_forage', 'label' => 'Hay / Forage', 'legacy_category' => 'equip_hay_forage'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_lawn', 'label' => 'Lawn Equipment', 'legacy_category' => 'equip_lawn'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_lawn_mowers', 'label' => 'Lawn Mowers', 'legacy_category' => 'equip_lawn_mowers'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_livestock', 'label' => 'Livestock', 'legacy_category' => 'equip_livestock'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_material-handling', 'label' => 'Material Handling', 'legacy_category' => 'equip_material-handling'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_salt_spreader', 'label' => 'Salt Spreader', 'legacy_category' => 'equip_salt_spreader'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_snow_plow', 'label' => 'Snow Plow', 'legacy_category' => 'equip_snow_plow'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_tillage', 'label' => 'Tillage', 'legacy_category' => 'equip_tillage'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_tractor', 'label' => 'Tractor', 'legacy_category' => 'equip_tractor'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_generator', 'label' => 'Generator', 'legacy_category' => 'equip_generator'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_fuel_solutions', 'label' => 'Fuel Solutions', 'legacy_category' => 'equip_fuel_solutions'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_power_washer', 'label' => 'Power Washer', 'legacy_category' => 'equip_power_washer'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_earth-mover', 'label' => 'Earth Mover', 'legacy_category' => 'equip_earth-mover'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_snow_blower', 'label' => 'Snow Blower/Snow Thrower', 'legacy_category' => 'equip_snow_blower'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_chainsaw', 'label' => 'Chainsaw', 'legacy_category' => 'equip_chainsaw'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_saddle', 'label' => 'Saddle', 'legacy_category' => 'equip_saddle'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_racks', 'label' => 'Racks', 'legacy_category' => 'equip_racks'],
            ['entity_type_id' => 6, 'category' => 'equipment.equip_hitches', 'label' => 'Hitches', 'legacy_category' => 'equip_hitches'],

            // Semitrailer Category
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_flatbed', 'label' => 'Flat Bed', 'legacy_category' => 'semi_flatbed'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_double', 'label' => 'Double Drop', 'legacy_category' => 'semi_double'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_dryvan', 'label' => 'Dry Van', 'legacy_category' => 'semi_dryvan'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_lowboy', 'label' => 'Low Boy', 'legacy_category' => 'semi_lowboy'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_livestock', 'label' => 'Livestock', 'legacy_category' => 'semi_livestock'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_reefer', 'label' => 'Reefer', 'legacy_category' => 'semi_reefer'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_grain-hopper', 'label' => 'Grain Hopper', 'legacy_category' => 'semi_grain-hopper'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_tanker', 'label' => 'Tanker', 'legacy_category' => 'semi_tanker'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_dump', 'label' => 'Dump', 'legacy_category' => 'semi_dump'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_hopper_trailers', 'label' => 'Hopper Trailer', 'legacy_category' => 'semi_hopper_trailers'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_curtainside', 'label' => 'Curtainside', 'legacy_category' => 'semi_curtainside'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_container', 'label' => 'Container', 'legacy_category' => 'semi_container'],
            ['entity_type_id' => 7, 'category' => 'semitrailer.semi_detach', 'label' => 'Detach', 'legacy_category' => 'semi_detach'],

            // Semitruck Category
            ['entity_type_id' => 9, 'category' => 'semitruck.semitruck_standard', 'label' => 'Standard', 'legacy_category' => 'semitruck_standard'],
            ['entity_type_id' => 9, 'category' => 'semitruck.semitruck_tanker_truck', 'label' => 'Tanker Truck', 'legacy_category' => 'semitruck_tanker_truck'],
            ['entity_type_id' => 9, 'category' => 'semitruck.semitruck_flatbed_truck', 'label' => 'Flatbed Truck', 'legacy_category' => 'semitruck_flatbed_truck'],
            ['entity_type_id' => 9, 'category' => 'semitruck.semitruck_dump_truck', 'label' => 'Dump Truck', 'legacy_category' => 'semitruck_dump_truck'],
            ['entity_type_id' => 9, 'category' => 'semitruck.semitruck_other', 'label' => 'Other', 'legacy_category' => 'semitruck_other'],

            // Building Category
            ['entity_type_id' => 10, 'category' => 'building.barn', 'label' => 'Barn', 'legacy_category' => 'barn'],
            ['entity_type_id' => 10, 'category' => 'building.cabin', 'label' => 'Cabin', 'legacy_category' => 'cabin'],
            ['entity_type_id' => 10, 'category' => 'building.utility_shed', 'label' => 'Utility Shed', 'legacy_category' => 'utility_shed'],
            ['entity_type_id' => 10, 'category' => 'building.cottage', 'label' => 'Cottage Shed', 'legacy_category' => 'cottage'],
            ['entity_type_id' => 10, 'category' => 'building.metro_shed', 'label' => 'Metro Shed', 'legacy_category' => 'metro_shed'],
            ['entity_type_id' => 10, 'category' => 'building.vinyl', 'label' => 'Vinyl Building', 'legacy_category' => 'vinyl'],
            ['entity_type_id' => 10, 'category' => 'building.steel_frame_shed', 'label' => 'Steel Frame Shed', 'legacy_category' => 'steel_frame_shed'],
            ['entity_type_id' => 10, 'category' => 'building.metal_building', 'label' => 'Metal Building', 'legacy_category' => 'metal_building'],
            ['entity_type_id' => 10, 'category' => 'building.garage_carport', 'label' => 'Garage/Carport', 'legacy_category' => 'garage_carport'],
            ['entity_type_id' => 10, 'category' => 'building.other', 'label' => 'Other', 'legacy_category' => 'other'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
