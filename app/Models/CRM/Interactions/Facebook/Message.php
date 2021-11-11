<?php

namespace App\Models\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Message
 * @package App\Models\CRM\Interactions\Facebook
 *
 * @property int $id
 * @property int $message_id
 * @property int $conversation_id
 * @property int $interaction_id
 * @property string $from_id
 * @property string $to_id
 * @property string $message
 * @property string $tags
 * @property boolean $read
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface $deleted_at
 *
 * @property Conversation $conversation
 */
class Message extends Model
{
    use TableAware, SoftDeletes;


    /**
     * @const string Messaging Types
     */
    const MSG_TYPE_RESPONSE = 'RESPONSE';
    const MSG_TYPE_UPDATE = 'UPDATE';
    const MSG_TYPE_TAG = 'MESSAGE_TAG';
    const MSG_TYPE_DEFAULT = self::MSG_TYPE_UPDATE;

    /**
     * @const string Supported Message Tags
     */
    const MSG_TYPE_EVENT = 'CONFIRMED_EVENT_UPDATE';
    const MSG_TYPE_PURCHASE = 'POST_PURCHASE_UPDATE';
    const MSG_TYPE_ACCOUNT = 'ACCOUNT_UPDATE';
    const MSG_TYPE_HUMAN = 'HUMAN_AGENT';
    const MSG_TYPE_FEEDBACK = 'CUSTOMER_FEEDBACK';

    /**
     * @const array Messaging Types That Make Primary Type MSG_TYPE_TAG
     */
    const MSG_TYPE_TAGS = [
        self::MSG_TYPE_EVENT,
        self::MSG_TYPE_PURCHASE,
        self::MSG_TYPE_ACCOUNT,
        self::MSG_TYPE_HUMAN,
        self::MSG_TYPE_FEEDBACK
    ];

    /**
     * @const array Supported "Type" Entries
     */
    const MSG_TYPE_ALL = [
        self::MSG_TYPE_RESPONSE,
        self::MSG_TYPE_UPDATE,
        self::MSG_TYPE_EVENT,
        self::MSG_TYPE_PURCHASE,
        self::MSG_TYPE_ACCOUNT,
        self::MSG_TYPE_HUMAN,
        self::MSG_TYPE_FEEDBACK
    ];


    /**
     * @const Table Name
     */
    const TABLE_NAME = 'fbapp_messages';


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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "message_id",
        "conversation_id",
        "interaction_id",
        "from_id",
        "to_id",
        "message",
        "tags",
        "read",
        "created_at",
        "updated_at"
    ];

    /**
     * Get the interaction that owns the message.
     *
     * @return BelongsTo
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class, "interaction_id", "interaction_id");
    }

    /**
     * Get the conversation the message belongs to
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'conversation_id');
    }

    /**
     * @return MorphOne
     */
    public function interactionMessage(): MorphOne
    {
        return $this->morphOne(InteractionMessage::class, 'interactionMessage', 'tb_name', 'tb_primary_id');
    }


    /**
     * Get Message Direction
     * 
     * @return string: incoming|outgoing
     */
    public function getDirectionAttribute(): string
    {
        return ($this->conversation->page_id === $this->to_id) ? 'incoming' : 'outgoing';
    }
}
