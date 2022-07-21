<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixNoGroupVariablesForWebsiteConfigDefaultTable extends Migration
{
    private const CALL_TO_ACTION_GROUP = 'Call to Action Pop-Up';
    private const INVENTORY_GROUP = 'Inventory Display';
    private const NO_GROUP = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->where('key', 'call-to-action/image/link-url')->update([
            'grouping' => self::CALL_TO_ACTION_GROUP
        ]);

        DB::table('website_config_default')->where('key', 'inventory/filters/enable_filters')->update([
            'grouping' => self::INVENTORY_GROUP
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', 'call-to-action/image/link-url')->update([
            'grouping' => self::NO_GROUP
        ]);

        DB::table('website_config_default')->where('key', 'inventory/filters/enable_filters')->update([
            'grouping' => self::NO_GROUP
        ]);
    }
}
