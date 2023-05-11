<?php

declare(strict_types=1);

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class AddBodyScriptsToWebsiteConfigDefaultTable
 */
class AddBodyScriptsToWebsiteConfigDefaultTable extends Migration
{
    private const TABLE_NAME = 'website_config_default';

    private const GENERAL_BODY_SCRIPT = [
        'key' => WebsiteConfig::GENERAL_BODY_SCRIPT_KEY,
        'private' => 0,
        'type' => 'textarea',
        'label' => 'Body Scripts (admin visible only)',
        'grouping' => 'General',
        'sort_order' => 2325,
        'note' => "Body scripts to be added to between the body tags. Dealers can't see and have no control over this.",
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (DB::table(self::TABLE_NAME)->where('key', self::GENERAL_BODY_SCRIPT['key'])->doesntExist()) {
            DB::table(self::TABLE_NAME)
                ->insert(self::GENERAL_BODY_SCRIPT);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table(self::TABLE_NAME)
            ->where('key', self::GENERAL_BODY_SCRIPT['key'])
            ->delete();
    }
}
