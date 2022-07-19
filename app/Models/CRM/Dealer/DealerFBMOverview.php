<?php

namespace App\Models\CRM\Dealer;

use Illuminate\Database\Eloquent\Model;

class DealerFBMOverview extends Model
{
    protected $table = 'dealer_fbm_overview';

    protected $dates = [
        'last_run_ts'
    ];
}
