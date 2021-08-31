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
 */
class CustomOverlay extends Model
{
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