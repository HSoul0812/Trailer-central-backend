<?php

namespace App\Models\Integration\Collector;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CollectorSpecification
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_id
 * @property string $logical_operator
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 * @property Collection<CollectorSpecificationRule> $rules
 * @property Collection<CollectorSpecificationAction> $actions
 */
class CollectorSpecification extends Model
{
    const LOGICAL_OPERATOR_AND = 'and';
    const LOGICAL_OPERATOR_OR = 'or';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_specification';

    protected $fillable = [
        'id',
        'collector_id',
        'logical_operator',
        'created_at',
        'updated_at',
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CollectorSpecificationRule::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CollectorSpecificationAction::class);
    }
}
