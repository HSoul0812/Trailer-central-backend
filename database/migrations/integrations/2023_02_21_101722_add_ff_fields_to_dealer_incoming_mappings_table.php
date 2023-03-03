<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFfFieldsToDealerIncomingMappingsTable extends Migration
{
    private const DEALER_INCOMING_MAPPINGS_TABLE = 'dealer_incoming_mappings';

    private const PJ_INTEGRATION_NAME = 'pj';
    private const UTC_INTEGRATION_NAME = 'utc';

    private const PJ_MAPPINGS = [
        'pull_type' => [
            'BP' => 'bumper',
            'GN' => 'gooseneck',
            'PT' => 'pintle',
            'SD' => 'tractor_hookup',
        ],
        'roof_type' => [
            'Round' => 'round',
            'Flat'  => 'flat'
        ],
        'nose_type' => [
            'V Front' => 'v_front',
            'Flat'    => 'flat'
        ],
        'status' => [
            'sold'      => '2',
            'available' => '1',
            'on_order'  => '3',
        ],
        'color' => [
            'Black Powdercoat'            => 'black',
            'Equipment Yellow Powdercoat' => 'yellow',
            'Grey Powdercoat'             => 'grey',
            'Red Powdercoat'              => 'red',
            'Tractor Green Powdercoat'    => 'green',
            'Tractor Orange'              => 'orange',
            'White Powdercoat'            => 'white'
        ]
    ];

    private const UTC_MAPPING = [
        'status' => [
            'available' => '1',
            'sold'      => '2',
            'on order'  => '3',
        ],
        'pull_type' => [
            'BUMPER PULL' => 'bumper',
            'bumper'      => 'bumper',
            'fifth_wheel' => 'fifth_wheel',
            'gooseneck'   => 'gooseneck',
            'pintle'      => 'pintle',
            'tag'         => 'bumper',
            '5th wheel'   => 'fifth_wheel'
        ],
        'nose_type' => [
            'round'  => 'round',
            'flat'   => 'flat',
            'v_front' => 'v_front',
        ],
        'roof_type' => [
            'round' => 'round',
            'flat'  => 'flat'
        ],
        'brand' => [
            'haulmark'       => 'Haulmark',
            'wells cargo'    => 'Wells Cargo',
            'tc trecker'     => 'Wells Cargo',
            'road force'     => 'Wells Cargo',
            'exiss trailers' => 'Exiss',
            'sooner'         => 'Sooner',
            'exiss'          => 'Exiss',

        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::PJ_MAPPINGS as $type => $pjMapping) {
            foreach ($pjMapping as $mapFrom => $mapTo) {
                DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)->insert([
                    'integration_name' => self::PJ_INTEGRATION_NAME,
                    'type' => $type,
                    'map_from' => $mapFrom,
                    'map_to' => $mapTo,
                ]);
            }
        }

        foreach (self::UTC_MAPPING as $type => $pjMapping) {
            foreach ($pjMapping as $mapFrom => $mapTo) {
                DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)->insert([
                    'integration_name' => self::UTC_INTEGRATION_NAME,
                    'type' => $type,
                    'map_from' => $mapFrom,
                    'map_to' => $mapTo,
                ]);
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
        DB::table(self::DEALER_INCOMING_MAPPINGS_TABLE)
            ->where('integration_name', '=', self::PJ_INTEGRATION_NAME)
            ->orWhere('integration_name', '=', self::UTC_INTEGRATION_NAME)
            ->delete();
    }
}
