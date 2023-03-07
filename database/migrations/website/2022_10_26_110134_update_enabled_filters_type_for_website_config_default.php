<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEnabledFiltersTypeForWebsiteConfigDefault extends Migration
{
    private const KEY = 'inventory/filters/enable_filters';

    private const NEW_CONFIG = [
        'type' => 'enumerable_multiple'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')
            ->where('key', self::KEY)
            ->update(self::NEW_CONFIG);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::KEY)->update(
            [
                'type' => 'textarea'
            ]
        );
    }
}
