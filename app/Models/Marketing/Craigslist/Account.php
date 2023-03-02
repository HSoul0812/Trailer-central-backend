<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\Marketing\VirtualCard;
use App\Models\Traits\TableAware;
use App\Models\User\User;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\VirtualCard;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use TableAware;


    /**
     * Define Table Name Constant
     */
    const TABLE_NAME = 'clapp_accounts';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'profile_id',
        'virtual_card_id',
        'username',
        'password',
        'smtp_password',
        'smtp_server',
        'smtp_port',
        'smtp_security',
        'smtp_auth',
        'imap_password',
        'imap_server',
        'imap_port',
        'imap_security'
    ];

    /**
     * Get Dealer
     *
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Virtual Card
     *
     * @return BelongsTo
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(VirtualCard::class, 'id', 'virtual_card_id');
    }

    /**
     * Get Profile
     * 
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id', 'profile_id');
    }

    /**
     * Get Access Tokens
     * 
     * @return HasMany
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(AccessToken::class, 'relation_id', 'id')
                    ->whereRelationType('clapp_accounts');
    }
}
