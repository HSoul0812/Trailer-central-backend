<?php

declare(strict_types=1);

namespace App\Models\CRM\Dms\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $inventory_id
 * @property string $created_at
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Collection|CustomerInventory create(array $attributes = [])
 */
class CustomerInventory extends Model
{
    public const TABLE_NAME = 'dms_customer_inventory';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'inventory_id'
    ];

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
