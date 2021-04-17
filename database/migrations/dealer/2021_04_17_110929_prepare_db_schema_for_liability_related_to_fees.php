<?php

declare(strict_types=1);

use App\Models\User\DealerLocationQuoteFee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrepareDbSchemaForLiabilityRelatedToFees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dealer_location_quote_fee', static function (Blueprint $table) {
            $charged_types = [
                DealerLocationQuoteFee::CHARGED_TYPE_INCOME,
                DealerLocationQuoteFee::CHARGED_TYPE_LIABILITY,
                DealerLocationQuoteFee::CHARGED_TYPE_COMBINED
            ];

            $table->decimal('cost_amount', 10, 2)
                ->nullable()
                ->default(0)
                ->after('amount');
            $table->enum('fee_charged_type', $charged_types)
                ->default(DealerLocationQuoteFee::CHARGED_TYPE_DEFAULT)
                ->after('accounting_class');
        });

        Schema::table('qb_item_category', static function (Blueprint $table) {
            $table->integer('liability_acc_id')
                ->nullable()
                ->after('income_acc_id')
                ->comment('Liability account where it needs to be sent');
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
            $table->dropColumn('cost_amount');
            $table->dropColumn('fee_charged_type');
        });

        Schema::table('qb_item_category', static function (Blueprint $table) {
            $table->dropColumn('liability_acc_id');
        });
    }
}
