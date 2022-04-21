<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Email\Campaign;
use App\Models\CRM\Email\Blast;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Email Template
 *
 * @package App\Models\CRM\Email
 */
class Template extends Model
{
    use TableAware;

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
     * 
     * @return BelongsTo
     */
    public function crmUser(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Dealer User
     * 
     * @return BelongsTo
     */
    public function newDealerUser(): BelongsTo
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Campaigns Using Template
     * 
     * @return HasMany
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'email_template_id', 'id');
    }

    /**
     * Get Blasts Using Template
     * 
     * @return HasMany
     */
    public function blasts(): HasMany
    {
        return $this->hasMany(Blast::class, 'email_template_id', 'id');
    }
}