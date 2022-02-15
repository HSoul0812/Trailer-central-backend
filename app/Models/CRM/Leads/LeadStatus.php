<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;
use App\Models\CRM\User\SalesPerson;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LeadStatus
 * @package App\Models\CRM\Leads
 *
 * @property int $id
 * @property int $tc_lead_identifier
 * @property string $status
 * @property string $source
 * @property \DateTimeInterface $next_contact_date
 * @property int $sales_person_id
 * @property string $contact_type
 *
 * @property Lead $lead
 * @property SalesPerson $salesPerson
 */
class LeadStatus extends Model
{
    use TableAware;

    const TYPE_CONTACT = 'CONTACT';
    const TYPE_TASK = 'TASK';

    const STATUS_WON = 'Closed';
    const STATUS_WON_CLOSED = 'Closed (Won)';
    const STATUS_LOST = 'Closed (Lost)';
    const STATUS_HOT = 'Hot';
    const STATUS_COLD = 'Cold';
    const STATUS_MEDIUM = 'Medium';
    const STATUS_UNCONTACTED = 'Uncontacted';
    const STATUS_NEW_INQUIRY = 'New Inquiry';

    const STATUS_ARRAY = [
        self::STATUS_HOT,
        self::STATUS_COLD,
        self::STATUS_LOST,
        self::STATUS_MEDIUM,
        self::STATUS_NEW_INQUIRY,
        self::STATUS_UNCONTACTED,
        self::STATUS_WON_CLOSED,
        self::STATUS_WON
    ];

    const TABLE_NAME = 'crm_tc_lead_status';

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
    protected $primaryKey = 'id';

    public $timestamps = false;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tc_lead_identifier',
        'status',
        'source',
        'next_contact_date',
        'sales_person_id',
        'contact_type'
    ];

    /**
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'identifier', 'tc_lead_identifier');
    }

    /**
     * @return BelongsTo
     */
    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class, 'sales_person_id', 'id');
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
