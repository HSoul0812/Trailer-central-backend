<?php

declare(strict_types=1);

namespace App\Models\Integration\Facebook;

use App\Models\User\CrmUser;
use App\Models\CRM\User\SalesPerson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Chat
 * @package App\Models\Integration\Facebook
 */
class Chat extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_chat';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
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
        'sales_person_id',
        'account_id',
        'account_name',
        'page_id'
    ];


    /**
     * CRM User
     * 
     * @return BelongsTo
     */
    public function crmUser(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get new dealer user
     * 
     * @return BelongsTo
     */
    public function newDealerUser(): BelongsTo
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Sales Person
     * 
     * @return BelongsTo
     */
    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class, 'id', 'sales_person_id');
    }

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
     * Access Token
     * 
     * @return HasOne
     */
    public function accessToken()
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('facebook')
                    ->whereRelationType('fbapp_chat');
    }
}
