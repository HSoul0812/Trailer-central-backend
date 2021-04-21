<?php

declare(strict_types=1);

use App\Models\CRM\Dms\Quickbooks\Item;
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
        $charged_types = [
            DealerLocationQuoteFee::CHARGED_TYPE_INCOME,
            DealerLocationQuoteFee::CHARGED_TYPE_LIABILITY,
            DealerLocationQuoteFee::CHARGED_TYPE_COMBINED
        ];

        Schema::table('dealer_location_quote_fee', static function (Blueprint $table) use ($charged_types) {
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
                ->after('income_acc_id');
        });

        Schema::table('qb_items', static function (Blueprint $table) use ($charged_types) {
            $table->enum('fee_charged_type', $charged_types)
                ->nullable()
                ->after('vendor_id')
                ->comment('Specific column for fees, used to determine which QBO account the amount need to be sent');
        });

        Schema::table('qb_items_new', static function (Blueprint $table)  {
            $table->integer('liability_acc_id')
                ->nullable()
                ->after('income_acc_id');
        });

        DB::table('qb_items')
            ->where('type', Item::ITEM_TYPES['ADD_ON'])
            ->update(['fee_charged_type' => DealerLocationQuoteFee::CHARGED_TYPE_DEFAULT]);
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

        Schema::table('qb_items', static function (Blueprint $table) {
            $table->dropColumn('fee_charged_type');
        });

        Schema::table('qb_items_new', static function (Blueprint $table)  {
            $table->dropColumn('liability_acc_id');
        });
    }
}
