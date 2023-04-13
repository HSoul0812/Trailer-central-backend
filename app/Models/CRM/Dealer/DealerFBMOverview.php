<?php

namespace App\Models\CRM\Dealer;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getTodaysStatus($groupBy)
    {
        return DB::table('dealer_fbm_overview')
            ->selectRaw("
                        CASE
                            WHEN last_attempt_ts < CURDATE() THEN 'not attempted'
                            WHEN last_attempt_posts = 0 THEN 'failed'
                            WHEN posts_per_day > last_attempt_posts THEN 'partial'
                            ELSE 'success'
                        END AS status_today,
                        COUNT(1) AS aggregate
                        ")
            ->groupBy($groupBy)
            ->get();
    }
}
