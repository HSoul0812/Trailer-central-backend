<?php

namespace App\Models\CRM\User;

use App\Models\CRM\Dms\UnitSale;
use App\Models\Pos\Sale;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalesPerson
 * @package App\Models\CRM\User
 * @property Collection<Sale> $posSales
 * @property Collection<GenericSaleInterface> $allSales
 */
class SalesPerson extends Model implements Filterable
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'dealer_location_id',
        'perms',
        'first_name',
        'last_name',
        'email'
    ];

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * Define Type Arrays
     *
     * @var array
     */
    const TYPES_DEFAULT   = ['general', 'manual'];
    const TYPES_INVENTORY = ['craigslist', 'inventory', 'call'];
    const TYPES_VALID     = ['default', 'inventory', 'financing', 'trade'];


    /**
     * Get the sales person's full name
     * 
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'user_id');
    }

    public function crmUser()
    {
        return $this->hasOne(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get new dealer user
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'id');
    }

    public function posSales()
    {
        return $this->hasMany(Sale::class, 'sales_person_id');
    }

    public function unitSales()
    {
        return $this->hasMany(UnitSale::class, 'sales_person_id');
    }

    /**
     * @return Collection<GenericSaleInterface>
     */
    public function allSales() {
        return $this->posSales->merge($this->unitSales);
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
