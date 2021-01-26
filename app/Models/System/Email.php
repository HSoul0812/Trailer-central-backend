<?php

namespace App\Models\System;

use App\Models\Integration\Auth\AccessToken;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Email
 * @package App\Models\System\Email
 */
class Email extends Model
{
    use TableAware;

    // Define Table Name Constant
    const TABLE_NAME = 'system_emails';

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
        'email'
    ];

    /**
     * Google Access Token
     * 
     * @return HasOne
     */
    public function googleToken(): HasOne
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('google')
                    ->whereRelationType('system_emails');
    }
}
