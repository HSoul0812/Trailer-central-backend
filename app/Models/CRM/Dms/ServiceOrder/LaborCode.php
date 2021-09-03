<?php

namespace App\Models\CRM\Dms\ServiceOrder;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LaborCode
 * @package App\Models\CRM\Dms\ServiceOrder
 *
 * @property int $id
 * @property int $dealer_id
 * @property string $name
 * @property double $hourly_rate
 * @property double $price
 * @property double $meta
 */
class LaborCode extends Model
{
    use TableAware;

    protected $table = 'dms_settings_labor_code';

    public $timestamps = false;
}
