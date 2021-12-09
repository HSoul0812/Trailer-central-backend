<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomOverlay
 * @package App\Models\Inventory
 *
 * @property int $id,
 * @property string $name,
 * @property string $value,
 * @property int $dealer_id
 *
 * @method self create(array $attributes = [])
 */
class CustomOverlay extends Model
{
    public const VALID_CUSTOM_NAMES = [
        'overlay_1',
        'overlay_2',
        'overlay_3',
        'overlay_4',
        'overlay_5',
        'overlay_6',
        'overlay_7',
        'overlay_8',
        'overlay_9',
        'overlay_10'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'custom_overlays';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
        'dealer_id'
    ];
}
