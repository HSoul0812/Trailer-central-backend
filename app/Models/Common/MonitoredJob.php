<?php

declare(strict_types=1);

namespace App\Models\Common;

use App\Contracts\Support\DTO;
use App\Models\Observers\Common\MonitoredJobObserver;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Represents a monitored job.
 *
 * It is useful for prevent concurrent access to resources shared by a job, or just to prevent excessive use of resources
 *
 * @property string $token the primary key value for this message, it could be provided by the creator
 * @property string $queue_job_id the queue job id belonging to this monitored job. It is unique
 * @property string $queue the name of the queue
 * @property string $concurrency_level the allowed concurrency level, it could be: by-dealer, by-job, without-restrictions
 * @property int $dealer_id the dealer id who launched it
 * @property string $name the key name of the job
 * @property string $status it could be: processing, completed, or failed
 * @property float $progress progress between 0 to 100
 * @property MonitoredJobPayload $payload json data useful for handle the job
 * @property MonitoredJobResult $result json data resulting
 * @property string $created_at when the job was created
 * @property string $updated_at when the job was last updated
 * @property string $finished_at when the job was finished (or failed)
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Collection|static create(array $attributes = [])
 * @method static static findOrFail($id, $columns = ['*'])
 */
class MonitoredJob extends Model
{
    use TableAware;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * By dealer concurrency level
     */
    public const LEVEL_BY_DEALER = 'by-dealer';

    /**
     * By job concurrency level
     */
    public const LEVEL_BY_JOB = 'by-job';

    /**
     * Without restrictions concurrency level
     */
    public const LEVEL_WITHOUT_RESTRICTIONS = 'without-restrictions';

    /**
     * Default concurrency level
     */
    public const LEVEL_DEFAULT = self::LEVEL_WITHOUT_RESTRICTIONS;

    public const QUEUE_NAME = 'default';

    public const QUEUE_JOB_NAME = 'default-monitored-job';

    /**
     * @var string
     */
    protected $table = 'monitored_job';

    /**
     * @var string
     */
    protected $primaryKey = 'token';

    /**
     * @var bool
     */
    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var callable
     */
    private $queueableJobDefinition;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'dealer_id',
        'payload',
        'queue',
        'concurrency_level',
        'name',
        'result',
        'queue_job_id',
        'status'
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'status',
        'progress',
        'created_at',
        'updated_at',
        'finished_at',
        'result'
    ];

    /**
     * @param callable $lambda
     * @return self
     */
    public function withQueueableJob(callable $lambda): self
    {
        $this->queueableJobDefinition = $lambda;

        return $this;
    }

    public function hasQueueableJob(): bool
    {
        return is_callable($this->queueableJobDefinition);
    }

    /**
     * @return callable|null
     */
    public function getQueueableJob(): ?callable
    {
        return $this->queueableJobDefinition;
    }
        /**
     * Payload mutator
     *
     * @param array $value
     */
    public function setPayloadAttribute(array $value): void
    {
        $this->attributes['payload'] = json_encode($value);
    }

    /**
     * Payload accessor
     *
     * @param string|null $value
     * @return MonitoredJobPayload
     */
    public function getPayloadAttribute(?string $value): DTO
    {
        return MonitoredJobPayload::from(json_decode($value, true));
    }

    /**
     * Result mutator
     *
     * @param array $value
     */
    public function setResultAttribute(array $value): void
    {
        $this->attributes['result'] = json_encode($value);
    }

    /**
     * Result accessor
     *
     * @param string|null $value
     * @return MonitoredJobResult
     */
    public function getResultAttribute(?string $value): DTO
    {
        return MonitoredJobResult::from(json_decode($value, true));
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING || $this->status === self::STATUS_PROCESSING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public static function boot(): void
    {
        parent::boot();
        self::observe(new MonitoredJobObserver());
    }
}
