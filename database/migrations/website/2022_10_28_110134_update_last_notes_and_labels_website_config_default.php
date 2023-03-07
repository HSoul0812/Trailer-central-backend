<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateLastNotesAndLabelsWebsiteConfigDefault extends Migration
{
    private const NEW_VALUES_INDEXED_BY_KEY = [
        'showroom/use_series' => ['note' => 'Consolidates models into series on the showroom page.'],
        'inventory/filters/enable_filters' => ['note' => 'Allows user to override the standard filter list with a custom setup.'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (self::NEW_VALUES_INDEXED_BY_KEY as $key => $config) {
            DB::table('website_config_default')
                ->where('key', $key)
                ->update($config);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (self::NEW_VALUES_INDEXED_BY_KEY as $key => $config) {
            DB::table('website_config_default')
                ->where('key', $key)
                ->update(['note' => null]);
        }
    }
}
