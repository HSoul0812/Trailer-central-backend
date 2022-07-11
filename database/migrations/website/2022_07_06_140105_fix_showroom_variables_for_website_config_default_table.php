<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixShowroomVariablesForWebsiteConfigDefaultTable extends Migration
{
    private const IS_PRIVATE_VARIABLE = 1;
    private const IS_NOT_PRIVATE_VARIABLE = 0;
    private const SHOWROOM_GROUP = 'Showroom Setup';
    private const NO_GROUP = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->where('key', 'showroom/show_brands')->update([
            'private' => self::IS_NOT_PRIVATE_VARIABLE,
            'grouping' => self::SHOWROOM_GROUP
        ]);

        DB::table('website_config_default')->where('key', 'showroom/brands')->update([
            'private' => self::IS_NOT_PRIVATE_VARIABLE,
            'grouping' => self::SHOWROOM_GROUP
        ]);

        DB::table('website_config_default')->where('key', 'showroom/use_series')->update([
            'private' => self::IS_NOT_PRIVATE_VARIABLE,
            'grouping' => self::SHOWROOM_GROUP,
            'values' => '{"0":"Disabled","1":"Enabled"}'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', 'showroom/show_brands')->update([
            'private' => self::IS_PRIVATE_VARIABLE,
            'grouping' => '',
        ]);

        DB::table('website_config_default')->where('key', 'showroom/brands')->update([
            'private' => self::IS_PRIVATE_VARIABLE,
            'grouping' => self::NO_GROUP,
        ]);

        DB::table('website_config_default')->where('key', 'showroom/use_series')->update([
            'private' => self::IS_PRIVATE_VARIABLE,
            'grouping' => self::NO_GROUP,
            'values' => '{"0":"Disabled","1":"Enabled"}'
        ]);
    }
}
