<?php

namespace App\Models\CRM\Interactions;

use App\Helpers\SanitizeHelper;
use App\Models\CRM\Leads\LeadType;
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

    protected $casts = [
        'is_read' => 'boolean',
        'hidden' => 'boolean'
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

        $helper = new SanitizeHelper();

        if (!$message) {
            return $array;
        }

        if ($this->tb_name === TextLog::getTableName()) {
            $lead = $message->lead;
            $leadId = $message->lead_id;
            $leadPhone = !empty($lead->phone_number) ? $lead->phone_number : '';
            $dateSent = $message->date_sent;
            $salesPersonIds = [];

            $array['text'] = $message->log_message;
            $array['from_number'] = $message->from_number;
            $array['to_number'] = $message->to_number;
            $array['is_incoming'] = $helper->sanitizePhoneNumber($message->from_number) === $helper->sanitizePhoneNumber($leadPhone);
            $array['files'] = $message->files ? $message->files->toArray() : [];

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
            $leadEmail = !empty($lead->email_address) ? $lead->email_address : '';
            $dateSent = $message->date_sent ?? $message->created_at;
            $salesPersonIds = [];

            $array['interaction_id'] = $message->interaction_id;
            $array['parent_message_id'] = $message->parent_message_id;
            $array['title'] = $message->subject;
            $array['text'] = $message->body;
            $array['from_email'] = $message->from_email;
            $array['to_email'] = $message->to_email;
            $array['from_name'] = $message->from_name;
            $array['to_name'] = $message->to_name;
            $array['date_delivered'] = $message->date_delivered;
            $array['is_incoming'] = strcasecmp($leadEmail, $message->from_email) === 0;

            $array['from_number'] = null;
            $array['to_number'] = null;
            $array['user_name'] = null;
            $array['files'] = [];
        }

        if ($this->tb_name === FbMessage::getTableName()) {
            $lead = $message->conversation->lead;
            $leadId = $lead->identifier;
            $dateSent = $message->created_at;

            if ($message->conversation->chat && $message->conversation->chat->salesPersons->isNotEmpty()) {
                $salesPersonIds = $message->conversation->chat->salesPersons->pluck('id')->toArray();
            } else {
                $salesPersonIds = [];
            }

            $array['interaction_id'] = $message->interaction_id;
            $array['text'] = $message->message;
            $array['date_delivered'] = $message->created_at;
            $array['user_name'] = $message->conversation->fbUser->name;
            $array['is_incoming'] = strcmp($message->from_id, $message->conversation->user_id) === 0;

            $array['parent_message_id'] = null;
            $array['title'] = null;
            $array['from_email'] = null;
            $array['to_email'] = null;
            $array['from_name'] = null;
            $array['to_name'] = null;
            $array['from_number'] = null;
            $array['to_number'] = null;
            $array['files'] = [];
        }

        if (!$dateSent instanceof \DateTimeInterface)  {
            $dateSent = new Carbon($dateSent);
        }

        $array['date_sent'] = $dateSent;
        $array['lead_id'] = $leadId;
        $array['message_created_at'] = $message->created_at;
        $array['message_updated_at'] = $message->updated_at;

        $array['lead_first_name'] = !empty($lead->first_name) ? $lead->first_name : '';
        $array['lead_last_name'] = !empty($lead->last_name) ? $lead->last_name : '';

        $array['unassigned'] = !empty($lead->lead_type) ? ($lead->lead_type === LeadType::TYPE_NONLEAD) : true;

        $array['dealer_id'] = $lead ? ($lead->website->dealer_id ?? $lead->dealer_id) : 0;

        if (!empty($lead->leadStatus)) {
            $salesPersonIds[] = $lead->leadStatus->sales_person_id;
        }

        $array['sales_person_ids'] = $salesPersonIds;

        return $array;
    }
}
