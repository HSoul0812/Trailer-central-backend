<?php

namespace App\Models\Website\PaymentCalculator;

use App\Models\Inventory\EntityType;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $website_id
 * @property int $entity_type_id
 * @property string $inventory_condition
 * @property int $months
 * @property float $apr
 * @property float $down
 * @property string $operator
 * @property float $inventory_price
 * @property string $financing
 * @property \DateTimeInterface $updated_at,
 *
 * @property EntityType $entityType
 */
class Settings extends Model
{
    use TableAware;

    const CONDITION_USED = 'used';
    const CONDITION_NEW = 'new';

    const FINANCING = 'financing';
    const NO_FINANCING = 'no_financing';

    const OPERATOR_LESS_THAN = 'less_than';
    const OPERATOR_OVER = 'over';

    const CREATED_AT = null;

    protected $table = 'website_payment_calculator_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'entity_type_id',
        'inventory_condition',
        'months',
        'apr',
        'down',
        'operator',
        'inventory_price',
        'financing'
    ];

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class,'entity_type_id');
    }
}
