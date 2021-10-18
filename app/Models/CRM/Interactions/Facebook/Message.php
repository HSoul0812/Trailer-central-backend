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
        "read"
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
}
