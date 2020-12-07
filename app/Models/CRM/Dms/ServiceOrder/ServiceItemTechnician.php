<?php


namespace App\Models\CRM\Dms\ServiceOrder;


use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceItemTechnician
 * @package App\Models\CRM\Dms\ServiceOrder
 * @property ServiceItem $serviceItem
 * @property Technician $technician
 */
class ServiceItemTechnician extends Model implements Filterable
{
    protected $table = 'dms_service_technician';

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id', 'id');
    }

    public function technician()
    {
        return $this->hasOne(Technician::class, 'id', 'dms_settings_technician_id');
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['start_date', 'completed_date'];
    }
}
