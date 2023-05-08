<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColtonrvToCollectorTable extends Migration
{
    private const DEALER_ID = 9133;

    private const COLLECTOR_TABLE = 'collector';
    private const DEALER_INCOMING_MAPPINGS_TABLE = 'dealer_incoming_mappings';
    private const COLLECTOR_SPECIFICATION_TABLE = 'collector_specification';
    private const COLLECTOR_SPECIFICATION_RULES_TABLE = 'collector_specification_rules';
    private const COLLECTOR_SPECIFICATION_ACTIONS_TABLE = 'collector_specification_actions';

    private const OVERRIDABLE_FIELDS = [
        "number_batteries" => false,
        "passengers" => false,
        "ac_btu" => true,
        "air_conditioners" => true,
        "available_beds" => true,
        "awning_size" => false,
        "axle_weight" => true,
        "axles" => false,
        "black_water_capacity" => true,
        "brand" => false,
        "cab_type" => false,
        "cargo_weight" => true,
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
        "description" => true,
        "draft" => false,
        "drive_trail" => false,
        "dry_weight" => true,
        "electrical_service" => true,
        "engine" => false,
        "engine_hours" => false,
        "engine_size" => false,
        "floorplan" => false,
        "fresh_water_capacity" => true,
        "fuel_capacity" => false,
        "fuel_type" => true,
        "furnace_btu" => true,
        "gray_water_capacity" => true,
        "gvwr" => true,
        "has_stock_images" => false,
        "height" => true,
        "height_display_mode" => true,
        "hidden_price" => false,
        "hitch_weight" => true,
        "horsepower" => false,
        "hull_type" => false,
        "images" => false,
        "interior_color" => true,
        "is_featured" => false,
        "is_rental" => true,
        "is_special" => false,
        "length" => true,
        "length_display_mode" => true,
        "livingquarters" => true,
        "manger" => false,
        "manufacturer" => false,
        "midtack" => false,
        "mileage" => true,
        "model" => false,
        "monthly_payment" => false,
        "msrp" => false,
        "nose_type" => false,
        "note" => false,
        "number_awnings" => true,
        "price" => false,
        "propulsion" => false,
        "pull_type" => true,
        "ramps" => false,
        "roof_type" => false,
        "sales_price" => false,
        "seating_capacity" => false,
        "shortwall_length" => false,
        "show_on_website" => false,
        "showroom_files" => true,
        "side_wall_height" => false,
        "sleeping_capacity" => true,
        "slideouts" => true,
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
        "video_embed_code" => true,
        "vin" => false,
        "website_price" => false,
        "weekly_price" => false,
        "weight" => true,
        "wet_weight" => false,
        "width" => true,
        "width_display_mode" => true,
        "year" => false,

        "payload_capacity" => true,
        "axle_capacity" => true,
        "overall_length" => true,
        "external_link" => true,
        "min_width" => true,
        "min_height" => true,
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
        'path_to_fields_to_description' => null,
        'fields_to_description' => null,
        'use_secondary_image' => true,
        'append_floorplan_image' => true,
        'update_images' => true,
        'update_files' => false,
        'import_with_showroom_category' => false,
        'unarchive_sold_items' => false,
        'cdk_username' => null,
        'cdk_password' => null,
        'ids_token' => null,
        'ids_default_location' => null,
        'use_factory_mapping' => true,
        'xml_url' => null,
        'skip_categories' => null,
        'skip_locations' => null,
        'zero_msrp' => false,
        'linebreak_characters' => null,
        'only_types' => null,
        'local_image_directory_address' => null,
        'motility_username' => null,
        'motility_password' => null,
        'motility_account_no' => null,
        'motility_integration_id' => null,
        'use_latest_ftp_file_only' => false,
        'spincar_active' => false,
        'spincar_spincar_id' => null,
        'spincar_filenames' => null,
        'api_url' => null,
        'api_key_name' => null,
        'api_key_value' => null,
        'api_params' => null,
        'api_max_records' => null,
        'api_pagination' => null,
        'ignore_manually_added_units' => false,
        'is_bdv_enabled' => true,
        'csv_url' => null,
        'show_on_auction123' => false,
        'video_source_fields' => null,
        'is_mfg_brand_mapping_enabled' => false,
        'cdk_dealer_cmfs' => null,
        'override_all' => false,
        'override_images' => false,
        'override_video' => false,
        'override_prices' => false,
        'override_attributes' => false,
        'override_descriptions' => false,

        'use_partial_update' => true,
        'days_till_full_run' => 7,
        'mark_sold_manually_added_items' => false,
        'not_save_unmapped_on_factory_units' => false,
        'conditional_title_format' => 'year,manufacturer,real_brand,model;year,manufacturer,series,model',
        'use_brands_for_factory_mapping' => true,
        'check_images_for_bdv_matching' => true,
    ];

    private const DEALER_INCOMING_MAPPING_FIELDS = [
        [
            'map_from' => 'stock_number',
            'map_to' => 'stock',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'designation',
            'map_to' => 'designation',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'designation',
            'map_to' => 'condition',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'manufacturer',
            'map_to' => 'manufacturer',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'manufacturer',
            'map_to' => 'brand',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'model',
            'map_to' => 'model',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'model_year',
            'map_to' => 'year',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'type',
            'map_to' => 'type',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'type',
            'map_to' => 'type_code',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'status',
            'map_to' => 'status',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'mileage',
            'map_to' => 'mileage',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'exterior_color',
            'map_to' => 'exterior_color',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'exterior_color',
            'map_to' => 'color',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'special_web_price',
            'map_to' => 'hidden_price',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'take_price',
            'map_to' => 'sales_price',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'total_list',
            'map_to' => 'msrp',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'brand',
            'map_to' => 'real_brand',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'chassis_no',
            'map_to' => 'vin',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
        [
            'map_from' => 'gl_location_code',
            'map_to' => 'dealer_location',
            'dealer_id' => self::DEALER_ID,
            'type' => 'fields',
        ],
    ];

    private const DEALER_INCOMING_MAPPING_LOCATIONS = [
        [
            'map_from' => 'CRV',
            'map_to' => 15490,
            'dealer_id' => self::DEALER_ID,
            'type' => 'dealer_location'
        ],
        [
            'map_from' => 'OP',
            'map_to' => 15491,
            'dealer_id' => self::DEALER_ID,
            'type' => 'dealer_location'
        ],
        [
            'map_from' => 'POC',
            'map_to' => 21213,
            'dealer_id' => self::DEALER_ID,
            'type' => 'dealer_location'
        ],
    ];

    private const DEALER_INCOMING_MAPPING_CONDITIONS = [
        [
            'map_from' => 'NEW',
            'map_to' => 'new',
            'dealer_id' => self::DEALER_ID,
            'type' => 'condition'
        ],
        [
            'map_from' => 'USED',
            'map_to' => 'used',
            'dealer_id' => self::DEALER_ID,
            'type' => 'condition'
        ],
        [
            'map_from' => 'CONSIGNMENT',
            'map_to' => 'used',
            'dealer_id' => self::DEALER_ID,
            'type' => 'condition'
        ],
    ];

    private const DEALER_INCOMING_MAPPING_DEFAULT = [
        [
            'map_from' => 'price',
            'map_to' => 0,
            'dealer_id' => self::DEALER_ID,
            'type' => 'default_values'
        ]
    ];

    private const COLLECTOR_SPECIFICATIONS = [
        [
            'logical_operator' => 'and',
            'rules' => [
                [
                    'condition' => 'same',
                    'field' => 'lot_location_code',
                    'value' => 'CRV'
                ],
            ],
            'actions' => [
                [
                    'action' => 'mapping',
                    'field' => 'dealer_location',
                    'value' => 'CRV'
                ]
            ],
        ],
        [
            'logical_operator' => 'and',
            'rules' => [
                [
                    'condition' => 'same',
                    'field' => 'lot_location_code',
                    'value' => 'OP'
                ],
            ],
            'actions' => [
                [
                    'action' => 'mapping',
                    'field' => 'dealer_location',
                    'value' => 'OP'
                ]
            ],
        ],
        [
            'logical_operator' => 'and',
            'rules' => [
                [
                    'condition' => 'same',
                    'field' => 'lot_location_code',
                    'value' => 'POC'
                ],
            ],
            'actions' => [
                [
                    'action' => 'mapping',
                    'field' => 'dealer_location',
                    'value' => 'POC'
                ]
            ],
        ],
        [
            'logical_operator' => 'and',
            'rules' => [
                [
                    'condition' => 'same',
                    'field' => 'manufacturer',
                    'value' => 'HARLEY DAVIDSON'
                ],
            ],
            'actions' => [
                [
                    'action' => 'mapping',
                    'field' => 'type_code',
                    'value' => 'MOTORCYCLE'
                ]
            ],
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = new \DateTime();

        $params = self::COLTONRV_PARAMS;

        $params['overridable_fields'] = json_encode(self::OVERRIDABLE_FIELDS);
        $params['created_at'] = $now;
        $params['updated_at'] = $now;

        $collectorId = DB::table(self::COLLECTOR_TABLE)->insertGetId($params);

        $dealerIncomingMapping = array_merge(
            self::DEALER_INCOMING_MAPPING_FIELDS,
            self::DEALER_INCOMING_MAPPING_CONDITIONS,
            self::DEALER_INCOMING_MAPPING_LOCATIONS,
            self::DEALER_INCOMING_MAPPING_DEFAULT
        );

        foreach ($dealerIncomingMapping as $mapping) {
            $isExists = DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)->where([
                'map_from' => $mapping['map_from'],
                'dealer_id' => $mapping['dealer_id'],
                'type' => $mapping['type'],
            ])->exists();

            if ($isExists) {
                continue;
            }

            DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)->insert($mapping);
        }

        foreach (self::COLLECTOR_SPECIFICATIONS as $specification) {
            $actions = $specification['actions'];
            $rules = $specification['rules'];

            unset($specification['actions']);
            unset($specification['rules']);

            $specification['created_at'] = $now;
            $specification['updated_at'] = $now;
            $specification['collector_id'] = $collectorId;

            $specificationId = DB::table(self::COLLECTOR_SPECIFICATION_TABLE)->insertGetId($specification);

            foreach ($actions as $action) {
                $action['created_at'] = $now;
                $action['updated_at'] = $now;
                $action['collector_specification_id'] = $specificationId;

                DB::table(self::COLLECTOR_SPECIFICATION_ACTIONS_TABLE)->insert($action);
            }

            foreach ($rules as $rule) {
                $rule['created_at'] = $now;
                $rule['updated_at'] = $now;
                $rule['collector_specification_id'] = $specificationId;

                DB::table(self::COLLECTOR_SPECIFICATION_RULES_TABLE)->insert($rule);
            }
        }
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
