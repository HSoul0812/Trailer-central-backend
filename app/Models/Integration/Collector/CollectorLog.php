<?php

namespace App\Models\Integration\Collector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CollectorLog
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_id
 * @property string $logical_operator
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class CollectorLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_log';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'collector_id',
        'new_items',
        'sold_items',
        'unsold_items',
        'archived_items',
        'unarchived_items',
        'validation_errors',
        'exception',
        'created_at',
        'updated_at'
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }
}
