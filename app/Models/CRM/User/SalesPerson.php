<?php

namespace App\Models\CRM\User;

use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    const TABLE_NAME = 'crm_sales_person';

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
    protected $primaryKey = 'id';
    
    /**
     * Define Type Arrays
     *
     * @var array
     */
    const TYPES_DEFAULT   = ['general', 'manual'];
    const TYPES_INVENTORY = ['craigslist', 'inventory', 'call'];
    const TYPES_VALID     = ['default', 'inventory', 'financing', 'trade'];


    /**
     * Get Full Name
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'user_id');
    }

    public function crmUser()
    {
        return $this->hasOne(CrmUser::class, 'user_id', 'user_id');
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
