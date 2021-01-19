<?php

namespace App\Models\System;

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * @package App\Models\System\Email
 */
class Email extends Model
{
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
    public function googleToken()
    {
        return $this->hasOne(AccessToken::class, 'relation_id', 'id')
                    ->whereTokenType('google')
                    ->whereRelationType('sales_person');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
