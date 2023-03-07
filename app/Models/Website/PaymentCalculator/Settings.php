<?php

namespace App\Models\Website\PaymentCalculator;

use App\Models\Inventory\EntityType;
use App\Models\Traits\TableAware;
use App\Models\Website\Website;
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
 * @property Website $website
 */
class Settings extends Model
{
    use TableAware;

    const NO_SETTINGS_AVAILABLE = [
        'apr' => null,
        'down' => null,
        'years' => null,
        'months' => null,
        'monthly_payment' => null,
        'down_percentage' => null
    ];

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
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function isLessThan(): bool
    {
        return $this->operator === self::OPERATOR_LESS_THAN;
    }

    public function isOver(): bool
    {
        return $this->operator === self::OPERATOR_OVER;
    }

    public function isNoFinancing(): bool
    {
        return $this->financing === self::NO_FINANCING;
    }
}
