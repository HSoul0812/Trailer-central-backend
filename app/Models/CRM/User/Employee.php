<?php

declare(strict_types=1);

namespace App\Models\CRM\User;

use App\Helpers\StringHelper;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\Traits\TableAware;
use App\Models\User\DealerUser;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User as Dealer;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $dealer_id
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property DateTimeInterface $birthday
 * @property string $ssn
 * @property string $address
 * @property string $email
 * @property string $phone
 * @property string $job_title
 * @property float $salary
 * @property float $hourly_rate
 * @property float $commission_rate
 * @property integer $crm_user_id
 * @property integer $service_user_id
 * @property integer $qb_id
 * @property integer $is_timeclock_user
 *
 * @property-read Dealer $dealer
 * @property-read DealerUser $user
 * @property-read Technician $technician
 * @property-read TimeClock $timeClock
 *
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null find($id, $columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder select($select = null)
 * @method static self create(array $properties)
 */
class Employee extends Model
{
    use TableAware;

    protected $table = 'dealer_employee';

    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'first_name',
        'last_name',
        'display_name',
        'birthday',
        'ssn',
        'address',
        'email',
        'phone',
        'job_title',
        'salary',
        'hourly_rate',
        'commission_rate',
        'crm_user_id',
        'service_user_id',
        'qb_id',
        'is_timeclock_user'
    ];

    public function setFirstNameAttribute(string $value): void
    {
        $this->attributes['first_name'] = StringHelper::trimWhiteSpaces($value);
    }

    public function setLastNameAttribute(string $value): void
    {
        $this->attributes['last_name'] = StringHelper::trimWhiteSpaces($value);
    }

    public function setDisplayNameAttribute(string $value): void
    {
        $this->attributes['display_name'] = StringHelper::trimWhiteSpaces($value);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'dealer_id', 'dealer_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(DealerUser::class, 'dealer_user_id', 'crm_user_id');
    }

    public function technician(): HasOne
    {
        return $this->hasOne(Technician::class, 'id', 'service_user_id');
    }

    public function timeClock(): HasOne
    {
        return $this->hasOne(TimeClock::class, 'employee_id', 'id')->orderBy('punch_in', 'DESC');
    }
}
