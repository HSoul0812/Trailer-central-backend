<?php

namespace App\Models\CRM\Leads\Jotform;

use App\Models\CRM\Leads\Lead;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WebsiteFormSubmissions
 *
 * @package App\Models\CRM\Leads\Jotform
 *
 * @property int lead_id
 * @property int|null merge_id
 * @property int|null trade_id
 * @property int|null submission_id
 * @property int|null jotform_id
 * @property string|null customer_id
 * @property string ip_address
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string status
 * @property int new
 * @property string answers
 * @property boolean is_ssn_removed
 * @property Lead $lead
 */

class WebsiteFormSubmissions extends Model
{
    use TableAware;

    const TABLE_NAME = 'website_form_submissions';

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

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'merge_id',
        'trade_id',
        'submission_id',
        'jotform_id',
        'customer_id',
        'ip_address',
        'created_at',
        'updated_at',
        'status',
        'new',
        'answers',
        'is_ssn_removed',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_ssn_removed' => 'boolean',
        'answers' => 'json',
    ];

    /**
     * Get Lead.
     *
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'identifier');
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
