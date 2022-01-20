<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryFiltersToRvtOutFeed extends Migration
{

    // GET THE 2 RVTRADER INTEGRATION ID
    private const INTEGRATIONS = [2, 61];

    private const ACTUAL_FILTER = [
        'filters' => 'a:1:{i:0;a:2:{s:6:"filter";a:8:{i:0;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_a";s:8:"operator";s:2:"or";}i:1;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_b";s:8:"operator";s:2:"or";}i:2;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_c";s:8:"operator";s:2:"or";}i:3;a:3:{s:5:"field";s:8:"category";s:5:"value";s:12:"camper_popup";s:8:"operator";s:2:"or";}i:4;a:3:{s:5:"field";s:8:"category";s:5:"value";s:10:"camping_rv";s:8:"operator";s:2:"or";}i:5;a:3:{s:5:"field";s:8:"category";s:5:"value";s:3:"toy";s:8:"operator";s:2:"or";}i:6;a:3:{s:5:"field";s:8:"category";s:5:"value";s:19:"fifth_wheel_campers";s:8:"operator";s:2:"or";}i:7;a:3:{s:5:"field";s:8:"category";s:5:"value";s:14:"ice-fish_house";s:8:"operator";s:2:"or";}}s:8:"operator";s:3:"and";}}'
    ];


    private const ADD_PARK_MODEL_FILTER = [
        'filters' => 'a:1:{i:0;a:2:{s:6:"filter";a:9:{i:0;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_a";s:8:"operator";s:2:"or";}i:1;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_b";s:8:"operator";s:2:"or";}i:2;a:3:{s:5:"field";s:8:"category";s:5:"value";s:7:"class_c";s:8:"operator";s:2:"or";}i:3;a:3:{s:5:"field";s:8:"category";s:5:"value";s:12:"camper_popup";s:8:"operator";s:2:"or";}i:4;a:3:{s:5:"field";s:8:"category";s:5:"value";s:10:"camping_rv";s:8:"operator";s:2:"or";}i:5;a:3:{s:5:"field";s:8:"category";s:5:"value";s:3:"toy";s:8:"operator";s:2:"or";}i:6;a:3:{s:5:"field";s:8:"category";s:5:"value";s:19:"fifth_wheel_campers";s:8:"operator";s:2:"or";}i:7;a:3:{s:5:"field";s:8:"category";s:5:"value";s:14:"ice-fish_house";s:8:"operator";s:2:"or";}i:8;a:3:{s:5:"field";s:8:"category";s:5:"value";s:10:"park_model";s:8:"operator";s:2:"or";}}s:8:"operator";s:3:"and";}}'
        ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $checkIntegration = DB::table('integration')->whereIn('integration_id', self::INTEGRATIONS)->exists();

        if (!$checkIntegration) {
            DB::transaction(static function () {
                DB::table('integration')->whereIn('integration_id', self::INTEGRATIONS)->update(self::ADD_PARK_MODEL_FILTER);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('integration')->whereIn('integration_id', self::INTEGRATIONS)->update(self::ACTUAL_FILTER);
    }
}
