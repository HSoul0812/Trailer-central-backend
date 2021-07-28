<?php

namespace App\Models\CRM\User;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRole
 * @package App\Models\CRM\User
 */
class TimeClock extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_user_time_clock';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'punch_in',
        'punch_out',
        'metadata',
    ];
}
