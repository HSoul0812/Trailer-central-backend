<?php

namespace App\Models\CRM\Dms\ServiceOrder;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Type
 * @package App\Models\CRM\Dms\ServiceOrder
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $sort_order
 */
class Type extends Model
{
    protected $table = 'dms_repair_order_type';
}
