<?php

namespace App\Models\Integration\Collector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CollectorSpecificationRule
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_specification_id
 * @property string $condition
 * @property string $field
 * @property string $value
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class CollectorSpecificationRule extends Model
{
    const CONDITION_EQUAL = 'equal';
    const CONDITION_NOT_EQUAL = 'not_equal';
    const CONDITION_LT = 'lt';
    const CONDITION_GT = 'gt';
    const CONDITION_LTE = 'lte';
    const CONDITION_GTE = 'gte';
    const CONDITION_SAME = 'same';
    const CONDITION_NOT_SAME = 'not_same';
    const CONDITION_CONTAINS = 'contains';
    const CONDITION_NOT_CONTAINS = 'not_contains';

    const CONDITION_FORMATS = [
        self::CONDITION_EQUAL => 'Equal (=) [number]',
        self::CONDITION_NOT_EQUAL => 'Not Equal (!=) [number]',
        self::CONDITION_LT => 'Less Than (<) [number]',
        self::CONDITION_LTE => 'Less Than Or Equal (<=) [number]',
        self::CONDITION_GT => 'Greater Than (>) [number]',
        self::CONDITION_GTE => 'Greater Than Or Equal (>=) [number]',
        self::CONDITION_SAME => 'Same [string]',
        self::CONDITION_NOT_SAME => 'Not Same [string]',
        self::CONDITION_CONTAINS => 'Contains [string]',
        self::CONDITION_NOT_CONTAINS => 'Not Contains [string]',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_specification_rules';

    protected $fillable = [
        'collector_specification_id',
        'condition',
        'field',
        'value',
        'created_at',
        'updated_at',
    ];

    public function collectorSpecification(): BelongsTo
    {
        return $this->belongsTo(CollectorSpecification::class);
    }
}
