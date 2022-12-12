<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class InventoryMfg extends Model
{
    use TableAware;

    /**
     *
     */
    public const TABLE_NAME = 'inventory_mfg';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'label',
        'note',
        'vendor_id',
    ];
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @return string
     */
    public static function getTableName(): string {
        return self::TABLE_NAME;
    }

}
