<?php

namespace App\Models\CRM\User;

use App\Models\Traits\TableAware;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CRM Settings
 * @package App\Models\CRM\User
 *
 * @property $setting_id
 * @property $user_id
 * @property $type
 * @property $key
 * @property $value
 *
 * @property-read Settings $settings
 */
class Settings extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_settings';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'setting_id';

    /**
     * Disable Timestamps
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * All Fillable Columns
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key',
        'value'
    ];


    /**
     * CRM User
     * 
     * @return BelongsTo
     */
    public function crmUser(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * New Dealer User
     * 
     * @return BelongsTo
     */
    public function newDealerUser(): BelongsTo
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }

    /**
     * Dealer
     * 
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->newDealerUser()->user;
    }
}
