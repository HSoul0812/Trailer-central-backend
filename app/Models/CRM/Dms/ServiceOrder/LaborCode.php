<?php

namespace App\Models\CRM\Dms\ServiceOrder;

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
    const TABLE_NAME = 'dms_settings_labor_code';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;
}
