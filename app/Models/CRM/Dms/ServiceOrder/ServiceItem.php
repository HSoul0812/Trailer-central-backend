<?php


namespace App\Models\CRM\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceItem
 * @package App\Models\CRM\Dms\ServiceOrder
 * @property ServiceOrder $serviceOrder
 * @property LaborCode $laborCode
 * @property Collection<ServiceItemTechnician> $technicians
 */
class ServiceItem extends Model
{
    use TableAware;

    protected $table = 'dms_service_item';

    public $timestamps = false;

    public function laborCode()
    {
        return $this->hasOne(LaborCode::class, 'id', 'labor_code_id');
    }

    public function technicians()
    {
        return $this->hasMany(ServiceItemTechnician::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class, 'repair_order_id', 'id');
    }
}
