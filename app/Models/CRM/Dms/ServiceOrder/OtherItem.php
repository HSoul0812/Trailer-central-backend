<?php

namespace App\Models\CRM\Dms\ServiceOrder;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OtherItem
 *
 * @package App\Models\CRM\Dms\ServiceOrder
 */
class OtherItem extends Model
{
    public const TABLE_NAME = 'dms_other_item';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'is_custom_amount' => 'boolean',
    ];
}
