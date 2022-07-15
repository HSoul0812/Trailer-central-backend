<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixShowroomBrandsForWebsiteConfigDefaultTable extends Migration
{
    private const KEY_VARIABLE = 'showroom/brands';
    private const ENUMERABLE_MULTIPLE = 'enumerable_multiple';
    private const ENUMERABLE = 'enumerable';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->where('key', self::KEY_VARIABLE)->update([
            'type' => self::ENUMERABLE_MULTIPLE
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::KEY_VARIABLE)->update([
            'type' => self::ENUMERABLE
        ]);
    }
}
