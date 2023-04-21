<?php

namespace App\Models;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $batch_id
 * @property array|null $queues a valid array o monitored queues
 * @property integer $total_jobs
 * @property integer $processed_jobs
 * @property integer $failed_jobs
 * @property integer $wait_time time in seconds
 * @property array|null $context
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $finished_at
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static BatchedJob    find($id, array $columns = ['*'])
 * @method static BatchedJob    findOrFail($id, array $columns = ['*'])
 * @method static BatchedJob    first()
 * @method static BatchedJob    create(array $attributes = [])
 */
class BatchedJob extends Model
{
    use TableAware;

    private const TABLE_NAME = 'batched_job';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'batch_id';

    /**
     * Primary Key Doesn't Auto Increment
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Set String Primary Key
     *
     * @var string
     */
    protected $keyType = 'string';

    /** @var array<string> */
    protected $fillable = [
        'batch_id',
        'wait_time',
        'context',
        'queues',
        'total_jobs',
        'processed_jobs',
        'failed_jobs',
        'finished_at'
    ];

    /** @var array<string> */
    protected $dates = ['created_at', 'updated_at', 'finished_at'];

    /** @var array */
    protected $casts = [
        'context' => 'json',
        'queues' => 'json',
    ];

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
