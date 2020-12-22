<?php

namespace App\Models\CRM\Dms\ServiceOrder;

use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model implements Filterable
{
    protected $table = 'dms_settings_technician';

    public function jsonApiFilterableColumns(): ?array
    {
        return ['dealer_id'];
    }
}
