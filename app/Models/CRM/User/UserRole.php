<?php

namespace App\Models\CRM\User;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRole
 * @package App\Models\CRM\User
 */
class UserRole extends Model
{
    use CompositePrimaryKeys;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_user_role';

    protected $primaryKey = ['user_id', 'role_id'];

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
    ];
}
