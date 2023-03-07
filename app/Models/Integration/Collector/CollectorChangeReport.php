<?php

namespace App\Models\Integration\Collector;

use App\Models\User\NovaUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CollectorChangeReport
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_id
 * @property int $user_id
 * @property string $changes
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 */
class CollectorChangeReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_change_report';

    protected $fillable = [
        'collector_id',
        'user_id',
        'field',
        'changed_from',
        'changed_to',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NovaUser::class);
    }
}
