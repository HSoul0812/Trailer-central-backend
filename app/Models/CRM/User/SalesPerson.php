<?php

namespace App\Models\CRM\User;

use App\Models\CRM\Dms\GenericSaleInterface;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Pos\Sale;
use App\Models\User\CrmUser;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalesPerson
 * @package App\Models\CRM\User
 * @property Collection<Sale> $posSales
 * @property Collection<GenericSaleInterface> $allSales
 */
class SalesPerson extends Model implements Filterable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_sales_person';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

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
}
