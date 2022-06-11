<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixOrderStatusHintWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_STATUS_ORDER_KEY = 'inventory/status_order';

    private const INVENTORY_STATUS_ORDER_HINT = 'Works in tandem with the default sort order, defines a sort order by availability that cannot be changed. When default sort is by relevance, this will not work.';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->update(['note' => self::INVENTORY_STATUS_ORDER_HINT]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->update([
                    'note' => str_replace(
                        ' When default sort is by relevance, this will not work.',
                        '', self::INVENTORY_STATUS_ORDER_HINT
                    )
                ]
            );
    }
}
