<?php

declare(strict_types=1);

namespace App\Models\CRM\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $employee_id
 * @property DateTimeInterface $punch_in
 * @property DateTimeInterface $punch_out
 * @property string $metadata
 * @property DateTimeInterface|string $created_at
 * @property DateTimeInterface|string $update_at
 *
 * @property-read  Employee $employee
 *
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder select($select = null)
 * @method static self create(array $properties)
 */
class TimeClock extends Model
{
    use TableAware;

    public const TICKING = 'ticking';
    public const NOT_TICKING = 'not-ticking';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_employee_time_clock';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'punch_in',
        'metadata',
    ];

    public static function punchIn(int $employeeId, ?string $metaData = null): TimeClock
    {
        return self::create(['employee_id' => $employeeId, 'metadata' => $metaData, 'punch_in' => Date::now()]);
    }

    public function punchOut(): TimeClock
    {
        $this->punch_out = Date::now();
        $this->save();

        return $this;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
