<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\Location\Geolocation;
use App\Models\CRM\User\Customer;

class FixStatesForIndianaTrailerSales extends Migration
{
    
    const INDIANA_TRAILER_SALES_DEALER_ID = 7438;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $customers = Customer::where('dealer_id', self::INDIANA_TRAILER_SALES_DEALER_ID)->get();
        
        foreach($customers as $cust) {
            if (is_numeric($cust->region) && !empty($cust->postal_code)) {            
                $geoLocation = Geolocation::where('zip', $cust->postal_code)->first();
                if ($geoLocation) {
                    $cust->region = $geoLocation->state;
                }
            }
            
            if (is_numeric($cust->shipping_region) && !empty($cust->shipping_postal_code)) {            
                $geoLocation = Geolocation::where('zip', $cust->shipping_postal_code)->first();
                if ($geoLocation) {
                    $cust->shipping_region = $geoLocation->state;
                }
            }
            
            $cust->save();
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_settings_labor_code', function (Blueprint $table) {
            //
            $table->dropColumn('meta');
        });
    }
}
