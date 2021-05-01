<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\Blast;

/**
 * Class Email Template
 *
 * @package App\Models\CRM\Email
 */
class Template extends Model
{
    protected $table = 'crm_email_template';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'template_key',
        'html',
        'name',
        'template',
        'template_metadata',
        'template_json',
        'custom_template_name',
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

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