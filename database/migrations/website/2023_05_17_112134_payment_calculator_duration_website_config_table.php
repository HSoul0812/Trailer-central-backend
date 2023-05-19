<?php

declare(strict_types=1);

use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;
use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentCalculatorDurationWebsiteConfigTable
 */
class PaymentCalculatorDurationWebsiteConfigTable extends Migration
{
    private const PAYMENT_CALCULATOR_DURATION = [
        'key' => WebsiteConfig::PAYMENT_CALCULATOR_DURATION_KEY,
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Payment Calculator Duration',
        'grouping' => WebsiteConfigDefault::GROUPING_PAYMENT_CALCULATOR,
        'sort_order' => 2695,
        'note' => 'Payment Calculator Terms Duration example Monthly Payment or Biweekly',
        'values' => '{"monthly":"Monthly Payment","biweekly":"Biweekly Payment"}',
        'default_label' => 'Monthly Payment',
        'default_value' => WebsiteConfigDefault::PAYMENT_CALCULATOR_DURATION_MONTHLY,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $website = Website::query()
            ->where('domain', 'coltonrv.com')
            ->first();

        DB::table('website_payment_calculator_settings')
            ->where('website_id', '!=', $website->getKey())
            ->update([
                'inventory_category_id' => null,
            ]);

        if (DB::table('website_config_default')->where('key', WebsiteConfig::PAYMENT_CALCULATOR_DURATION_KEY)->doesntExist()) {
            DB::table('website_config_default')->insert(self::PAYMENT_CALCULATOR_DURATION);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')
            ->where('key', WebsiteConfig::PAYMENT_CALCULATOR_DURATION_KEY)
            ->delete();
    }
}
