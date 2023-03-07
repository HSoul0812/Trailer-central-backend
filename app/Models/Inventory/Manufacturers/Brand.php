<?php

namespace App\Models\Inventory\Manufacturers;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use TableAware;

    /**
     *
     */
    public const TABLE_NAME = 'manufacturer_brands';

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
        'manufacturer_id',
        'name',
    ];
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'brand_id';

    /**
     * @return string
     */
    public static function getTableName(): string {
        return self::TABLE_NAME;
    }

}
