<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColtonrvToCollectorTable extends Migration
{
    private const OVERRIDABLE_FIELDS = [
        "number_batteries" => false,
        "passengers" => false,
        "ac_btu" => false,
        "air_conditioners" => false,
        "available_beds" => false,
        "awning_size" => false,
        "axle_weight" => false,
        "axles" => false,
        "black_water_capacity" => false,
        "brand" => false,
        "cab_type" => false,
        "cargo_weight" => false,
        "type_code" => false,
        "chosen_overlay" => false,
        "color" => false,
        "designation" => false,
        "configuration" => false,
        "construction" => false,
        "conversion" => false,
        "cost_of_shipping" => false,
        "cost_of_unit" => false,
        "created_at" => false,
        "custom_conversion" => false,
        "daily_price" => false,
        "dead_rise" => false,
        "dealer_location" => false,
        "description" => false,
        "draft" => false,
        "drive_trail" => false,
        "dry_weight" => false,
        "electrical_service" => false,
        "engine" => false,
        "engine_hours" => false,
        "engine_size" => false,
        "floorplan" => false,
        "fresh_water_capacity" => false,
        "fuel_capacity" => false,
        "fuel_type" => false,
        "furnace_btu" => false,
        "gray_water_capacity" => false,
        "gvwr" => true,
        "has_stock_images" => false,
        "height" => true,
        "height_display_mode" => true,
        "hidden_price" => false,
        "hitch_weight" => false,
        "horsepower" => false,
        "hull_type" => false,
        "images" => false,
        "interior_color" => false,
        "is_featured" => false,
        "is_rental" => false,
        "is_special" => false,
        "length" => true,
        "length_display_mode" => true,
        "livingquarters" => false,
        "manger" => false,
        "manufacturer" => false,
        "midtack" => false,
        "mileage" => false,
        "model" => false,
        "monthly_payment" => false,
        "msrp" => false,
        "nose_type" => false,
        "note" => false,
        "number_awnings" => false,
        "price" => false,
        "propulsion" => false,
        "pull_type" => false,
        "ramps" => false,
        "roof_type" => false,
        "sales_price" => false,
        "seating_capacity" => false,
        "shortwall_length" => false,
        "show_on_website" => false,
        "showroom_files" => false,
        "side_wall_height" => false,
        "sleeping_capacity" => false,
        "slideouts" => false,
        "stalls" => false,
        "status" => false,
        "stock" => false,
        "tires" => false,
        "title" => false,
        "total_of_cost" => false,
        "total_weight_capacity" => false,
        "transmission" => false,
        "transom" => false,
        "use_website_price" => false,
        "video_embed_code" => false,
        "vin" => false,
        "website_price" => false,
        "weekly_price" => false,
        "weight" => true,
        "wet_weight" => false,
        "width" => true,
        "width_display_mode" => true,
        "year" => false,

        
        "payload_capacity" => true
    ];

    private const COLTONRV_PARAMS = [
        'dealer_id' => 9133,
        'dealer_location_id' => 15489,
        'process_name' => 'ColtonRV',
        'ftp_host' => 'ftp.trailercentral.com',
        'ftp_path' => 'Inventory Colton RV.xml',
        'ftp_login' => 'astraweb',
        'ftp_password' => 'X,mf4U=RW#pT89JC',
        'file_format' => 'xml',
        'path_to_data' => 'Unit',
        'create_items' => true,
        'update_items' => true,
        'archive_items' => true,
        'length_format' => null,
        'width_format' => null,
        'height_format' => null,
        'show_on_rvtrader' => true,
        'title_format' => 'year,manufacturer,model',
        'import_prices' => true,
        'import_description' => true,
        'images_delimiter' => ',', //????????
        'overridable_fields' => '[]',


        'not_archive_manually_items' => true
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $params = self::COLTONRV_PARAMS;
        $params['overridable_fields'] = self::OVERRIDABLE_FIELDS;

        Schema::table('collector', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            //
        });
    }
}
