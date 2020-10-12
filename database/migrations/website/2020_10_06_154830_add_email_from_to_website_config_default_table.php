<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEmailFromToWebsiteConfigDefaultTable extends Migration
{
    private const EMAIL_FROM_OPTION = [
        'key' => 'general/item_email_from',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Email logo',
        'note' => 'It allows to set logo and FROM name on emails',
        'grouping' => 'General',
        'values' => '{"trailer_central": "Trailer Central", "operate_beyond": "Operate Beyond"}',
        'values_mapping' => '{
            "trailer_central": {
                "logo": "https://dashboard.trailercentral.com/images/logo2.png",
                "fromEmail": "postmaster@trailercentral.com",
                "fromName": "Trailer Central",
                "logoUrl": "https://www.trailercentral.com/"
             },
            "operate_beyond": {
                "logo": "https://operatebeyond.com/wp-content/themes/trailer/assets/img/ob-web-dark.png",
                "fromEmail": "no-reply@operatebeyond.com",
                "fromName": "Operate Beyond",
                "logoUrl": "https://operatebeyond.com/"
            }
         }',
        'default_label' => '',
        'default_value' => 'trailer_central',
        'sort_order' => 1200,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::EMAIL_FROM_OPTION);

        $result = DB::select(DB::raw("
            SELECT d.dealer_id, COUNT(i.inventory_id) count_with_1_2_7, ii.count_without_1_2_7 count_without_1_2_7
            FROM inventory i
            JOIN dealer d ON d.dealer_id = i.dealer_id
            JOIN (
                SELECT dealer_id, COUNT(inventory_id) count_without_1_2_7
                FROM inventory WHERE entity_type_id NOT IN (1,2,7)
                GROUP BY dealer_id
            ) ii ON ii.dealer_id = d.dealer_id
            WHERE i.entity_type_id IN (1,2,7)
            GROUP BY d.dealer_id
            HAVING count_with_1_2_7 < count_without_1_2_7
        "));

        $dealerIds = array_column(array_map(function ($item) {
            return (array)$item;
        }, $result), 'dealer_id');

        $websiteIds = DB::table('website')
            ->select('id')
            ->whereIn('dealer_id', $dealerIds)
            ->pluck('id');

        foreach ($websiteIds as $websiteId) {
            $websiteConfigParams = [
                'website_id' => $websiteId,
                'key' => self::EMAIL_FROM_OPTION['key'],
                'value' => 'operate_beyond',
            ];

            DB::table('website_config')->insert($websiteConfigParams);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::EMAIL_FROM_OPTION['key'])->delete();
    }
}
