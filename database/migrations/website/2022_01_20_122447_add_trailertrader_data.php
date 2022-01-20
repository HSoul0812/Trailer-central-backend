<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\Export\LeadEmail;

class AddTrailertraderData extends Migration
{
    private const WEBSITE_CONFIG_PARAMS = [
        'key' => 'general/item_email_from',
        'website_id' => 284,
        'value' => 'trailer_trader'
    ];
  
    private const WEBSITE_CONFIG_DEFAULT_OLD_PARAMS = [
      'key' => 'general/item_email_from',
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
         }'
    ];
    
    private const WEBSITE_CONFIG_DEFAULT_PARAMS = [
        'key' => 'general/item_email_from',
        'values' => '{"trailer_central": "Trailer Central", "operate_beyond": "Operate Beyond", "trailer_trader": "Trailer Trader"}',
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
            },
            "trailer_trader": {
            "logo": "https://dealer-cdn.com/skin/classified/responsive/trailertraders-com/images/logo.png", 
            "fromEmail": "postmaster@trailertrader.com",
            "fromName": "Trailer Trader", 
            "logoUrl": "https://www.trailertrader.com/"

            }
         }'
    ];

    private const DEALER_LOCATION_PARAMS = [
        'dealer_id' => 1002,
        'name' => 'Trailer Trader',
        'address' => '401 Hall St SW',
        'city'    => 'Grand Rapid',
        'postalcode' => '49503',
        'country' => 'US',
        'email'   => 'trailertrader@trailertrader.com',
        'is_default' => 1
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::table('website_config')->updateOrInsert(self::WEBSITE_CONFIG_PARAMS, self::WEBSITE_CONFIG_PARAMS);
      DB::table('website_config_default')->where('key', self::WEBSITE_CONFIG_DEFAULT_PARAMS['key'])->update(self::WEBSITE_CONFIG_DEFAULT_PARAMS);
      $dealer_location = DealerLocation::create(self::DEALER_LOCATION_PARAMS);
      
      $leadEmailParams = [
        'dealer_id' => 1002,
        'email' => 'test@test.com',
        'dealer_location_id' => $dealer_location['dealer_location_id']
      ];
      
      LeadEmail::create($leadEmailParams);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config')->where('key', self::WEBSITE_CONFIG_PARAMS['key'])->where('website_id', self::WEBSITE_CONFIG_PARAMS['website_id'])->delete();
        DB::table('website_config_default')->where('key', self::WEBSITE_CONFIG_DEFAULT_OLD_PARAMS['key'])->update(self::WEBSITE_CONFIG_DEFAULT_OLD_PARAMS);
        
        $dealerLocation = DealerLocation::where('dealer_id', self::DEALER_LOCATION_PARAMS['dealer_id'])->where('name', self::DEALER_LOCATION_PARAMS['name'])->first();
        LeadEmail::where('dealer_id', $dealerLocation['dealer_id'])->where('dealer_location_id', $dealerLocation['dealer_location_id'])->delete();
        DB::table('dealer_location')->where('dealer_id', self::DEALER_LOCATION_PARAMS['dealer_id'])->where('name', self::DEALER_LOCATION_PARAMS['name'])->delete();
    }
}
