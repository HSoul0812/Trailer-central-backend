<?php

namespace App\Models\CRM\Leads\Facebook;

use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\Lead AS CrmLead;
use App\Models\Integration\Facebook\Page;
use App\Models\Traits\TableAware;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Lead
 * @package App\Models\CRM\Leads\Facebook
 *
 * @property int $id
 * @property int $page_id
 * @property int $user_id
 * @property int $lead_id
 * @property int $merge_id
 *
 * @property Page $page
 * @property User $fbUser
 * @property Conversation $conversation
 * @property Interaction $interaction
 * @property CrmLead $lead
 */
class Lead extends Model
{
    use TableAware, Compoships;

    const TABLE_NAME = 'fbapp_users_leads';

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
        'page_id',
        'user_id',
        'lead_id',
        'merge_id'
    ];

    /**
     * Get Page
     *
     * @return BelongsTo
     */
    public function page(): BelongsTo
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
     * Get conversation
     *
     * @return HasOne
     */
    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, ['page_id', 'user_id'], ['page_id', 'user_id']);
    }

    /**
     * Get the interaction that owns the facebook lead.
     *
     * @return BelongsTo
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class, 'interaction_id', 'merge_id');
    }

    /**
     * Get lead related to the facebook lead
     *
     * @return HasOne
     */
    public function lead(): HasOne
    {
        return $this->hasOne(CrmLead::class, 'identifier', 'lead_id');
    }
}
