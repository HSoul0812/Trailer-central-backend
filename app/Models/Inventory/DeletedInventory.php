<?php

namespace App\Models\Inventory;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeletedInventory
 * @package App\Models\Inventory
 *
 * @property string $vin
 * @property int $dealerId
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 */
class DeletedInventory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deleted_inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vin',
        'dealer_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
