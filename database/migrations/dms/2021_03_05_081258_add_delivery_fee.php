<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDeliveryFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `fee_type` `fee_type` ENUM('appraisal_fee','bank_fee','doc_fee','battery_fee','title_cert','state_inspection_fee','smog_cert','smog_fee','other_fee','filing_fee','lein_fee','vit','license_fee','handling_fee','freight_fee','mv_warranty_fee','vsi_fee','extended_warranty','gap_insurance','road_guard','trident','anti_theft_system','roadside_asst','paint_sealant','rust_proofing','tire_fee','notary_fee','messenger_fee','online_fee','plate_fee','processing_fee','county_fee','transfer_fee','title_registration_fee','loan_fee','dmv_fee','delivery_fee') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `fee_type` `fee_type` ENUM('appraisal_fee','bank_fee','doc_fee','battery_fee','title_cert','state_inspection_fee','smog_cert','smog_fee','other_fee','filing_fee','lein_fee','vit','license_fee','handling_fee','freight_fee','mv_warranty_fee','vsi_fee','extended_warranty','gap_insurance','road_guard','trident','anti_theft_system','roadside_asst','paint_sealant','rust_proofing','tire_fee','notary_fee','messenger_fee','online_fee','plate_fee','processing_fee','county_fee','transfer_fee','title_registration_fee',,'loan_fee','dmv_fee') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
}
