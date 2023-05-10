<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Interactions\EmailHistory;

/**
 * Class Email Blast Sent
 *
 * @package App\Models\CRM\Email
 */
class BlastSent extends Model
{
    use TableAware, CompositePrimaryKeys;

    const TABLE_NAME = 'crm_email_blasts_sent';

    protected $table = self::TABLE_NAME;

    /**
     * Composite Primary Key
     *
     * @var array<string>
     */
    protected $primaryKey = ['email_blasts_id', 'lead_id'];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_blasts_id',
        'lead_id',
        'message_id',
        'crm_email_history_id',
    ];

    /**
     * Get Email History
     *
     * @return BelongsTo
     */
    public function history()
    {
        return $this->belongsTo(EmailHistory::class, 'crm_email_history_id', 'email_id');
        // return $this->belongsTo(EmailHistory::class, 'message_id', 'message_id');
    }

    /**
     * Query scope of Delivered Email
     */
    public function scopeDelivered($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_delivered');
        });
    }

    /**
     * Query scope of Bounced Email
     */
    public function scopeBounced($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_bounced');
        });
    }

    /**
     * Query scope of Complained Email
     */
    public function scopeComplained($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_complained');
        });
    }

    /**
     * Query scope of Unsubscribed Email
     */
    public function scopeUnsubscribed($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_unsubscribed');
        });
    }

    /**
     * Query scope of Opened Email
     */
    public function scopeOpened($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_opened');
        });
    }

    /**
     * Query scope of Clicked Email
     */
    public function scopeClicked($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->whereNotNull('date_clicked');
        });
    }

    /**
     * Query scope of Skipped Email
     */
    public function scopeSkipped($query)
    {
        return $query->whereHas('history', function($q) {
            return $q->where('was_skipped', 1);
        });
    }
}
