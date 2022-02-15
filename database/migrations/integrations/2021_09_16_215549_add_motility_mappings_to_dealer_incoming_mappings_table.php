<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMotilityMappingsToDealerIncomingMappingsTable extends Migration
{
    private const MOTILITY_STATUS_MAPPING = [
        'map_from' => 'Status',
        'map_to' => 'status',
        'type' => 'fields',
    ];

    private const MOTILITY_TYPE_MAPPING = [
        'map_from' => 'Type',
        'map_to' => 'category',
        'type' => 'fields',
    ];
    
    private const MOTILITY_LENGTH_MAPPING = [
        'map_from' => 'Length',
        'map_to' => 'length',
        'type' => 'fields',
    ];
    
    private const MOTILITY_WIDTH_MAPPING = [
        'map_from' => 'Width',
        'map_to' => 'width',
        'type' => 'fields',
    ];
    
    private const MOTILITY_HEIGHT_MAPPING = [
        'map_from' => 'Height',
        'map_to' => 'height',
        'type' => 'fields',
    ];
    
    private const MOTILITY_YEAR_MAPPING = [
        'map_from' => 'Year',
        'map_to' => 'year',
        'type' => 'fields',
    ];
    
    private const MOTILITY_SELLING_PRICE_MAPPING = [
        'map_from' => 'selling_price',
        'map_to' => 'price',
        'type' => 'fields',
    ];
    
    private const MOTILITY_MODEL_MAPPING = [
        'map_from' => 'Model',
        'map_to' => 'model',
        'type' => 'fields',
    ];
    
    private const MOTILITY_MANUFACTURER_MAPPING = [
        'map_from' => 'Manufacturer',
        'map_to' => 'manufacturer',
        'type' => 'fields',
    ];
    
    private const MOTILITY_MAKE_MAPPING = [
        'map_from' => 'Make',
        'map_to' => 'brand',
        'type' => 'fields',
    ];
    
    private const MOTILITY_STOCKNUMBER_MAPPING = [
        'map_from' => 'StockNumber',
        'map_to' => 'stock',
        'type' => 'fields',
    ];
    
    private const MOTILITY_DISPLAYONINTERNET_MAPPING = [
        'map_from' => 'DisplayOnInternet',
        'map_to' => 'show_on_website',
        'type' => 'fields',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_incoming_mappings', function (Blueprint $table) {
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_STATUS_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_TYPE_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_LENGTH_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_WIDTH_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_HEIGHT_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_YEAR_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_SELLING_PRICE_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_MODEL_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_MANUFACTURER_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_MAKE_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_STOCKNUMBER_MAPPING);
          DB::table('dealer_incoming_mappings')->insert(self::MOTILITY_DISPLAYONINTERNET_MAPPING);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_incoming_mappings', function (Blueprint $table) {
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_STATUS_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_TYPE_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_LENGTH_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_WIDTH_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_HEIGHT_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_YEAR_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_SELLING_PRICE_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_MODEL_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_MANUFACTURER_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_MAKE_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_STOCKNUMBER_MAPPING);
          DB::table('dealer_incoming_mappings')->delete(self::MOTILITY_DISPLAYONINTERNET_MAPPING);
        });
    }
}
