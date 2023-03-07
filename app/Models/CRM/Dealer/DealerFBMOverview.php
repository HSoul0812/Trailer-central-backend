<?php

namespace App\Models\CRM\Dealer;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class DealerFBMOverview extends Model
{
    use TableAware;

    const TABLE_NAME = 'dealer_fbm_overview';


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    protected $dates = [
        'last_attempt_ts',
        'last_success_ts',
        'last_known_error_ts',
    ];
}
