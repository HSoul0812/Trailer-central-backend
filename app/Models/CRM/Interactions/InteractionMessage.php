<?php

namespace App\Models\CRM\Interactions;

use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use App\Models\CRM\Interactions\Facebook\Message as FbMessage;

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
 * @property EmailHistory|TextLog|FbMessage $message
 */
class InteractionMessage extends Model
{
    use Searchable, CustomSearch;

    const MESSAGE_TYPE_EMAIL = 'email';
    const MESSAGE_TYPE_SMS = 'sms';
    const MESSAGE_TYPE_FB = 'fb';

    const MESSAGE_TYPES = [
        self::MESSAGE_TYPE_EMAIL,
        self::MESSAGE_TYPE_SMS,
        self::MESSAGE_TYPE_FB,
    ];

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
            $lead = $message->lead;
            $leadId = $message->lead_id;
            $dateSent = $message->date_sent;

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
            $array['user_name'] = null;
        }

        if ($this->tb_name === EmailHistory::getTableName()) {
            $lead = $message->lead;
            $leadId = $message->lead_id;
            $dateSent = $message->date_sent;

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
            $array['user_name'] = null;
        }

        if ($this->tb_name === FbMessage::getTableName()) {
            $lead = $message->conversation->lead;
            $leadId = $lead->lead_id;
            $dateSent = $message->created_at;

            $array['interaction_id'] = $message->interaction_id;
            $array['text'] = $message->message;
            $array['date_delivered'] = $message->created_at;
            $array['user_name'] = $message->conversation->fbUser->name;

            $array['parent_message_id'] = null;
            $array['title'] = null;
            $array['from_email'] = null;
            $array['to_email'] = null;
            $array['from_name'] = null;
            $array['to_name'] = null;
            $array['from_number'] = null;
            $array['to_number'] = null;
        }

        if (empty($dateSent)) {
            $dateSent = null;
        } elseif (!$dateSent instanceof \DateTimeInterface)  {
            $dateSent = new Carbon($dateSent);
        }

        $array['date_sent'] = $dateSent;
        $array['lead_id'] = $leadId;
        $array['message_created_at'] = $message->created_at;
        $array['message_updated_at'] = $message->updated_at;

        $array['lead_first_name'] = $lead->first_name;
        $array['lead_last_name'] = $lead->last_name;

        $array['dealer_id'] = $lead->website->dealer_id ?? $lead->dealer_id;

        return $array;
    }
}
