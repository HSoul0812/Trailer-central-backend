<?php

namespace App\Models\CRM\Dealer;

use Illuminate\Database\Eloquent\Model;
use App\Models\Marketing\Facebook\Error as FBError;

class DealerFBMOverview extends Model
{
    protected $table = 'dealer_fbm_overview';

    protected $dates = [
        'last_attempt_ts',
        'last_success_ts',
    ];
}
