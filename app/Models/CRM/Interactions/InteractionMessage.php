<?php

namespace App\Models\CRM\Interactions;

use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * Class InteractionMessage
 * @package App\Models\CRM\Interactions
 *
 * @property int $id
 * @property string $message_type
 * @property int $tb_primary_id
 * @property string $tb_name
 * @property string|null $name
 * @property boolean $hidden
 * @property boolean $is_read
 *
 * @property EmailHistory|TextLog $message
 */
class InteractionMessage extends Model
{
    use Searchable, CustomSearch;

    const MESSAGE_TYPE_EMAIL = 'email';
    const MESSAGE_TYPE_SMS = 'sms';
    const MESSAGE_TYPE_FB = 'fb';

    protected $table = 'interaction_message';

    protected $fillable = [
        'message_type',
        'tb_primary_id',
        'tb_name',
        'name',
        'hidden',
        'is_read',
    ];

    /**
     * @return string
     */
    public function searchableAs(): string
    {
        return $this->table;
    }

    /**
     * @return MorphTo
     */
    public function message(): MorphTo
    {
        return $this->morphTo(null, 'tb_name', 'tb_primary_id');
    }

    /**
     * @return array
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $message = $this->message;

        if (!$message) {
            return $array;
        }

        if ($this->tb_name === TextLog::getTableName()) {
            $array['text'] = $message->log_message;
            $array['from_number'] = $message->from_number;
            $array['to_number'] = $message->to_number;

            $array['interaction_id'] = null;
            $array['parent_message_id'] = null;
            $array['title'] = null;
            $array['from_email'] = null;
            $array['to_email'] = null;
            $array['from_name'] = null;
            $array['to_name'] = null;
            $array['date_delivered'] = null;
        }

        if ($this->tb_name === EmailHistory::getTableName()) {
            $array['interaction_id'] = $message->interaction_id;
            $array['parent_message_id'] = $message->parent_message_id;
            $array['title'] = $message->subject;
            $array['text'] = $message->body;
            $array['from_email'] = $message->from_email;
            $array['to_email'] = $message->to_email;
            $array['from_name'] = $message->from_name;
            $array['to_name'] = $message->to_name;
            $array['date_delivered'] = $message->date_delivered;

            $array['from_number'] = null;
            $array['to_number'] = null;
        }

        $array['date_sent'] = $message->date_sent instanceof \DateTimeInterface ? $message->date_sent : null;
        $array['lead_id'] = $message->lead_id;
        $array['message_created_at'] = $message->created_at;
        $array['message_updated_at'] = $message->updated_at;

        $lead = $message->lead;

        $array['lead_first_name'] = $lead->first_name;
        $array['lead_last_name'] = $lead->last_name;

        $array['dealer_id'] = $lead->website->dealer_id ?? $lead->dealer_id;

        return $array;
    }
}
