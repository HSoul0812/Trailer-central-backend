<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOrderByNumberOfImagesOptionToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_DEFAULT_ORDER_KEY = 'inventory/default_order';

    private const INVENTORY_DEFAULT_ORDER_VALUES = [
        '1' => 'Newest',
        '2' => 'Price ($ to $$$)',
        '3' => 'Price ($$$ to $)',
        '4' => 'Title (A to Z)',
        '5' => 'Title (Z to A)',
        '6' => 'Length (Asc)',
        '7' => 'Length (Desc)',
        '8' => 'Stock (Asc)',
        '9' => 'Stock (Desc)',
        '10' => 'Photo counter (Asc)',
        '11' => 'Photo counter (Desc)'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
            ->update(['values' => json_encode(self::INVENTORY_DEFAULT_ORDER_VALUES), 'default_label' => 'Newest']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $values = self::INVENTORY_DEFAULT_ORDER_VALUES;

        unset($values['-1']);

        $values['1'] = 'Relevance';

        DB::table('website_config_default')
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
            ->update(['values' => json_encode($values), 'default_label' => 'Relevance']);
    }
}
