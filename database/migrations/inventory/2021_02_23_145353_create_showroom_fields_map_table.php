<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateShowroomFieldsMapTable extends Migration
{
    private const INVENTORY_FIELDS = [
        'id' => 'id',
        'manufacturer' => 'manufacturer',
        'images' => 'images',
        'category' => 'inventoryCategory',
        'model,brand' => 'model',
        'model' => 'real_model',
        'type' => 'category',
        'axles' => 'axles',
        'description' => 'description',
        'description_txt' => 'description_txt',
        'msrp' => 'msrp',
        'gvwr' => 'gvwr',
        'GVWR' => 'gvwr',
        'pull_type' => 'pull_type',
        'color' => 'color',
        'living_quarters' => 'livingquarters',
        'ramp' => 'ramps',
        'frame' => 'construction',
        'stalls' => 'stalls',
        'nose_type' => 'nose_type',
        'configuration' => 'configuration',
        'roof_type' => 'roof_type',
        'colors' => 'color',
        'engine_type' => 'fuel_type',
        'num_passengers' => 'passengers',
        'num_batteries' => 'number_batteries',
        'year' => 'year',
        'brand' => 'brand',
        'video_embed_code' => 'video_embed_code',
        'dry_weight' => 'dry_weight'
    ];

    private const ATTRIBUTE_FIELDS = [
        'colors' => 'color',
        'horsepower' => 'horsepower',
        'fuel_capacity' => 'fuel_capacity',
        'dry_weight' => 'dry_weight',
        'wet_weight' => 'wet_weight',
        'seating_capacity' => 'seating_capacity',
        'pull_type' => 'pull_type',
        'axles' => 'axles',
        'tires' => 'tires',
        'ramp' => 'ramps',
        'stalls' => 'stalls',
        'configuration' => 'configuration',
        'roof_type' => 'roof_type',
        'nose_type' => 'nose_type',
        'living_quarters' => 'livingquarters',
        'sleeps' => 'sleeping_capacity',
        'slideouts' => 'slideouts',
        'engine_size' => 'engine_size',
        'propulsion' => 'propulsion',
        'mileage' => 'mileage',
        'hull_type' => 'hull_type',
        'engine_hours' => 'engine_hours',
        'interior_color' => 'interior_color',
        'hitch_weight' => 'hitch_weight',
        'cargo_weight' => 'cargo_weight',
        'fresh_water_capacity' => 'fresh_water_capacity',
        'gray_water_capacity' => 'gray_water_capacity',
        'black_water_capacity' => 'black_water_capacity',
        'furnace_btu' => 'furnace_btu',
        'ac_btu' => 'ac_btu',
        'electrical_service' => 'electrical_service',
        'available_beds' => 'available_beds',
        'number_awnings' => 'number_awnings',
        'awning_size' => 'awning_size',
        'axle_weight' => 'axle_weight',
        'weight' => 'weight',
        'total_weight_capacity' => 'total_weight_capacity',
        'transom' => 'transom'
    ];

    const MEASURE_FIELDS = [
        'length' => 'length',
        'length_min_real' => 'length',
        'length_max_real' => 'length',
        'length_min' => 'length',
        'length_max' => 'length',
        'width' => 'width',
        'width_min_real' => 'width',
        'width_max_real' => 'width',
        'min_width' => 'width',
        'max_width' => 'width',
        'beam' => 'width',
        'height' => 'height',
        'height_min_real' => 'height',
        'height_max_real' => 'height',
        'min_height' => 'height',
        'max_height' => 'height',
    ];

    const BOOLEAN_FIELDS = [
        'livingquarters',
        'ramps',
    ];

    const INTEGER_FIELDS = [
        'id',
        'year'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('showroom_fields_mapping');

        Schema::create('showroom_fields_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['inventory', 'attribute', 'measure']);
            $table->string('map_from');
            $table->string('map_to');
            $table->enum('field_type', ['boolean', 'string', 'integer', 'float', 'array'])->nullable();
            $table->timestamps();
        });

        $now = new DateTime();

        foreach (self::INVENTORY_FIELDS as $mapFrom => $mapTo) {
            $params = [
                'map_from' => $mapFrom,
                'map_to' => $mapTo,
                'type' => 'inventory',
                'created_at' => $now
            ];

            if (in_array($mapTo, self::BOOLEAN_FIELDS)) {
                $params['field_type'] = 'boolean';
            }

            if (in_array($mapTo, self::INTEGER_FIELDS)) {
                $params['field_type'] = 'integer';
            }

            DB::table('showroom_fields_mapping')->insert($params);
        }

        foreach (self::ATTRIBUTE_FIELDS as $mapFrom => $mapTo) {
            $params = [
                'map_from' => $mapFrom,
                'map_to' => $mapTo,
                'type' => 'attribute',
                'created_at' => $now
            ];

            if (in_array($mapTo, self::BOOLEAN_FIELDS)) {
                $params['field_type'] = 'boolean';
            }

            DB::table('showroom_fields_mapping')->insert($params);
        }

        foreach (self::MEASURE_FIELDS as $mapFrom => $mapTo) {
            DB::table('showroom_fields_mapping')->insert([
                'map_from' => $mapFrom,
                'map_to' => $mapTo,
                'type' => 'measure',
                'created_at' => $now
            ]);
        }

        exit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('showroom_fields_mapping');
    }
}
