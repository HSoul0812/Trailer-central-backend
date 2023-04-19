<?php

use App\Models\Parts\ListingCategoryMappings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTiltTrailersToListingCategoryMappingsTable extends Migration
{
    const MAP_FROM = 'Tilt Trailers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        ListingCategoryMappings::create([
            'map_from' => self::MAP_FROM,
            'map_to' => 'semi_tilt',
            'type' => ListingCategoryMappings::TYPE_INVENTORY,
            'type_id' => ListingCategoryMappings::TYPE_ID_GENERAL_TRAILER,
            'entity_type_id' => ListingCategoryMappings::ENTITY_TYPE_ID_TRAILER,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        ListingCategoryMappings::where('map_from', self::MAP_FROM)->delete();
    }
}
