<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DealerUserPermission extends Model
{
    const TABLE_NAME = 'dealer_user_permissions';
    
    public const DMS_PERMISSIONS = [
        'back_office',
        'pos',
        'purchase_orders',
        'quotes',
        'service',
        'time_clock'
    ];
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_user_id',
        'feature',
        'permission_level'
    ];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $timestamps = false;

    public function dealerUser()
    {
        return $this->hasOne(DealerUser::class, 'dealer_user_id', 'dealer_user_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
