<?php

namespace App\Models\CRM\Interactions;

use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class EmailHistory
 * @package App\Models\CRM\Interactions
 *
 * @property int $email_id
 * @property int $lead_id
 * @property int|null $interaction_id
 * @property int $message_id
 * @property int|null $ses_message_id
 * @property int $root_message_id
 * @property int $parent_message_id
 * @property string $to_email
 * @property string $to_name
 * @property string $from_email
 * @property string $from_name
 * @property string $subject
 * @property string $body
 * @property boolean|null $use_html
 * @property \DateTimeInterface|null $date_sent
 * @property \DateTimeInterface|null $date_delivered
 * @property \DateTimeInterface|null $date_bounced
 * @property \DateTimeInterface|null $date_complained
 * @property \DateTimeInterface|null $date_unsubscribed
 * @property \DateTimeInterface|null $date_opened
 * @property \DateTimeInterface|null $date_clicked
 * @property boolean|null $invalid_email
 * @property boolean|null $was_skipped
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 * @property Lead $lead
 */
class EmailHistory extends Model
{
    use TableAware;

    const TABLE_NAME = 'crm_email_history';

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
    protected $primaryKey = 'email_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "lead_id",
        "quote_id",
        "interaction_id",
        "message_id",
        "ses_message_id",
        "root_message_id",
        "parent_message_id",
        "to_email",
        "to_name",
        "from_email",
        "from_name",
        "subject",
        "body",
        "use_html",
        "draft_saved",
        "date_sent",
        "date_delivered",
        "date_bounced",
        "date_complained",
        "date_unsubscribed",
        "date_opened",
        "date_clicked",
        "invalid_email",
        "was_skipped"
    ];

    /**
     * @const array
     */
    const REPORT_FIELDS = [
        'date_sent',
        'date_delivered',
        'date_bounced',
        'date_complained',
        'date_unsubscribed',
        'date_opened',
        'date_clicked'
    ];

    /**
     * @const array
     */
    const BOOL_FIELDS = [
        'invalid_email',
        'was_skipped'
    ];

    /**
     * Get the lead that owns the email history.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, "lead_id", "identifier");
    }

    /**
     * Get the lead that owns the email history.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(UnitSale::class, "quote_id", "id");
    }

    /**
     * Get the interaction that owns the email history.
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Lead::class, "interaction_id", "interaction_id");
    }

    /**
     * @return MorphOne
     */
    public function interactionMessage(): MorphOne
    {
        return $this->morphOne(InteractionMessage::class, 'interactionMessage', 'tb_name', 'tb_primary_id');
    }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'message_id', 'message_id');
    }
}
