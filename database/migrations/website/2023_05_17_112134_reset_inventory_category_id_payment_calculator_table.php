<?php

declare(strict_types=1);

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class ResetInventoryCategoryIdPaymentCalculatorTable
 */
class ResetInventoryCategoryIdPaymentCalculatorTable extends Migration
{
    private const TABLE_NAME = 'website_payment_calculator_settings';

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

        DB::table(self::TABLE_NAME)
            ->where('website_id', '!=', $website->getKey())
            ->update([
                'inventory_category_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Reverse migration
    }
}
