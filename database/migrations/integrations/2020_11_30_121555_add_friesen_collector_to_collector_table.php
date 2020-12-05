<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFriesenCollectorToCollectorTable extends Migration
{
    const FRIESEN_COLLECTOR_PARAMS = [
        'dealer_id' => 6371,
        'dealer_location_id' => 9755,
        'process_name' => 'friesen',
        'ftp_host' => 'ftp.trailercentral.com',
        'ftp_path' => 'friesen.csv',
        'ftp_login' => 'friesen',
        'ftp_password' => 'b}}%~}xJ$M;\{Ub8R',
        'file_format' => 'csv',
        'length_format' => 'feet',
        'width_format' => 'feet',
        'height_format' => 'feet',
        'title_format' => 'year,manufacturer,model,category',
        'import_prices' => true,
        'import_description' => true,
        'images_delimiter' => '|',
    ];

    const FRIESEN_MAPPING = [
        [
            'map_from' => 'Stockno',
            'map_to' => 'stock',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'VIN',
            'map_to' => 'vin',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'NEW_USED',
            'map_to' => 'designation',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Year',
            'map_to' => 'year',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Make',
            'map_to' => 'manufacturer',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Model',
            'map_to' => 'model',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Drivetrain',
            'map_to' => 'drive_trail',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Transmission',
            'map_to' => 'transmission',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'ExteriorColor',
            'map_to' => 'color',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'InteriorColor',
            'map_to' => 'interior_color',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Engine',
            'map_to' => 'engine_size',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'PRICE',
            'map_to' => 'price',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'MSRP',
            'map_to' => 'msrp',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Miles',
            'map_to' => 'mileage',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'PhotoURL',
            'map_to' => 'images',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Body',
            'map_to' => 'type_code',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Description',
            'map_to' => 'description',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Fuel_Type',
            'map_to' => 'fuel_type',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],
        [
            'map_from' => 'Cost',
            'map_to' => 'cost_of_unit',
            'dealer_id' => 6371,
            'type' => 'fields',
        ],

        [
            'map_from' => 'status',
            'map_to' => 'available',
            'dealer_id' => 6371,
            'type' => 'default_values',
        ],
        [
            'map_from' => 'available',
            'map_to' => 1,
            'dealer_id' => 6371,
            'type' => 'status',
        ],

        [
            'map_from' => 'Select',
            'map_to' => 'manual',
            'dealer_id' => 6371,
            'type' => 'transmission',
        ],
        [
            'map_from' => 'Automatic',
            'map_to' => 'automatic',
            'dealer_id' => 6371,
            'type' => 'transmission',
        ],

        [
            'map_from' => 'Front Wheel Drive',
            'map_to' => 'front_wheel_drive',
            'dealer_id' => 6371,
            'type' => 'drive_trail',
        ],
        [
            'map_from' => 'All-wheel Drive',
            'map_to' => 'automatic',
            'dealer_id' => 6371,
            'type' => 'drive_trail',
        ],
        [
            'map_from' => 'Front-wheel Drive',
            'map_to' => 'front_wheel_drive',
            'dealer_id' => 6371,
            'type' => 'drive_trail',
        ],

        [
            'map_from' => '4 Cylinder Engine',
            'map_to' => '4_cylinder',
            'dealer_id' => 6371,
            'type' => 'engine_size',
        ],
        [
            'map_from' => 'I-6 cyl',
            'map_to' => '6_cylinder',
            'dealer_id' => 6371,
            'type' => 'engine_size',
        ],
        [
            'map_from' => 'I-4 cyl',
            'map_to' => '4_cylinder',
            'dealer_id' => 6371,
            'type' => 'engine_size',
        ],
        [
            'map_from' => 'V-8 cyl',
            'map_to' => '8_cylinder',
            'dealer_id' => 6371,
            'type' => 'engine_size',
        ],

        [
            'map_from' => 'Gasoline Fuel',
            'map_to' => 'gas',
            'dealer_id' => 6371,
            'type' => 'fuel_type',
        ],
        [
            'map_from' => 'Premium Unleaded',
            'map_to' => 'gas',
            'dealer_id' => 6371,
            'type' => 'fuel_type',
        ],
        [
            'map_from' => 'Regular Unleaded',
            'map_to' => 'gas',
            'dealer_id' => 6371,
            'type' => 'fuel_type',
        ],
        [
            'map_from' => 'Diesel',
            'map_to' => 'diesel',
            'dealer_id' => 6371,
            'type' => 'fuel_type',
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('collector')->insert(self::FRIESEN_COLLECTOR_PARAMS);
        DB::table('dealer_incoming_mappings')->insert(self::FRIESEN_MAPPING);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('collector')->where(['dealer_id' => 6371])->delete();
        DB::table('dealer_incoming_mappings')->where(['dealer_id' => 6371])->delete();
    }
}
