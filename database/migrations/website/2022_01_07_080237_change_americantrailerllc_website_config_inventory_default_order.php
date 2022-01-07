<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Website\Website;

class ChangeAmericantrailerllcWebsiteConfigInventoryDefaultOrder extends Migration
{
    private $websiteConfig;

    public function __construct()
    {
        $this->websiteConfig = Website::where('domain', 'americantrailerllc.com')->with(['websiteConfigs' => function ($query) {
            $query->where('key', 'inventory/default_order');
        }])->first()->websiteConfigs->first();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->websiteConfig->update(['value' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Revert back to the existing value
        $this->websiteConfig->update(['value' => 8]);
    }
}
