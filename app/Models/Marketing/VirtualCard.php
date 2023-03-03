<?php

namespace App\Models\Marketing;

use App\Models\Marketing\Craigslist\Account;
use App\Models\Traits\TableAware;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualCard extends Model
{
    use TableAware;


    /**
     * Define Table Name Constant
     */
    const TABLE_NAME = 'dealer_virtual_cards';

    /**
     * Types of virtual card services used.
     *
     * @var array
     */
    const CARD_SERVICES = [
        'stripe',
        'gallileo',
        'finclusive'
    ];


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
        'type',
        'card_number',
        'security',
        'name_on_card',
        'address_street',
        'address_city',
        'address_state',
        'address_zip',
        'expires_at'
    ];

    /**
     * Get User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get CL Account
     *
     * @return BelongsTo
     */
    public function clAccount(): HasMany
    {
        return $this->belongsTo(Account::class, 'id', 'virtual_card_id');
    }
}
