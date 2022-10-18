<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateGrouppingForLeadMerging extends Migration
{
    private const KEY = 'leads/merge/enabled';
    private const GROUPING = [
        'grouping' => 'Contact Forms'
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
            ->update(self::GROUPING);
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
                'grouping' => ''
            ]
        );
    }
}
