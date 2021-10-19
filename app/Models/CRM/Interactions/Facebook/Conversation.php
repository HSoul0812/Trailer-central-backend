<?php

namespace App\Models\CRM\Interactions\Facebook;

use App\Models\CRM\Leads\Facebook\User;
use App\Models\Integration\Facebook\Page;
use App\Models\Traits\TableAware;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Conversation
 * @package App\Models\CRM\Interactions\Facebook
 *
 *  @property User $fbUser
 */
class Conversation extends Model
{
    use TableAware, Compoships;

    const TABLE_NAME = 'fbapp_conversations';

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
        "conversation_id",
        "page_id",
        "user_id",
        "link",
        "snippet",
        "newest_update"
    ];

    /**
     * Get the email history for the interaction.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    /**
     * Get Page
     *
     * @return BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }

    /**
     * Get facebook user
     *
     * @return BelongsTo
     */
    public function fbUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get facebook lead
     * 
     * @return BelongsTo
     */
    public function fbLead(): BelongsTo
    {
        return $this->belongsTo(FbLead::class, ['page_id', 'user_id'], ['page_id', 'user_id']);
    }

    /**
     * Get lead
     * 
     * @return Lead
     */
    public function getLeadAttribute(): Lead
    {
        return $this->fbLead->lead;
    }
}
