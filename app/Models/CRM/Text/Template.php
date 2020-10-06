<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\Blast;

/**
 * Class Text Template
 *
 * @package App\Models\CRM\Text
 */
class Template extends Model
{
    protected $table = 'crm_text_template';

    // Constant to Handle Reply STOP
    const REPLY_STOP = "\n\nReply \"STOP\" if you do not want to receive texts and promos from \"{dealer_name}\"";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'template',
        'deleted',
    ];

    /**
     * Get CRM User
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Dealer User
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }

    /**
     * @return type
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * @return type
     */
    public function blasts()
    {
        return $this->hasMany(Blast::class);
    }
}