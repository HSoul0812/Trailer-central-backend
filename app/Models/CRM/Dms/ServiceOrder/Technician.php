<?php

namespace App\Models\CRM\Dms\ServiceOrder;

use App\Models\Traits\TableAware;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model implements Filterable
{
    use TableAware;

    protected $table = 'dms_settings_technician';

    public $timestamps = false;

    public function jsonApiFilterableColumns(): ?array
    {
        return ['dealer_id'];
    }
}
