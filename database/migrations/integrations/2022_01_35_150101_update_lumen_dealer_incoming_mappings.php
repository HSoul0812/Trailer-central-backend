<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateLumenDealerIncomingMappings extends Migration
{

    private const TABLE_NAME = 'dealer_incoming_mappings';

    private const added_types = "ALTER TABLE " . self::TABLE_NAME . " CHANGE `type` `type` enum('manufacturer','category','entity_type','condition','status','pull_type','nose_type','construction','fuel_type','color','brand','manufacturer_brand','dealer_location','transmission','drive_trail','engine_size','fields','default_values','doors','body','transmission_speed','series','city_mpg','highway_mpg', 'propulsion');";

    private const actual_type = "ALTER TABLE " . self::TABLE_NAME . " CHANGE `type` `type` enum('manufacturer','category','entity_type','condition','status','color','pull_type','nose_type','construction','fuel_type','brand','manufacturer_brand','dealer_location','transmission','drive_trail','engine_size','fields','default_values');";

    private const ADD_CONSTRUCTION = [
        [
            'map_from' => 'Fiberglass',
            'map_to' => 'fiberglass',
            'type' => 'construction',
        ],
        [
            'map_from' => 'Composite',
            'map_to' => 'composite',
            'type' => 'construction',
        ],
        [
            'map_from' => 'Hypalon',
            'map_to' => 'hypalon',
            'type' => 'construction',
        ],
        [
            'map_from' => 'Roplene',
            'map_to' => 'roplene',
            'type' => 'construction',
        ],
        [
            'map_from' => 'Wood',
            'map_to' => 'wood',
            'type' => 'construction',
        ],
        [
            'map_from' => 'Other',
            'map_to' => 'other',
            'type' => 'construction',
        ]
    ];

    private const ADD_PROPULSION = [
        [
            'map_from' => 'Stern',
            'map_to' => 'stern',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Sail',
            'map_to' => 'sail',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'V Drive',
            'map_to' => 'v_drive',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Direct Drive',
            'map_to' => 'direct_drive',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Surface Drive',
            'map_to' => 'surface_drive',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Air Propeller',
            'map_to' => 'air_propeller',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Pod Drive',
            'map_to' => 'pod_drive',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Outboard',
            'map_to' => 'outboard',
            'type' => 'propulsion',
        ],
        [
            'map_from' => 'Other',
            'map_to' => 'other',
            'type' => 'propulsion',
        ]
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::statement(self::added_types);
            DB::table(self::TABLE_NAME)->insert(self::ADD_CONSTRUCTION);
            DB::table(self::TABLE_NAME)->insert(self::ADD_PROPULSION);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::statement(self::actual_type);
            foreach (self::ADD_CONSTRUCTION as $construction) {
                DB::table(self::TABLE_NAME)->where($construction)->delete();
            }
            foreach (self::ADD_PROPULSION as $propulsion) {
                DB::table(self::TABLE_NAME)->where($propulsion)->delete();
            }
        });
    }


}
