<?php

namespace App\Models\CRM\Dealer;

use Illuminate\Database\Eloquent\Model;
use App\Models\Marketing\Facebook\Error as FBError;

class DealerFBMOverview extends Model
{
    protected $table = 'dealer_fbm_overview';

    protected $dates = [
        'last_run_ts'
    ];

    public function clearErrors()
    {
        $errorsCount =  FBError::where('marketplace_id', $this->marketplace_id)->where('dismissed', 0)->count();
        FBError::where('marketplace_id', $this->marketplace_id)->where('dismissed', false)->update(['dismissed' => true]);
        return $errorsCount;
    }
}
