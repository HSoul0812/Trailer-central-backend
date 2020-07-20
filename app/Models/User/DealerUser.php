<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class DealerUser extends Model
{
    const TABLE_NAME = 'dealer_users';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = "dealer_user_id";
    
    public function getAccessTokenAttribute()
    {
        $authToken = AuthToken::where('user_id', $this->dealer_id)->firstOrFail();
        return $authToken->access_token;
    }
    
    public function dealer()
    {
        return $this->hasOne(User::class, 'dealer_id', 'dealer_id');
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }


    /**
     * Get By Sales Person
     * 
     * @param int $salesPersonId
     */
    public static function getBySalesPerson($salesPersonId) {
        // Get Dealer User By Sales Person
        return self::select(self::getTableName().'.*')
            ->leftJoin(DealerUserPermission::getTableName(), DealerUserPermission::getTableName() . '.dealer_user_id', '=', self::getTableName() . '.dealer_user_id')
            ->where(DealerUserPermission::getTableName() . '.feature', 'crm')
            ->where(DealerUserPermission::getTableName() . '.permission_level', $salesPersonId)
            ->first();
    }
}
