<?php

namespace App\Models\Integration\Collector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CollectorSpecificationAction
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_specification_id
 * @property string $action
 * @property string $field
 * @property string $value
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class CollectorSpecificationAction extends Model
{
    const ACTION_MAPPING = 'mapping';
    const ACTION_SKIP_ITEM = 'skip_item';

    const ACTION_FORMATS = [
        self::ACTION_MAPPING => 'Mapping',
        self::ACTION_SKIP_ITEM => 'Skip Item'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_specification_actions';

    protected $fillable = [
        'collector_specification_id',
        'action',
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
