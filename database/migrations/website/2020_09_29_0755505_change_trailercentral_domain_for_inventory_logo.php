<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Website\Config\WebsiteConfig;

class ChangeTrailercentralDomainForInventoryLogo extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = WebsiteConfig::where('key', WebsiteConfig::INVENTORY_PRINT_LOGO_KEY)->whereRaw("value <> ''")->get();
        foreach($config as $configValue) {
            $configValue->value = str_replace('https://trailercentral.com', 'https://dashboard.trailercentral.com', str_replace('www.trailercentral.com', 'dashboard.trailercentral.com', $configValue->value));
            $configValue->save();            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
