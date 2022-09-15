<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyTermListGroup extends Migration
{
    private const TERM_LIST_KEY = 'payment-calculator/term-list';
    private const TERM_LIST_VALUES = [
        'private' => true,
        'grouping' => 'Payment Calculator'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')
            ->where('key', self::TERM_LIST_KEY)
            ->update(self::TERM_LIST_VALUES);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::TERM_LIST_KEY)->update(
            [
                'private' => false,
                'grouping' => ''
            ]
        );
    }
}
