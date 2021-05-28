<?php

declare(strict_types=1);

use App\Models\User\DealerLocationQuoteFee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerLocationQuoteFeeCostHandler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $cost_handlers = [
            DealerLocationQuoteFee::COST_DEFAULT_HANDLER,
            DealerLocationQuoteFee::COST_AMOUNT_HANDLER
        ];

        Schema::table('dealer_location_quote_fee', static function (Blueprint $table) use ($cost_handlers) {

            $table->enum('cost_handler', $cost_handlers)
                ->default(DealerLocationQuoteFee::COST_DEFAULT_HANDLER)
                ->after('cost_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('dealer_location_quote_fee', static function (Blueprint $table) {
            $table->dropColumn('cost_handler');
        });
    }
}
