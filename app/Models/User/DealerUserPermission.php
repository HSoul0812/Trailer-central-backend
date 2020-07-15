<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class DealerUserPermission extends Model
{
    const TABLE_NAME = 'dealer_user_permissions';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function dealerUser()
    {
        return $this->hasOne(DealerUser::class, 'dealer_user_id', 'dealer_user_id');
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
