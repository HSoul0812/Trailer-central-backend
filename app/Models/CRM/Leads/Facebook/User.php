<?php

namespace App\Models\CRM\Leads\Facebook;

use Beta\Microsoft\Graph\Model\Conversation;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Facebook\Lead as UserLead;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Lead
 * @package App\Models\CRM\Leads\Facebook
 *
 * @property int $identifier
 * @property int $website_id
 * @property string $lead_type
 *
 * @property Website $website
 */
class User extends Model
{
    use TableAware;

    const TABLE_NAME = 'fbapp_users';


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
        'user_id',
        'name',
        'email'
    ];

    /**
     * Get leads related to the user
     * 
     * @return HasManyThrough
     */
    public function leads(): HasManyThrough
    {
        return $this->hasManyThrough(Lead::class, UserLead::class, 'identifier', 'lead_id', 'user_id', 'user_id');
    }

    /**
     * Get the conversations for the user.
     * 
     * @return HasMany
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_id', 'user_id');
    }
}
