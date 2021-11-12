<?php

namespace App\Models\CRM\Interactions;

use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{

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
    public function lead()
    {
        return $this->belongsTo(Lead::class, "lead_id", "identifier");
    }

    /**
     * Get the interaction that owns the email history.
     */
    public function interaction()
    {
        return $this->belongsTo(Lead::class, "interaction_id", "interaction_id");
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
