<?php

declare(strict_types=1);

namespace App\Models\Integration\Facebook;

use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Facebook\Page;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Class Chat
 * @package App\Models\Integration\Facebook
 *
 * @property SalesPerson $salesPerson
 * @property Collection<SalesPerson> $salesPersons
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
        return $this->belongsTo(SalesPerson::class, 'sales_person_id', 'id');
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

    /**
     * Get Sales Persons
     *
     * @return BelongsToMany
     */
    public function salesPersons(): BelongsToMany
    {
        return $this->belongsToMany(SalesPerson::class, ChatSalesPeople::class, 'fbapp_chat_id', 'sales_person_id')
            ->withTimestamps();
    }

    /**
     * Get Sales People Ids
     *
     * @return array
     */
    public function getSalesPeopleIdsAttribute(): array
    {
        return $this->salesPersons()->pluck(SalesPerson::TABLE_NAME .'.id')->toArray();
    }
}
