<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateFeeTypeFieldToDealerLocationQuoteFeeTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_location_quote_fee', function (Blueprint $table) {
            $table->string('fee_type', 50)->change();
            $table->string('title', 50)->after('dealer_location_id')->nullable();
            $table->boolean('is_additional')->after('amount')->default(false);
        });

        DB::table('dealer_location_quote_fee')
            ->select('id', 'fee_type')
            ->orderBy('id')
            ->chunk(500, function ($fees) {
                foreach ($fees as $fee) {
                    $title = ucfirst(implode(' ', explode('_', $fee->fee_type)));

                    DB::table('dealer_location_quote_fee')
                        ->where(['id' => $fee->id])
                        ->update(['title' => $title]);
                }
            });

        Schema::table('dealer_location_quote_fee', function (Blueprint $table) {
            $table->string('title', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_location_quote_fee', function (Blueprint $table) {
            $table->enum('fee_type', [
                'appraisal_fee',
                'bank_fee',
                'doc_fee',
                'battery_fee',
                'title_cert',
                'state_inspection_fee',
                'smog_cert',
                'smog_fee',
                'other_fee',
                'filing_fee',
                'lein_fee',
                'vit',
                'license_fee',
                'handling_fee',
                'freight_fee',
                'mv_warranty_fee',
                'vsi_fee',
                'extended_warranty',
                'gap_insurance',
                'road_guard',
                'trident',
                'anti_theft_system',
                'roadside_asst',
                'paint_sealant',
                'rust_proofing',
                'tire_fee',
                'notary_fee',
                'messenger_fee',
                'online_fee',
                'plate_fee',
                'processing_fee',
                'county_fee',
                'transfer_fee',
                'title_registration_fee',
                'loan_fee',
                'dmv_fee',
                'delivery_fee'
            ])->change();

            $table->dropColumn('title');
            $table->dropColumn('is_additional');
        });
    }
}
