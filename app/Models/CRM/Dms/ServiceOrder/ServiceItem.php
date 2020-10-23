<?php


namespace App\Models\CRM\Dms\ServiceOrder;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceItem
 * @package App\Models\CRM\Dms\ServiceOrder
 * @property LaborCode $laborCode
 * @property Collection<ServiceItemTechnician> $technicians
 */
class ServiceItem extends Model
{
    protected $table = "dms_service_item";

    public function laborCode()
    {
        return $this->hasOne(LaborCode::class, 'id', 'labor_code_id');
    }

    public function technicians()
    {
        return $this->hasMany(ServiceItemTechnician::class, 'id', 'service_item_id');
    }
}
