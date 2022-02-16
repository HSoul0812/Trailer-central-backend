<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWebsiteConfigDefaultLogo extends Migration
{
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
          "logo": "https://qa.trailertrader.com/img/TrailerTraderLogo.e353fdbc.png", 
          "fromEmail": "postmaster@trailertrader.com",
          "fromName": "Trailer Trader", 
          "logoUrl": "https://www.trailertrader.com/"

          }
       }'
  ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->where('key', self::WEBSITE_CONFIG_DEFAULT_PARAMS['key'])->update(self::WEBSITE_CONFIG_DEFAULT_PARAMS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::WEBSITE_CONFIG_DEFAULT_OLD_PARAMS['key'])->update(self::WEBSITE_CONFIG_DEFAULT_OLD_PARAMS);
    }
}
